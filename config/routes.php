<?php
use Cake\Routing\Router;

Router::prefix('api', function ($routes) {
    $routes->extensions(['json', 'xml']);

    $routes->fallbacks('InflectedRoute');
});