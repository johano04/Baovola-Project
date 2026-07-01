<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->group('api', static function ($routes) {
    $routes->group('auth', static function ($routes) {
        $routes->post('register', 'Api\Auth::register');
        $routes->post('login', 'Api\Auth::login');
        $routes->post('logout', 'Api\Auth::logout');
    });

    $routes->group('products', static function ($routes) {
        $routes->get('/', 'Api\Products::index');
        $routes->get('/(:num)', 'Api\Products::show/$1');
    });

    $routes->group('cart', static function ($routes) {
        $routes->get('/', 'Api\Cart::index');
        $routes->post('add', 'Api\Cart::add');
        $routes->put('update/(:num)', 'Api\Cart::update/$1');
        $routes->delete('remove/(:num)', 'Api\Cart::remove/$1');
    });

    $routes->group('orders', static function ($routes) {
        $routes->get('/', 'Api\Orders::index');
        $routes->post('/', 'Api\Orders::create');
    });

    $routes->post('seed', 'Api\SeederController::seed');
});