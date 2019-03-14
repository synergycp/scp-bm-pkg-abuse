<?php

namespace Packages\Abuse\App\Contact;

use App\Http\RouteServiceProvider;
use Illuminate\Routing\Router;

/**
 * Routes regarding Contacts.
 */
class ContactRoutesProvider
extends RouteServiceProvider
{
    /**
     * @var string
     */
    protected $package = 'abuse';

    protected function api(Router $router)
    {
        $router->resource(
            'contact',
            ContactController::class
        );
    }
}
