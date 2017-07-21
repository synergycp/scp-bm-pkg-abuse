<?php

namespace Packages\Abuse\App\Suspension;

use App\Client\Client;
use App\Client\Server\Models\ClientServer;
use App\Server;
use App\Server\Port\Port;
use App\Api\Key;
use Packages\Abuse\App\Report\Report as AbuseReport;
use Packages\Abuse\App\Suspension\Suspension;
use App\Hub;
use App\Group\Group;
use App\Server\ServerRepository;
use Mockery;
use Carbon\Carbon;
use Packages\Abuse\App\Suspension\SuspensionSync;
use Illuminate\Database\Query\Builder;
use Packages\Abuse\App\Report\ReportRepository;
use App\Client\Server\ClientServerAccessService;
use Packages\Testing\App\Test\TestCase;

class SuspensionSyncTest
    extends TestCase
{
    protected $client;
    protected $server;
    protected $report;
    protected $clientServer;
    protected $url = 'pkg/abuse/report';
    protected $apiKey;
    protected $suspension;
    protected $testCase;
    protected $hub;
    protected $hubPort;
    protected $group;
    protected $serverPort;
    protected $reports;
    protected $query;
    protected $clientServerAccess;
    protected $suspensionSync;
    protected $serverRepository;

    public function setUp()
    {
        parent::setUp();

        $this->query = Mockery::mock(Builder::class);
        $this->reports = Mockery::mock(ReportRepository::class);
        $this->clientServerAccess = Mockery::mock(ClientServerAccessService::class);
        $this->suspension = Mockery::mock(Suspension::class);
        $this->serverRepository = Mockery::mock(ServerRepository::class);

        $this->suspensionSync = new SuspensionSync(
            $this->suspension,
            $this->serverRepository,
            $this->reports
        );

        $this->suspension
            ->shouldReceive('maxReportDate')
            ->andReturn(Carbon::now())
        ;

        $this->server = $this->factory('testing', Server\Server::class)->create();

        $this->clientServer = new ClientServer();

        $this->hub = $this->factory('testing', Hub\Hub::class)->create();
        $this->hubPort = $this->factory('testing', Hub\Port\Port::class)->create(['hub_id' => $this->hub->id]);
        $this->group = $this->factory('testing', Group::class)->create();
        $this->hub->groups()->attach($this->group);
        $this->serverPort = $this->factory('testing', Server\Port\Port::class)->create(['group_id' => $this->group->id, 'server_id' => $this->server->id, 'hub_port_id' => $this->hubPort->id]);

    }

    public function testSuspend()
    {
        $this->clientCreate();
        $server = $this->server->fresh()->load('access.client');
        $this->reportCreate('subDay', $server);

        $this->suspension
            ->shouldReceive('suspendServer')->once()
            ->with(\Mockery::mustBe($server), \Mockery::mustBe($this->report->pending_at))
            ->andReturn(true)
        ;

        $this->suspension
            ->shouldReceive('suspendWarning')->never()
            ->andReturn(true)
        ;

        $this->suspensionSync->sync();
    }

    public function testWarning()
    {
        $this->clientCreate();
        $server = $this->server->fresh()->load('access.client');

        $this->reportCreate('addDay', $server);

        $this->suspension
            ->shouldReceive('suspendServer')
            ->never()
        ;

        $this->suspension
            ->shouldReceive('suspendWarning')
            ->once()
            ->with(\Mockery::mustBe($server), \Mockery::mustBe($this->report->pending_at))
        ;

        $this->suspensionSync->sync();
    }

    public function testVipSuspend()
    {
       $this->vipClientSettings('subDay');
    }

    public function testVipWarning()
    {
        $this->vipClientSettings();
    }

    private function vipClientSettings($day='addDay')
    {
        $this->clientCreate(true);

        $server = $this->server->fresh()->load('access.client');
        $this->reportCreate($day, $server, true);

        $this->suspension
            ->shouldReceive('suspendServer')->never()
        ;

        $this->suspension
            ->shouldReceive('suspendWarning')->never()
        ;

        $this->suspensionSync->sync();
    }

    private function clientCreate($vip=false)
    {
        $clientSettings = empty($vip)
            ? []
            : ['billing_ignore_auto_suspend' => true]
        ;

        $this->client = $this->factory('testing', Client::class)->create($clientSettings);
        $this->clientServer
            ->client()
            ->associate($this->client)
            ->server()
            ->associate($this->server)
            ->save()
        ;
    }

    private function reportCreate($day='addDay', Server\Server $server)
    {
        $this->report = $this->factory('abuse', AbuseReport::class)
             ->create([
                 'pending_type' => 0,
                 'client_id' => $this->client->id,
                 'server_id' => $this->server->id,
                 'pending_at' => Carbon::now()->$day(),
             ]);

        $this->query
            ->shouldReceive('select')
            ->andReturn($this->query)
        ;

        $this->query
            ->shouldReceive('groupBy')
            ->andReturn($this->query)
        ;

        $this->query
            ->shouldReceive('get')
            ->andReturn(collection($this->report->fresh()))
        ;

        $this->query
            ->shouldReceive('pendingClient')
            ->andReturn($this->query)
        ;

        $this->reports
            ->shouldReceive('query')
            ->andReturn($this->query)
        ;

        $this->reports
            ->shouldReceive('whereNotNull')
            ->andReturn($this->query)
        ;

        $this->serverRepository
            ->shouldReceive('find')
            ->with([$server->id])
            ->andReturn($this->query)
        ;

        $this->query
            ->shouldReceive('load')
            ->andReturn($this->query)
        ;

        $this->query
            ->shouldReceive('keyBy')
            ->andReturn(collection($server)->keyBy('id'))
        ;
    }

    public function tearDown()
    {
        $this->clientServer->delete();
        $this->report->delete();
        $this->server->delete();
        $this->client->delete();
        $this->serverPort->delete();
        $this->hub->groups()->detach($this->group);
        $this->serverPort->delete();
        $this->hubPort->delete();
        $this->group->delete();

        parent::tearDown();
    }
}
