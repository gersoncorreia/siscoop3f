<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routes = app('router')->getRoutes();

foreach ($routes as $route) {
    if (strpos($route->uri(), 'login') !== false) {
        echo sprintf("%-30s %-30s %s\n", $route->uri(), $route->getName(), implode(', ', $route->middleware()));
    }
}
