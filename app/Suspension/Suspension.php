<?php

namespace Packages\Abuse\App\Suspension;

use App\Setting\SettingService;
use Packages\Abuse\App\Suspension\Events;
use App\Client\Server\ClientServerAccessService;
use Carbon\Carbon;
use App\Server\Server;
use Illuminate\Events\Dispatcher;

class Suspension
{
    /**
     * @var ClientServerAccessService
     */
    private $access;
    
    /**
     * @var Dispatcher
     */
    private $event;

    /**
     * @var SettingService
     */
    private $setting;

    public function __construct(ClientServerAccessService $access, Dispatcher $event, SettingService $setting)
    {
        $this->access = $access;
        $this->event = $event;
        $this->setting = $setting;
    }

    public function suspendServer(Server $server, Carbon $createdAt)
    {
        $this->access->suspend($server->access, 'Abuse');
        $this->event->fire(
            new Events\ServerSuspend($server, $createdAt)
        );
    }

    public function suspendWarning(Server $server, Carbon $createdAt)
    {
        $this->event->fire(
            new Events\ServerSuspendWarning($server, $createdAt)
        );
    }

    /**
     * @return Carbon|null
     */
    public function maxReportDate()
    {
        $days = $this->setting->getValue('pkg.abuse.auto_suspension');

        if ($days === '0' || $days === null || $days === "") {
            return null;
        }
        
        return Carbon::now()
            ->subDays($days)
            ;
    }
}
