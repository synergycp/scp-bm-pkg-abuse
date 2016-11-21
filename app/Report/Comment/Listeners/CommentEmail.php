<?php

namespace Packages\Abuse\App\Report\Comment\Listeners;

use Packages\Abuse\App\Report\Comment\Events;
use Packages\Abuse\App\Report\Comment\Comment;
use App\Mail;
use App\Auth\Sso;

/**
 * Send out an Email when a Comment is created on a Report.
 */
class CommentEmail
extends Mail\EmailListener
{
    /**
     * @var Sso\SsoUrlService
     */
    protected $sso;

    /**
     * @param Mail\Mailer       $mail
     * @param Sso\SsoUrlService $sso
     */
    public function __construct(
        Mail\Mailer $mail,
        Sso\SsoUrlService $sso
    ) {
        parent::__construct($mail);

        $this->sso = $sso;
    }

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
     */
    protected function toClient(Comment $comment)
    {
        $client = $comment->report->client;
        $context = [
            'client' => $client->expose('name'),
            'report' => $comment->report->expose('id'),
            'comment' => $comment->expose('body'),
            'urls' => [
                'view' => $this->sso->view($comment->report, $client),
            ],
        ];

        $this
            ->create('abuse_report_comment.tpl')
            ->setData($context)
            ->toUser($client)
            ->send()
            ;
    }
}
