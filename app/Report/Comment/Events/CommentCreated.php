<?php

namespace Packages\Abuse\App\Report\Comment\Events;

use App\Log\Log;

class CommentCreated extends CommentLoggableEvent
{
    public function log(Log $log)
    {
        $targets = array_filter([
            $this->comment->report,
            $this->comment->report->server,
        ]);

        $log->setDesc('Comment on Abuse Report')
            ->setTargets($targets)
            ;
    }
}
