<?php

namespace Packages\Abuse\App\Report\Comment\Listeners;

use Packages\Abuse\App\Report\Comment\Events;

/**
 * Set pending status on a Report after a Comment is created.
 */
class CommentUpdateParent
{
    /**
     * Handle the event.
     *
     * @param Events\CommentCreated $event
     */
    public function handle(Events\CommentCreated $event)
    {
        $comment = $event->comment;
        $report = $comment->report;

        $setPending = $report->client_id && $comment->isByAdmin()
            ? 'setPendingClient' : 'setPendingAdmin';
        $report->$setPending()->save();
    }
}
