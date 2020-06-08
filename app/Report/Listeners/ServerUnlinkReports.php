<?php

namespace Packages\Abuse\App\Report\Listeners;

use App\Server\Events\ServerEvent;
use Packages\Abuse\App\Report\ReportRepository;

/**
 * When a server is deleted, unlink the abuse reports pertaining to that server.
 */
class ServerUnlinkReports
{
    /**
     * @var ReportRepository
     */
    protected $reports;

    /**
     * @param ReportRepository $reports
     */
    public function __construct(
        ReportRepository $reports
    ) {
        $this->reports = $reports;
    }

    /**
     * Handle the event.
     *
     * @param ServerEvent $event
     */
    public function handle(ServerEvent $event)
    {
        $serverID = $event->getServer()->getKey();

        $this->reports
            ->query()
            ->where('server_id', $serverID)
            ->update([
                'server_id' => null,
            ])
            ;
    }
}
