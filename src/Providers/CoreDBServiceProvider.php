<?php
namespace ppeCore\dvtinh\Providers;
use Illuminate\Support\ServiceProvider;
class CoreDBServiceProvider extends ServiceProvider
{

    public function boot()
    {
        //Load route
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        //Load migrate
        $this->loadMigrationsFrom(__DIR__ . '/../../src/Database/migrations');
        //public config
        $this->publishes([
            __DIR__.'/../../config/ppe.php' => config_path('ppe.php'),
        ]);
        //public lang
        $this->publishes([
            __DIR__.'/../../lang/en/ppe.php' => resource_path('lang/en/ppe.php'),
        ]);
    }

}