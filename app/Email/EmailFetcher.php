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

    $s = app('Settings');

    $host = (string) ($s->{'pkg.abuse.auth.host'} ?? '');
    $user = (string) ($s->{'pkg.abuse.auth.user'} ?? '');
    $pass = (string) ($s->{'pkg.abuse.auth.pass'} ?? '');

    // if any of the settings are empty then the Fetcher should do nothing.
    if (empty($host) || empty($user) || empty($pass)) {
      return;
    }

    $server = new Server($host, '993', '/imap/ssl/novalidate-cert');

    $this->connection = $server->authenticate($user, $pass);

    $archiveSetting = '';
    try {
      $archiveSetting = (string) ($s->{'pkg.abuse.auth.archive_folder'} ?? '');
    } catch (\Throwable $exc) {
      // Setting value is null — treat as empty for auto-detection.
    }

    $this->archiveFolder = $this->resolveArchiveFolder($archiveSetting, $host);

    return $this->connection;
  }

  /**
   * Determine the archive folder to use.
   *
   * If explicitly configured, use that. Otherwise auto-detect: check for
   * [Gmail]/All Mail (Gmail), falling back to "Archived".
   *
   * @param string $configured
   * @param string $host
   *
   * @return string
   */
  private function resolveArchiveFolder($configured, $host) {
    if (!empty($configured)) {
      return $configured;
    }

    // Auto-detect Gmail by host or by checking for the Gmail mailbox.
    $isGmail = stripos($host, 'gmail') !== false
            || stripos($host, 'google') !== false;

    if ($isGmail) {
      return '[Gmail]/All Mail';
    }

    // Check if [Gmail]/All Mail exists (covers custom domains on Google Workspace).
    try {
      if ($this->connection->hasMailbox('[Gmail]/All Mail')) {
        return '[Gmail]/All Mail';
      }
    } catch (\Exception $exc) {
      // Ignore - fall through to default.
    }

    return 'Archived';
  }
}
