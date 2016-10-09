<?php

use App\Support\Database\Migration;
use App\Log\LogTarget;
use Packages\Abuse\App\Report\Report;
use Packages\Abuse\App\Report\Comment\Comment;

class MoveAbuseReports
extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        LogTarget::query()
            ->where('target_type', 'App\Models\AbuseReport')
            ->update([
                'target_type' => Report::class,
            ])
            ;

        Comment::query()
            ->where('author_type', 'App\Models\Administrator')
            ->update([
                'author_type' => \App\Admin\Admin::class,
            ]);

        Comment::query()
            ->where('author_type', 'App\Models\Client')
            ->update([
                'author_type' => \App\Client\Client::class,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        LogTarget::query()
            ->where('target_type', Report::class)
            ->update([
                'target_type' => 'App\Models\AbuseReport',
            ])
            ;

        Comment::query()
            ->where('author_type', \App\Admin\Admin::class)
            ->update([
                'author_type' => 'App\Models\Administrator',
            ]);

        Comment::query()
            ->where('author_type', \App\Client\Client::class)
            ->update([
                'author_type' => 'App\Models\Client',
            ]);
    }
}
