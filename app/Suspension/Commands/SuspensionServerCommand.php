<?php

namespace Packages\Abuse\App\Suspension\Commands;

use App\Console\Commands\Command;
use Packages\Abuse\App\Suspension;

class SuspensionServerCommand
    extends Command
{
    /**
     * @var Suspension\ReportList
     */
    protected $report;

    /**
     * @param Suspension\ReportList $report
     */
    public function boot(Suspension\ReportList $report)
    {
        $this->report = $report;
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
        $this->report->get();
    }
}
