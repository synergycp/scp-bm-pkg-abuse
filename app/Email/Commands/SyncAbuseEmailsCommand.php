<?php

namespace Packages\Abuse\App\Email\Commands;

use App\Services\Console\Commands\Command;
use Packages\Abuse\App\Email;

class SyncAbuseEmailsCommand
extends Command
{
    /**
     * @var Email\EmailSyncService
     */
    protected $service;

    /**
     * @param Email\EmailSyncService $service
     */
    public function boot(Email\EmailSyncService $service)
    {
        $this->service = $service;
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'abuse:sync-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync abuse emails.';

    public function handle()
    {
        $this->info('Syncing abuse emails...');
        $this->service->sync();
    }
}
