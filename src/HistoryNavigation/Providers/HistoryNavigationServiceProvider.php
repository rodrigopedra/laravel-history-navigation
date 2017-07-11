<?php

namespace RodrigoPedra\HistoryNavigation\Providers;

use Illuminate\Support\ServiceProvider;
use RodrigoPedra\HistoryNavigation\HistoryNavigationService;
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
        $this->loadRoutesFrom( __DIR__ . '/../../routes.php' );
        $this->publishes( [ __DIR__ . '/../../config.php' => config_path( 'navigate-back.php' ) ] );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__ . '/../../config.php', 'navigate-back' );

        $this->app->singleton( HistoryNavigationService::class, function () {
            return new HistoryNavigationService(
                $this->app[ 'request' ],
                $this->app[ 'url' ],
                config( 'navigate-back.default' ),
                config( 'navigate-back.limit' ),
                config( 'navigate-back.skip-history', [] )
            );
        } );

        $this->app->singleton( TrackHistoryNavigation::class, function () {
            return new TrackHistoryNavigation( $this->app->make( HistoryNavigationService::class ) );
        } );

        $this->app[ 'router' ]->pushMiddlewareToGroup( 'web', TrackHistoryNavigation::class );
    }
}
