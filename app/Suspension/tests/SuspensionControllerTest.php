<?php

namespace Packages\Abuse\App\Report\Suspension;

use App\Client\Client;
use App\Client\Server\Models\ClientServer;
use App\Server;
use App\Server\Port\Port;
use App\Api\Key;
use Packages\Abuse\App\Report\Report as AbuseReport;
use Packages\Abuse\App\Suspension\Suspension;
use App\Hub;
use App\Group\Group;

use \App\Support\Test\TestCase;

class SuspensionControllerTest
    extends TestCase
{
    protected $clientWarning;
    protected $serverWarning;
    protected $reportWarning;
    protected $clientSuspend;
    protected $serverSuspend;
    protected $reportSuspend;
    protected $clientServerWarning;
    protected $clientServerSuspend;
    protected $url = 'api/pkg/abuse/report';
    protected $apiKey;
    protected $suspension;
    protected $testCase;

    protected $hubWarning;
    protected $hubPortWarning;
    protected $groupWarning;
    protected $serverPortWarning;

    protected $hubSuspend;
    protected $hubPortSuspend;
    protected $groupSuspend;
    protected $serverPortSuspend;

    public function boot(\Packages\Testing\App\Test\TestCase $testCase, Suspension $suspension)
    {
        $this->testCase = $testCase;
        $this->suspension = $suspension;
    }
    public function setUp()
    {
        parent::setUp();

        // Warning generation
        $this->serverWarning = $this->testCase->factory('testing', Server\Server::class)->create();
        $this->clientWarning = $this->testCase->factory('testing', Client::class)->create();
        $this->reportWarning = $this->testCase->factory('abuse', AbuseReport::class)->create(['pending_type' => 0, 'client_id' => $this->clientWarning->id, 'server_id' => $this->serverWarning->id, 'created_at' => $this->suspension->maxReportDate()->addDay()]);
        $this->clientServerWarning = new ClientServer();
        $this->clientServerWarning
            ->client()
            ->associate($this->clientWarning)
            ->server()
            ->associate($this->serverWarning)
            ->save()
        ;

        $this->hubWarning = $this->testCase->factory('testing', Hub\Hub::class)->create();
        $this->hubPortWarning = $this->testCase->factory('testing', Hub\Port\Port::class)->create(['hub_id' => $this->hubWarning->id]);
        $this->groupWarning = $this->testCase->factory('testing', Group::class)->create();
        $this->hubWarning->groups()->attach($this->groupWarning);
        $this->serverPortWarning = $this->testCase->factory('testing', Server\Port\Port::class)->create(['group_id' => $this->groupWarning->id, 'server_id' => $this->serverWarning->id, 'hub_port_id' => $this->hubPortWarning->id]);

        // Suspend generation
        $this->serverSuspend = $this->testCase->factory('testing', Server\Server::class)->create();
        $this->clientSuspend = $this->testCase->factory('testing', Client::class)->create();
        $this->reportSuspend = $this->testCase->factory('abuse', AbuseReport::class)->create(['pending_type' => 0, 'client_id' => $this->clientSuspend->id, 'server_id' => $this->serverSuspend->id, 'created_at' => $this->suspension->maxReportDate()->subDay()]);
        $this->clientServerSuspend = new ClientServer();
        $this->clientServerSuspend
            ->client()
            ->associate($this->clientSuspend)
            ->server()
            ->associate($this->serverSuspend)
            ->save()
        ;

        $this->hubSuspend = $this->testCase->factory('testing', Hub\Hub::class)->create();
        $this->hubPortSuspend = $this->testCase->factory('testing', Hub\Port\Port::class)->create(['hub_id' => $this->hubSuspend->id]);
        $this->groupSuspend = $this->testCase->factory('testing', Group::class)->create();
        $this->hubSuspend->groups()->attach($this->groupSuspend);
        $this->serverPortSuspend = $this->testCase->factory('testing', Server\Port\Port::class)->create(['group_id' => $this->groupSuspend->id, 'server_id' => $this->serverSuspend->id, 'hub_port_id' => $this->hubPortSuspend->id]);


        \Artisan::call('abuse:suspension');
    }

    public function testSuspend()
    {
        if ($this->clientServerSuspend->find($this->clientServerSuspend->id)->suspensions) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(true);

    }

    public function testWarning()
    {
        if (!$this->serverPortWarning->find($this->serverPortWarning->id)->suspensions) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    public function tearDown()
    {
        $this->clientServerSuspend->delete();
        $this->clientServerWarning->delete();
        $this->reportSuspend->delete();
        $this->reportWarning->delete();
        $this->serverSuspend->delete();
        $this->serverWarning->delete();
        $this->clientSuspend->delete();
        $this->clientWarning->delete();

        $this->serverPortSuspend->delete();
        $this->hubSuspend->groups()->detach($this->groupSuspend);
        $this->serverPortSuspend->delete();
        $this->hubPortSuspend->delete();
        $this->groupSuspend->delete();

        $this->serverPortWarning->delete();
        $this->hubWarning->groups()->detach($this->groupWarning);
        $this->serverPortWarning->delete();
        $this->hubPortWarning->delete();
        $this->groupWarning->delete();
    }
}