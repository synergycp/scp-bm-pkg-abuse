<?php

namespace Packages\Abuse\App\Report;

use App\Client\Client;
use App\Entity\Entity;
use App\Server\Server;
use App\Entity\LookupService;
use App\Ip\IpAddressRangeContract;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Packages\Abuse\App\Email\Email;

/**
 * Business logic for Abuse Reports.
 */
class ReportService
{
    /**
     * @var Carbon
     */
    protected $now;

    /**
     * @var LookupService
     */
    protected $lookup;

    /**
     * @var ReportFilterService
     */
    protected $filter;

    /**
     * @var ReportRepository
     */
    protected $reports;

    /**
     * @param Carbon              $now
     * @param LookupService       $lookup
     * @param ReportFilterService $filter
     * @param ReportRepository    $reports
     */
    public function __construct(
        Carbon $now,
        LookupService $lookup,
        ReportFilterService $filter,
        ReportRepository $reports
    ) {
        $this->now = $now;
        $this->filter = $filter;
        $this->lookup = $lookup;
        $this->reports = $reports;
    }

    /**
     * Generate (but don't save) an abuse report for the given IP Address.
     *
     * @param IpAddressRangeContract $addr
     *
     * @return Report
     */
    public function make(IpAddressRangeContract $addr)
    {
        $entity = $this->lookup->addr($addr);

        return $this->makeWithEntity($addr, $entity);
    }

    /**
     * @param Report $report
     */
    public function resolve(Report $report)
    {
        if ($report->is_resolved) {
            return;
        }

        $report->resolve()->save();

        event(new Events\ReportStatusChanged($report));
    }

    /**
     * Get the number of open abuse reports for the given Client.
     *
     * @param Client $client
     *
     * @return int
     */
    public function countOpen(Client $client)
    {
        $this->filter->clientHasAccess(
            $query = $this->reports->query(),
            $client->getKey()
        );

        return $query
            ->open()
            ->count()
            ;
    }

    /**
     * Get all abuse reports matching the given unique keys.
     *
     * @param Collection $keys
     *
     * @return Builder
     */
    public function matching(Collection $keys)
    {
        return $this->reports->query()->whereIn('msg_num', $keys);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function messageNumberExists($key)
    {
        return $this->matching(collect([$key]))->count() > 0;
    }

    /**
     * @return Report|null
     */
    public function latest()
    {
        return $this->reports->latest();
    }

    /**
     * @param Email $email
     *
     * @return Carbon
     */
    public function minDate(Email $email)
    {
        $settings = (array) app('Settings');
        $mins = [
            $this->now->subDays(
                array_get($settings, 'pkg.abuse.report.threshold', 7)
            ),
        ];

        // TODO: latest based on $email
        if ($latest = $this->latest()) {
            $mins[] = $latest
                ->date
                ->subSeconds(1)
            ;
        }

        $minimum = function (Carbon $carry, Carbon $date) {
            return $carry->max($date);
        };

        return collection($mins)
            ->reduce($minimum, array_pop($mins))
            ;
    }

    /**
     * @param Report      $report
     * @param Entity|null $entity
     */
    public function setEntity(Report $report, $entity)
    {
        $this->setServer($report, dot_get($entity, 'owner.server'));

        if (!$entity) {
            $report->entity()->dissociate();

            return;
        }

        $report->entity()->associate($entity);
    }

    /**
     * @param Report      $report
     * @param Server|null $server
     */
    public function setServer(Report $report, $server)
    {
        $this->setClient($report, dot_get($server, 'access.client'));

        if (!$server) {
            $report->server()->dissociate();

            return;
        }

        $report->server()->associate($server);
    }

    /**
     * @param Report      $report
     * @param Client|null $client
     */
    public function setClient(Report $report, $client)
    {
        if (!$client) {
            $report->client()->dissociate();
            $report->setPendingAdmin();

            return;
        }

        $report->client()->associate($client);
        $report->setPendingClient();
    }

    /**
     * @param IpAddressRangeContract $addr
     * @param Entity|null                   $entity
     *
     * @return mixed
     */
    public function makeWithEntity(IpAddressRangeContract $addr, $entity = null)
    {
         // Generate the report.
        $report = $this->reports->make([
            'addr' => (string) $addr,
            // This is the default value, but can be overridden.
            'reported_at' => $this->now,
        ])->setPendingAdmin();

        if ($entity) {
            $this->setEntity($report, $entity);
        }

        return $report;
    }
}
