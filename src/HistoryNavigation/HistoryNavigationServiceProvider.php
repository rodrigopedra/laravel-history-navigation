<?php

namespace RodrigoPedra\HistoryNavigation;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use RodrigoPedra\HistoryNavigation\Http\Middleware\TrackHistoryNavigation;

class HistoryNavigationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../../views', 'history-navigation');

        $configPath = $this->app['path.config'] . DIRECTORY_SEPARATOR . 'navigate-back.php';
        $this->publishes([__DIR__ . '/../../config/navigate-back.php' => $configPath]);

        $this->listenToAuthEvents();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/navigate-back.php', 'navigate-back');

        $this->app->singleton(HistoryNavigationService::class, function () {
            return new HistoryNavigationService(
                $this->app['url'],
                $this->app->make(\Illuminate\Contracts\Session\Session::class),
                $this->app['config']->get('navigate-back')
            );
        });

        $this->app->singleton(TrackHistoryNavigation::class, function () {
            return new TrackHistoryNavigation($this->app->make(HistoryNavigationService::class));
        });

        $this->app['router']->pushMiddlewareToGroup('web', TrackHistoryNavigation::class);
    }

    private function listenToAuthEvents()
    {
        Event::listen(\Illuminate\Auth\Events\Login::class, function () {
            $this->app->make(HistoryNavigationService::class)->clear()->persist();
        });

        Event::listen(\Illuminate\Auth\Events\Logout::class, function () {
            $this->app->make(HistoryNavigationService::class)->clear()->persist();
        });
    }
}
