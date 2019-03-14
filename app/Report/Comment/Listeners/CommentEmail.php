<?php

namespace Packages\Abuse\App\Report\Comment\Listeners;

use Packages\Abuse\App\Contact\ClientAbuseContact;
use Packages\Abuse\App\Report\Comment\Events;
use Packages\Abuse\App\Report\Comment\Comment;
use App\Mail;

/**
 * Send out an Email when a Comment is created on a Report.
 */
class CommentEmail
extends Mail\EmailListener
{
    /**
     * Handle the event.
     *
     * @param Events\CommentCreated $event
     */
    public function handle(Events\CommentCreated $event)
    {
        $comment = $event->comment;
        $method = $comment->isByAdmin()
            && $comment->report->client
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
     *
     * @throws \Throwable
     */
    protected function toClient(Comment $comment)
    {
        $client = $comment->report->client;
        $context = [
            'client' => $client->expose('name'),
            'report' => $comment->report->expose('id'),
            'comment' => $comment->expose('body'),
            'urls' => [
                'view' => url('/'),
            ],
        ];

        $this
            ->create('abuse_report_comment.tpl')
            ->setData($context)
            ->toUser(with(new ClientAbuseContact($client))->setIgnoreOptOut(true))
            ->send()
            ;
    }
}
