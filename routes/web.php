<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/testapi', function () {
    return response()->json(
        collect(Route::getRoutes())->map(function ($route) {
            return [
                'uri' => $route->uri(),
                'method' => $route->methods(),
                'action' => $route->getActionName(),
            ];
        })->values()
    );
});

