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
        $smtpHost = 'smtp_host';
        $smtpUser = 'smtp_user';
        $smtpPassword = 'smtp_pass';
        $server = new Server(app('Settings')->{$smtpHost});

        return $this->connection = $server->authenticate(
            app('Settings')->{$smtpUser},
            app('Settings')->{$smtpPassword}
        );
    }
}
