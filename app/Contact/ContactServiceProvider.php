<?php

namespace Packages\Abuse\App\Contact;

class ContactServiceProvider
extends \App\Support\ServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        ContactRoutesProvider::class,
    ];
}
