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
use Packages\Abuse\App\Report\Report;

use App\Server\ServerRepository;
use Mockery;
use Carbon\Carbon;
use Packages\Abuse\App\Suspension\SuspensionSync;
use Illuminate\Database\Query\Builder;
use Packages\Abuse\App\Report\ReportRepository;

use Packages\Testing\App\Test\TestCase;

class SuspensionControllerTest
    extends TestCase
{
    protected $client;
    protected $server;
    protected $report;
    protected $clientServer;
    protected $url = 'api/pkg/abuse/report';
    protected $apiKey;
    protected $suspension;
    protected $testCase;

    protected $hub;
    protected $hubPort;
    protected $group;
    protected $serverPort;

    protected $suspensionSync;


    public function boot(Suspension $suspension, SuspensionSync $suspensionSync)
    {
        $this->suspension = $suspension;
        $this->suspensionSync = $suspensionSync;
    }
    public function setUp()
    {
        parent::setUp();

        $this->server = $this->factory('testing', Server\Server::class)->create();
        $this->client = $this->factory('testing', Client::class)->create();
        $this->clientServer = new ClientServer();
        $this->clientServer
            ->client()
            ->associate($this->client)
            ->server()
            ->associate($this->server)
            ->save()
        ;

        $this->hub = $this->factory('testing', Hub\Hub::class)->create();
        $this->hubPort = $this->factory('testing', Hub\Port\Port::class)->create(['hub_id' => $this->hub->id]);
        $this->group = $this->factory('testing', Group::class)->create();
        $this->hub->groups()->attach($this->group);
        $this->serverPort = $this->factory('testing', Server\Port\Port::class)->create(['group_id' => $this->group->id, 'server_id' => $this->server->id, 'hub_port_id' => $this->hubPort->id]);

    }

    public function testSuspend()
    {
        $this->reportCreate('subDay', $this->server->fresh());
        $this->suspensionSync->sync();
        $this->assertEquals(1, $this->clientServer->fresh()->suspensions);
    }

    public function testWarning()
    {
        $this->reportCreate('addDay', $this->server->fresh());
        $this->suspensionSync->sync();
        $this->assertEquals(0, $this->clientServer->fresh()->suspensions);
    }

    private function reportCreate($day='addDay', Server\Server $server)
    {
        $this->report = $this->factory('abuse', AbuseReport::class)->create(['pending_type' => 0, 'client_id' => $this->client->id, 'server_id' => $this->server->id, 'created_at' => $this->suspension->maxReportDate()->$day()]);
        $maxReportDate = Carbon::now();
        $suspension = Mockery::mock(Suspension::class);

        $suspension
            ->shouldReceive('maxReportDate')
            ->andReturn($maxReportDate)
        ;

        $suspension
            ->shouldReceive('suspendServer')
            ->with($server, $this->report->created_at)
            ->andReturn(true)
        ;

        $suspension
            ->shouldReceive('suspendWarning')
            ->with($server, $this->report->created_at)
            ->andReturn(true)
        ;

        $reportItem = Mockery::mock(Report::class, ['server_id' => $server->id, 'created_at' => $this->report->created_at]);

        $reports = Mockery::mock(ReportRepository::class);
        $query = Mockery::mock(Builder::class);
        $reports
            ->shouldReceive('query')
            ->andReturn($query)
        ;

        $reports
            ->shouldReceive('whereNotNull')
            ->andReturn($query)
        ;

        $reports
            ->shouldReceive('select')
            ->andReturn($query)
        ;

        $reports
            ->shouldReceive('pendingClient')
            ->andReturn($query)
        ;

        $reports
            ->shouldReceive('groupBy')
            ->andReturn($query)
        ;

        $reports
            ->shouldReceive('get')
            ->andReturn($reportItem)
        ;

        $serverRepository = Mockery::mock(ServerRepository::class);
        $serverRepository
            ->shouldReceive('find')
            ->andReturn(collect($server))
            ;
    }

    public function tearDown()
    {
        Mockery::close();
        $this->clientServer->delete();
        $this->report->delete();
        $this->server->delete();
        $this->client->delete();
        $this->serverPort->delete();
        $this->hub->groups()->detach($this->group);
        $this->serverPort->delete();
        $this->hubPort->delete();
        $this->group->delete();
    }
}