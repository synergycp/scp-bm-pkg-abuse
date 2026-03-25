<?php

namespace Packages\Abuse\App\Email;

use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Server;
use Ddeboer\Imap\Connection;
use Ddeboer\Imap\MessageIterator;
use Ddeboer\Imap\MessageInterface;

class EmailFetcher {
  /**
   * @var Connection|null
   */
  protected $connection;

  /**
   * @var string
   */
  protected $archiveFolder;

  /**
   * @param string $box
   *
   * @return MessageIterator|void
   */
  public function get($box = 'INBOX') {
    if (!($connect = $this->connect())) {
      return;
    }

    return $connect->getMailbox($box)->getMessages(new SearchExpression());
  }

  /**
   * Move a message from the inbox to the archive folder.
   *
   * @param MessageInterface $message
   */
  public function archive(MessageInterface $message) {
    $connect = $this->connect();
    if (!$connect || !$this->archiveFolder) {
      return;
    }

    $archiveBox = $connect->getMailbox($this->archiveFolder);
    $message->move($archiveBox);
  }

  /**
   * @return Connection|void
   */
  private function connect() {
    if ($this->connection) {
      return $this->connection;
    }

    $settings = (array) app('Settings');

    // if any of the settings are empty then the Fetcher should do nothing.
    if (
      empty($settings['pkg.abuse.auth.host']) ||
      empty($settings['pkg.abuse.auth.user']) ||
      empty($settings['pkg.abuse.auth.pass'])
    ) {
      return;
    }

    $this->archiveFolder = $settings['pkg.abuse.auth.archive_folder'] ?? '[Gmail]/All Mail';

    $server = new Server(
      $settings['pkg.abuse.auth.host'],
      '993',
      '/imap/ssl/novalidate-cert'
    );

    return $this->connection = $server->authenticate(
      $settings['pkg.abuse.auth.user'],
      $settings['pkg.abuse.auth.pass']
    );
  }
}
