<?php

\Route::get( '/navigate/back', [
    'uses' => 'RodrigoPedra\\HistoryNavigation\\Http\\Controllers\\HistoryNavigationController@back',
    'as'   => 'navigate.back',
] );

\Route::post( '/navigate/sync', [
    'uses' => 'RodrigoPedra\\HistoryNavigation\\Http\\Controllers\\HistoryNavigationController@sync',
    'as'   => 'navigate.sync',
] );
