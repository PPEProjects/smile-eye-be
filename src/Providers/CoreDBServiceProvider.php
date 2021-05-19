<?php
namespace ppe\dvtinh\Providers;
use Illuminate\Support\ServiceProvider;
class CoreDBServiceProvider extends ServiceProvider
{

    public function boot()
    {
        //Load route
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        //Load migrate
        $this->loadMigrationsFrom(__DIR__ . '/../../src/Database/migrations');
        //Load factory
        $this->publishes([
            __DIR__.'/../../database/factories' => base_path('database/factories'),
        ]);
        //public config
        $this->publishes([
            __DIR__.'/../../config/ppe.php' => config_path('ppe.php'),
        ]);

    }

}