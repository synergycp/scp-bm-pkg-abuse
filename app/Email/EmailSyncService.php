<?php

namespace Packages\Abuse\App\Email;

class EmailSyncService
{
    /**
     * Synchronize all mailboxes with the mail server.
     */
    public function sync()
    {
        $sync = function (Email $email) {
            $emailSync = new EmailSynchronizer($email);

            $emailSync->start();
        };

        collect([new Email()])->each($sync);
    }
}
