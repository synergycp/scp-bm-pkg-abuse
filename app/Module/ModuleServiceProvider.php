<?php

namespace Packages\Abuse\App\Module;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadTranslationsFrom(
            $this->basePath().'/resources/lang',
            'pkg.'.$this->folder()
        );
    }

    public function register()
    {
    }

    protected function folder()
    {
        return 'abuse';
    }

    protected function basePath()
    {
        return sprintf(
            '%s/packages/%s',
            $this->app->basePath(),
            $this->folder()
        );
    }
}
