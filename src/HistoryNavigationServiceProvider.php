<?php

namespace RodrigoPedra\HistoryNavigation;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use RodrigoPedra\HistoryNavigation\Http\Middleware\TrackHistoryNavigation;

class HistoryNavigationServiceProvider extends ServiceProvider
{
    public array $singletons = [
        HistoryNavigationService::class => HistoryNavigationService::class,
        TrackHistoryNavigation::class => TrackHistoryNavigation::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/navigate-back.php', 'navigate-back');

        $this->app
            ->when(HistoryNavigationService::class)
            ->needs('$defaultUrl')
            ->giveConfig('navigate-back.default-url', '/');

        $this->app
            ->when(HistoryNavigationService::class)
            ->needs('$limit')
            ->giveConfig('navigate-back.history-limit', 50);

        $this->app
            ->when(HistoryNavigationService::class)
            ->needs('$skipPatternsList')
            ->giveConfig('navigate-back.skip-patterns', []);

        $this->app
            ->when(HistoryNavigationService::class)
            ->needs('$removeEmptyQueryParameters')
            ->giveConfig('navigate-back.query.remove-empty', true);

        $this->app
            ->when(HistoryNavigationService::class)
            ->needs('$ignoreQueryParametersList')
            ->giveConfig('navigate-back.query.ignore-parameters', ['page']);
    }

    public function boot(Dispatcher $events, Router $router): void
    {
        $this->publishes([
            __DIR__ . '/../config/navigate-back.php' => $this->app->configPath('navigate-back.php'),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../views', 'history-navigation');

        $router->pushMiddlewareToGroup('web', TrackHistoryNavigation::class);

        $events->listen(Login::class, function () {
            $this->app->make(HistoryNavigationService::class)->clear()->persist();
        });

        $events->listen(Logout::class, static function () {
            $this->app->make(HistoryNavigationService::class)->clear()->persist();
        });
    }
}
