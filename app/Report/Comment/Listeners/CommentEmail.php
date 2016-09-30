<?php

namespace Packages\Abuse\App\Report\Comment\Listeners;

use Packages\Abuse\App\Report\Comment\Events;
use Packages\Abuse\App\Report\Comment\Comment;
use App\Services\Mail\EmailListener;

/**
 * Send out an Email when a Comment is created on a Report.
 */
class CommentEmail
extends EmailListener
{
    /**
     * Handle the event.
     *
     * @param Events\CommentCreated $event
     */
    public function handle(Events\CommentCreated $event)
    {
        $comment = $event->comment;

        $method = $comment->isByAdmin() && $comment->report->client
            ? 'toClient'
            : 'toAdmin'
            ;

        $this->$method($comment);
    }

    /**
     * @param Comment $comment
     */
    protected function toAdmin(Comment $comment)
    {
        //
    }

    /**
     * @param Comment $comment
     */
    protected function toClient(Comment $comment)
    {
        $client = $comment->report->client;
        $context = [
            'client' => $client->expose('name'),
            'report' => $comment->report->expose('id'),
            'comment' => $comment->expose('body'),
        ];

        $this->create('abuse_report_comment.tpl')
            ->setData($context)
            ->toUser($client)
            ->send()
            ;
    }
}
