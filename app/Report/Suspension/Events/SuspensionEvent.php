<?php

namespace Packages\Abuse\App\Report\Suspension\Events;

use Packages\Abuse\App\Report\Suspension;
use App\Support\Event;
use App\Support\Database\SerializesModels;
use App\Server\Server;


abstract class SuspensionEvent extends Event
{
    use SerializesModels;

    /**
     * @var Server
     */
    public $server;

    /**
     * Created date
     */
    public $createdDate;

    /**
     * Create a new event instance.
     */
    public function __construct(Server $server, $createdDate)
    {
        $this->server = $server;
        $this->createdDate = $createdDate;
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
}
