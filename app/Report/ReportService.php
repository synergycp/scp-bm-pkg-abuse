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
        // Generate the report.
        $report = $this->reports->make([
            'addr' => (string) $addr,
            'reported_at' => $this->now,
        ])->setPendingAdmin();

        $this->makeRelations($report, $addr);

        return $report;
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
        $query = $this->reports->query();

        $this->filter->clientHasAccess($query, $client->id);

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
     * @return Report|null
     */
    public function latest()
    {
        return $this->reports->latest();
    }

    /**
     * Create relationships on a newly generated Report.
     * NOTE: Report is not in the database yet.
     *
     * @param Report                 $report
     * @param IpAddressRangeContract $range
     */
    private function makeRelations(
        Report $report,
        IpAddressRangeContract $range
    ) {
        $entity = $this->lookup->addr($range);

        $this->setEntity($report, $entity);
    }

    /**
     * @param Report      $report
     * @param Entity|null $entity
     */
    public function setEntity(Report $report, $entity)
    {
        $this->setServer($report, dot_get($entity, 'server'));

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
}
