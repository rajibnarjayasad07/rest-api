<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'cors'],function ($router) {

$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/profile', 'AuthController@me');

//stuff
$router->group(['prefix' => 'stuff'], function() use ($router){
    $router->get('/', 'StuffController@index');
    $router->post('/create', 'StuffController@store');
    $router->get('/show/{id}', 'StuffController@show');
    $router->put('/patch/{id}', 'StuffController@update');
    $router->delete('/delete/{id}', 'StuffController@destroy');
    $router->get('/trash', 'StuffController@deleted');
    $router->put('/restore/{id}', 'StuffController@restore');
    $router->put('/restoreAll/', 'StuffController@restoreAll');
    $router->delete('/permanentDel/{id}', 'StuffController@permanentDel');
    $router->delete('/permanentDelAll/', 'StuffController@permanentDelAll');
});

$router->group(['prefix' => 'inbound'], function() use ($router){
    $router->get('/', 'InboundStuffController@index');
    $router->post('/create', 'InboundStuffController@store');
    $router->get('/show/{id}', 'InboundStuffController@show');
    $router->patch('/patch/{id}', 'InboundStuffController@update');
    $router->delete('/delete/{id}', 'InboundStuffController@destroy');
    $router->get('/trash', 'InboundStuffController@deleted');
    $router->put('/restore/{id}', 'InboundStuffController@restore');
    $router->put('/restoreAll/', 'InboundStuffController@restoreAll');
    $router->delete('/permanentDel/{id}', 'InboundStuffController@permanentDel');
    $router->delete('/permanentDelAll/', 'InboundStuffController@permanentDelAll');
});

// $router->get('/inbound', 'InboundStuffController@index');
// $router->post('/inbound/create', 'InboundStuffController@store');
// $router->patch('/inbound/patch/{id}', 'InboundStuffController@update');
// $router->get('/inbound/show/{id}', 'InboundStuffController@show');
// $router->delete('/inbound/delete/{id}', 'InboundStuffController@destroy');

//stock
$router->group(['prefix' => 'stock'], function() use ($router){
    $router->get('/', 'StuffStockController@index');
    $router->post('/create', 'StuffStockController@store');
    $router->get('/show/{id}', 'StuffStockController@show');
    $router->patch('/patch/{id}', 'StuffStockController@update');
    $router->delete('/delete/{id}', 'StuffStockController@destroy');
    $router->get('/trash', 'StuffStockController@deleted');
    $router->put('/restore/{id}', 'StuffStockController@restore');
    $router->put('/restoreAll/', 'StuffStockController@restoreAll');
    $router->delete('/permanentDel/{id}', 'StuffStockController@permanentDel');
    $router->delete('/permanentDelAll/', 'StuffStockController@permanentDelAll');
});

$router->get('/lending', 'lendingController@index');
$router->post('/lending/create', 'lendingController@store');
$router->patch('/lending/patch/{id}', 'lendingController@update');
$router->get('/lending/show/{id}', 'lendingController@show');
$router->delete('/lending/delete/{id}', 'lendingController@destroy');

//user
$router->group(['prefix' => 'user'], function() use ($router){
    $router->get('/', 'userController@index');
    $router->post('/create', 'userController@store');
    $router->get('/show/{id}', 'userController@show');
    $router->patch('/patch/{id}', 'userController@update');
    $router->delete('/delete/{id}', 'userController@destroy');
    $router->get('/trash', 'userController@deleted');
    $router->put('/restore/{id}', 'userController@restore');
    $router->put('/restoreAll/', 'userController@restoreAll');
    $router->delete('/permanentDel/{id}', 'userController@permanentDel');
    $router->delete('/permanentDelAll/', 'userController@permanentDelAll');
});

$router->get('/restoration', 'restorationController@index');
$router->post('/restoration/create', 'restorationController@store');
$router->patch('/restoration/patch/{id}', 'restorationController@update');
$router->get('/restoration/show/{id}', 'restorationController@show');
$router->delete('/restoration/delete/{id}', 'restorationController@destroy');

});