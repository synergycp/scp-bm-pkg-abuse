<?php

namespace Packages\Abuse\App\Report;

use App\Client\Client;
use App\Client\Server\Models\ClientServer;
use App\Server\Server;
use App\Api\Key;
use Packages\Abuse\App\Report\Report as AbuseReport;
use App\Support\Test\TestCase;

class ReportControllerTest extends TestCase
{
    protected $client;
    protected $server;
    protected $report;
    protected $clientServer;
    protected $url = 'api/pkg/abuse/report';
    protected $apiKey;

    public function setUp()
    {
        parent::setUp();

        $this->server = $this->package_factory('abuse', Server::class)->create();
        $this->client = $this->package_factory('abuse', Client::class)->create();
        $this->report = $this->package_factory('abuse', AbuseReport::class)->create(['client_id' => $this->client->id, 'server_id' => $this->server->id]);
        $this->apiKey = new Key\Key();
        $this->apiKey->owner()->associate($this->client);
        $this->apiKey->save();

        $this->clientServer = new ClientServer();
        $this->clientServer
            ->client()
            ->associate($this->client)
            ->server()
            ->associate($this->server)
            ->save()
        ;
    }

    public function testShowServer() {

        $this->auth($this->client);
        $params = sprintf('%s?key=%s', $this->url, $this->apiKey->key);
        $resp = $this->call('GET', $params);
        $data = $resp->getData(true);

        foreach ($data['data']['data'] as $item) {
            if (!$item['server'])
                $this->assertTrue(false);
        }

        $this->assertTrue(true);
    }

    public function testNotShowServer() {

        $this->clientServer->delete();
        $params = sprintf('%s?key=%s', $this->url, $this->apiKey->key);
        $resp = $this->call('GET', $params);
        $data = $resp->getData(true);

        foreach ($data['data']['data'] as $item) {
            if ($item['server'])
                $this->assertTrue(false);
        }

        $this->assertTrue(true);
    }

    public function tearDown()
    {
        $this->clientServer->delete();
        $this->report->delete();
        $this->apiKey->delete();
        $this->server->delete();
        $this->client->delete();
    }

    private function package_factory()
    {
        $arguments = func_get_args();
        $arguments[0] = __DIR__ . '/../../../../' . $arguments[0] . '/database/factories/';

        if( ! is_dir($arguments[0]) ) {
            throw new \InvalidArgumentException("Package name is invalid or \"/database/factories/\" is not created: \n");
        }

        $factory = \Illuminate\Database\Eloquent\Factory::construct(
            \Faker\Factory::create(),
            $arguments[0]
        );

        if( isset($arguments[2]) && is_string($arguments[2]) ) {
            return $factory->of($arguments[1], $arguments[2])->times(isset($arguments[3]) ? $arguments[3] : 2);
        } elseif( isset($arguments[2]) ) {
            return $factory->of($arguments[1])->times($arguments[2]);
        } else {
            return $factory->of($arguments[1]);
        }
    }

}
