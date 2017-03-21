<?php

namespace Packages\Abuse\App\Suspension\Commands;

use App\Console\Commands\Command;
use Packages\Abuse\App\Suspension;

class SuspensionServerCommand
    extends Command
{
    /**
     * @var Suspension\SuspensionSync
     */
    protected $suspension;

    /**
     * @param Suspension\SuspensionSync $suspension
     */
    public function boot(Suspension\SuspensionSync $suspension)
    {
        $this->suspension = $suspension;
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'abuse:suspension';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check servers for abuse reports.';

    public function handle()
    {
        $this->info('Check servers...');
        $this->suspension->sync();
    }
}
