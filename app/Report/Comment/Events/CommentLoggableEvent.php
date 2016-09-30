<?php

namespace Packages\Abuse\App\Report\Comment\Events;

use App\Support\Event;
use App\Services\Log\Log;
use App\Services\Log\LoggableEvent;
use Packages\Abuse\App\Report\Comment\Comment;

abstract class CommentLoggableEvent
extends Event
implements LoggableEvent
{
    use \App\Support\Database\SerializesModels;

    /**
     * The comment that generated the event.
     *
     * @var Comment
     */
    public $comment;

    /**
     * Create a new event instance.
     *
     * @param Comment $comment
     *
     * @return void
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }

    /**
     * @param Log $log
     */
    abstract public function log(Log $log);
}
