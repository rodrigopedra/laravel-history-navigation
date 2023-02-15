<?php

use Illuminate\Support\Facades\Route;
use RodrigoPedra\HistoryNavigation\Http\Controllers\HistoryNavigationController;

Route::get('/navigate/back', HistoryNavigationController::class)
    ->middleware(['web'])
    ->name('navigate.back');
