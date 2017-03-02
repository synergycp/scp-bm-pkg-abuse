<?php

namespace Packages\Abuse\App\Email;

use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Search\Date\After;
use App\Mail\Imap\Server;
use App\Mail\Imap\Connection;
use App\Mail\Imap\MessageIterator;
use Carbon\Carbon;

class EmailFetcher
{
    /**
     * @var SearchExpression
     */
    protected $search;

    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(
    ) {
        $this->search = new SearchExpression();
    }

    /**
     * @param Carbon $date
     *
     * @return $this
     */
    public function after(Carbon $date)
    {
        $this->search->addCondition(new After($date));

        return $this;
    }

    /**
     * @param string $box
     *
     * @return MessageIterator
     */
    public function get($box = 'INBOX')
    {
        return $this
            ->connect()
            ->getMailbox($box)
            ->getMessages($this->search)
            ;
    }

    /**
     * @return Connection
     */
    private function connect()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $settings = app('Settings');
        // if any of the settings are empty then the Fetcher should do nothing.
        if(empty($settings->pkg_abuse_auth_host) || empty($settings->pkg_abuse_auth_user) || empty($settings->pkg_abuse_auth_pass))
            return null;

        $server = new Server($settings->pkg_abuse_auth_host);

        return $this->connection = $server->authenticate(
            $settings->pkg_abuse_auth_user,
            $settings->pkg_abuse_auth_pass
        );
    }
}
