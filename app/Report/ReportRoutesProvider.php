<?php

namespace Packages\Abuse\App\Report;

use App\Http\RouteServiceProvider;
use Illuminate\Routing\Router;

/**
 * Routes regarding Servers.
 */
class ReportRoutesProvider
extends RouteServiceProvider
{
    /**
     * @var string
     */
    protected $package = 'abuse';

    /**
     * Setup Routes.
     */
    public function bootRoutes()
    {
        $base = implode('.', ['pkg', $this->package, '']);
        $this->sso->map(Report::class, $base.'report');
    }

    protected function api(Router $router)
    {
        $router->resource(
            'report',
            ReportController::class
        );

        $router->resource(
            'report.comment',
            Comment\CommentController::class
        );
    }
}
