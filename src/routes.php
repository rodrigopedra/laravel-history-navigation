<?php

\Route::get( '/navigate/back', [
    'uses' => 'RodrigoPedra\\HistoryNavigation\\Http\\Controllers\\HistoryNavigationController@back',
    'as'   => 'navigate.back',
] );
