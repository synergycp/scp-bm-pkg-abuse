<?php

namespace Packages\Abuse\App;

use Packages\Abuse\App\Report;
use Route;

Route::resource(
    'report',
    '\\'.Report\ReportController::class
);

Route::resource(
    'report.comment',
    '\\'.Report\Comment\CommentController::class
);
