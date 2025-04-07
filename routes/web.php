<?php

use App\Controllers\Auth\LoginController;
use App\Controllers\Campaign\CampaignController;
use App\Controllers\DashboardController;
use App\Controllers\ProfileController;
use App\Controllers\SettingsController;
use App\Controllers\SubscriberController;

// Auth routes
$router->get('/login', 'App\Controllers\Auth\LoginController@showLoginForm');
$router->post('/login', 'App\Controllers\Auth\LoginController@login');
$router->get('/logout', 'App\Controllers\Auth\LoginController@logout');

// Dashboard
$router->get('/', 'App\Controllers\DashboardController@index');

// Campaign routes
$router->get('/campaigns', 'App\Controllers\Campaign\CampaignController@index');
$router->get('/campaigns/create', 'App\Controllers\Campaign\CampaignController@create');
$router->post('/campaigns', 'App\Controllers\Campaign\CampaignController@store');
$router->get('/campaigns/{id}', 'App\Controllers\Campaign\CampaignController@show');
$router->get('/campaigns/{id}/edit', 'App\Controllers\Campaign\CampaignController@edit');
$router->put('/campaigns/{id}', 'App\Controllers\Campaign\CampaignController@update');
$router->delete('/campaigns/{id}', 'App\Controllers\Campaign\CampaignController@destroy');
$router->post('/campaigns/{id}/send', 'App\Controllers\Campaign\CampaignController@send');

// Subscriber routes
$router->get('/subscribers', 'App\Controllers\SubscriberController@index');
$router->get('/subscribers/create', 'App\Controllers\SubscriberController@create');
$router->post('/subscribers', 'App\Controllers\SubscriberController@store');
$router->get('/subscribers/{id}', 'App\Controllers\SubscriberController@show');
$router->get('/subscribers/{id}/edit', 'App\Controllers\SubscriberController@edit');
$router->put('/subscribers/{id}', 'App\Controllers\SubscriberController@update');
$router->delete('/subscribers/{id}', 'App\Controllers\SubscriberController@destroy');
$router->post('/subscribers/import', 'App\Controllers\SubscriberController@import');

// Profile routes
$router->get('/profile', 'App\Controllers\ProfileController@index');
$router->put('/profile', 'App\Controllers\ProfileController@update');
$router->put('/profile/password', 'App\Controllers\ProfileController@updatePassword');

// Settings routes
$router->get('/settings', 'App\Controllers\SettingsController@index');
$router->put('/settings', 'App\Controllers\SettingsController@update');
$router->post('/settings/api-key', 'App\Controllers\SettingsController@generateApiKey');

// API routes
$router->group(['prefix' => 'api', 'middleware' => 'api'], function($router) {
    $router->get('/campaigns', 'App\Controllers\Api\CampaignController@index');
    $router->post('/campaigns', 'App\Controllers\Api\CampaignController@store');
    $router->get('/campaigns/{id}', 'App\Controllers\Api\CampaignController@show');
    $router->put('/campaigns/{id}', 'App\Controllers\Api\CampaignController@update');
    $router->delete('/campaigns/{id}', 'App\Controllers\Api\CampaignController@destroy');
    
    $router->get('/subscribers', 'App\Controllers\Api\SubscriberController@index');
    $router->post('/subscribers', 'App\Controllers\Api\SubscriberController@store');
    $router->get('/subscribers/{id}', 'App\Controllers\Api\SubscriberController@show');
    $router->put('/subscribers/{id}', 'App\Controllers\Api\SubscriberController@update');
    $router->delete('/subscribers/{id}', 'App\Controllers\Api\SubscriberController@destroy');
}); 