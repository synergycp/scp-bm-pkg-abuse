<?php

namespace Packages\Abuse\App\Report\Commands;

use App\Console\Commands\Command;
use Carbon\Carbon;
use Packages\Abuse\App\Report\Comment\Comment;
use Packages\Abuse\App\Report\ReportRepository;

class DeleteOldAbuseReportsCommand
    extends Command
{
    /**
     * @var ReportRepository
     */
    protected $reports;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'abuse:report:expire';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire abuse reports that have been archived for a while.';

    public function boot(ReportRepository $reports)
    {
        $this->reports = $reports;
    }

    public function handle()
    {
        $this->info('Expiring abuse reports...');
        $reports = $this->reports
            ->query()
            ->where('resolved_at', '<', Carbon::now()
                                              ->subMonth())
        ;
        with(clone $reports)->chunk(200, function ($reports) {
            Comment::query()
                   ->whereIn('abuse_report_id', $reports->map(function ($report) {
                       return $report->id;
                   }))
                   ->forceDelete()
            ;
        });
        $reports->forceDelete();
    }
}
