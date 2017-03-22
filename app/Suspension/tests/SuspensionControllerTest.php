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

use App\Client\Server\ClientServerAccessService;

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
    protected $reports;
    protected $query;
    protected $clientServerAccess;
    protected $suspensionSync;

    public function setUp()
    {
        parent::setUp();

        $this->query = Mockery::mock(Builder::class);
        $this->reports = Mockery::mock(ReportRepository::class);
        $this->clientServerAccess = Mockery::mock(ClientServerAccessService::class);

        $this->suspension = new Suspension(
            $this->clientServerAccess
        );

        $this->suspensionSync = new SuspensionSync(
            $this->suspension,
            app(ServerRepository::class),
            $this->reports
        );

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

        $this->clientServerAccess
            ->shouldReceive('suspend')
            ->once()
        ;

        $this->suspensionSync->sync();
    }

    public function testWarning()
    {
        $this->reportCreate('addDay', $this->server->fresh());

        $this->clientServerAccess
            ->shouldReceive('suspend')
            ->never()
        ;

        $this->suspensionSync->sync();
    }

    private function reportCreate($day='addDay', Server\Server $server)
    {
        $this->report = $this->factory('abuse', AbuseReport::class)->create(['pending_type' => 0, 'client_id' => $this->client->id, 'server_id' => $this->server->id, 'created_at' => $this->suspension->maxReportDate()->$day()]);
        $maxReportDate = Carbon::now();
        $this->suspension = Mockery::mock(Suspension::class);

        $this->suspension
            ->shouldReceive('maxReportDate')
            ->andReturn($maxReportDate)
        ;

        $this->suspension
            ->shouldReceive('suspendServer')
            ->with($server, $this->report->created_at)
            ->andReturn(true)
        ;

        $this->suspension
            ->shouldReceive('suspendWarning')
            ->with($server, $this->report->created_at)
            ->andReturn(true)
        ;

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
            ->andReturn(collect($this->report->fresh()))
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