<?php

namespace Packages\Abuse\App\Report\Listeners;

use App\Client\Events\ClientEvent;
use Packages\Abuse\App\Report\Comment\Comment;
use Packages\Abuse\App\Report\Report;
use Packages\Abuse\App\Report\ReportRepository;

/**
 * When a client is deleted, delete the abuse reports pertaining to their account.
 */
class ClientDeleteReports
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
     * @param ClientEvent $event
     */
    public function handle(ClientEvent $event)
    {
        $clientID = $event->getClient()->getKey();

        $this->reports
            ->query()
            ->where('client_id', $clientID)
            ->update([
                'client_id' => null,
            ])
            ;
    }
}
