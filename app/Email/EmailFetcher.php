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
     * @var Connection|null
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
     * @return MessageIterator|void
     */
    public function get($box = 'INBOX')
    {
        if (!$connect = $this->connect()) {
            return;
        }

        return $connect
            ->getMailbox($box)
            ->getMessages($this->search)
            ;
    }

    /**
     * @return Connection|void
     */
    private function connect()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $settings = (array) app('Settings');

        // if any of the settings are empty then the Fetcher should do nothing.
        if (empty($settings['pkg.abuse.auth.host']) ||
            empty($settings['pkg.abuse.auth.user']) ||
            empty($settings['pkg.abuse.auth.pass'])
        ) {
            return;
        }

        $server = new Server($settings['pkg.abuse.auth.host']);

        return $this->connection = $server->authenticate(
            $settings['pkg.abuse.auth.user'],
            $settings['pkg.abuse.auth.pass']
        );
    }
}
