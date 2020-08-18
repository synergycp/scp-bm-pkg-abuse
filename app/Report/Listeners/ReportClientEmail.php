<?php

namespace Packages\Abuse\App\Report\Listeners;

use App\Client;
use App\Mail;
use App\Url\UrlService;
use Packages\Abuse\App\Contact\ClientAbuseContact;
use Packages\Abuse\App\Report\Events\ReportClientReassigned;
use Packages\Abuse\App\Report\Report;

class ReportClientEmail
  extends Mail\EmailListener {
  /**
   * @var Client\Server\ClientServerAccessService
   */
  private $access;

  /**
   * @var UrlService
   */
  private $url;

  /**
   * ReportClientEmail constructor.
   *
   * @param Client\Server\ClientServerAccessService $access
   * @param UrlService                              $url
   */
  public function boot(Client\Server\ClientServerAccessService $access, UrlService $url) {
    $this->access = $access;
    $this->url = $url;
  }

  /**
   * Handle the event.
   *
   * @param ReportClientReassigned $event
   *
   * @return void
   * @throws \Throwable
   */
  public function handle(ReportClientReassigned $event) {
    $settings = (array) app('Settings');
    if (!array_get($settings, 'pkg.abuse.email.enabled')) {
      return;
    }

    $report = $event->report;

    if ($report->resolved_at) {
      return;
    }

    foreach ($this->clients($report) as $client) {
      $context = ['client' => $client->expose('name'), 'server' => $this->server($report), 'report' => $this->report(
        $report
      ), 'urls' => ['view' => sprintf(
        '%s/pkg/abuse/report/%d',
        $this->url->base(get_class($client)),
        $report->getKey()
      ),],];

      $this->create('abuse_report.tpl')->setData($context)->toUser(new ClientAbuseContact($client))->send();
    }
  }

  /**
   * Get Clients that have access to the Report.
   *
   * @param Report $report
   *
   * @return Client\Client[]
   */
  public function clients(Report $report) {
    if (!$server = $report->server) {
      return [$report->client];
    }

    return $this->access->clients($report->server);
  }

  /**
   * @param Report $report
   *
   * @return array|null
   */
  private function server(Report $report) {
    if (!$server = $report->server) {
      return null;
    }

    return $server->expose('id', 'nickname', 'name');
  }

  /**
   * @param Report $report
   *
   * @return array
   */
  private function report(Report $report) {
    return $report->expose('id', 'addr', 'body') + ['date' => (string)$report->created_at,];
  }
}
