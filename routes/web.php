<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Facades\Route;

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

// --- Web Routes for serving HTML pages ---
$router->get('/login', function () use ($router) {
    return view('login');
});

$router->get('/register', function () use ($router) {
    return view('register');
});

$router->get('/dashboard', function () use ($router) {
    return view('dashboard');
});
// --- End Web Routes ---


// Authentication API Routes
$router->group(['prefix' => 'api/auth'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
    $router->post('logout', 'AuthController@logout'); // Client-side token deletion
    $router->post('store-token', 'AuthController@storeToken'); // Store token in session - removed jwt.auth middleware to avoid circular dependency
});

// Protected API Routes (requires JWT)
$router->group(['prefix' => 'api', 'middleware' => 'auth:api'], function () use ($router) {
    $router->get('me', 'AuthController@me');
    $router->get('dashboard-data', function () {
        // Example protected route for dashboard data
        return response()->json(['message' => 'Welcome to the protected dashboard!', 'data' => ['item1' => 'Value A', 'item2' => 'Value B']]);
    });
});

// Preblock routes
$router->group(['prefix' => 'api', 'middleware' => ['session.token']], function () use ($router) {
    $router->get('/preblock', 'Backend\PreblockMclController@index');
    $router->get('/preblock-visit', 'Backend\PreblockMclController@showVisit');
    $router->get('/crm-details', 'Backend\PreblockMclController@getAllCrmDetails');
    $router->get('/crm-visits', 'Backend\PreblockMclController@getVisits');
    $router->get('/generate-transno', 'Backend\PreblockMclController@generateTransNo');
    $router->post('/store', 'Backend\PreblockMclController@store');
    $router->post('/update', 'Backend\PreblockMclController@update');
    $router->delete('/destroy/{id}', 'Backend\PreblockMclController@destroy');
    $router->post('/crm-visits/export-pdf', 'Backend\PreblockMclController@exportPdf');
    $router->post('/crm-visit-detail/{id}/is-visited', 'Backend\PreblockMclController@updateIsVisited');
    $router->get('/report-preblock-visit', 'Backend\PreblockMclController@showReportVisit');
    $router->get('/report-preblock-visit/data', 'Backend\PreblockMclController@reportVisit');

    Route::get('/report-customer', 'Backend\ReportSalesDistrictController@index');
    Route::post('/report-customer-export', 'Backend\ReportSalesDistrictController@exportByCustomer');
    
    Route::get('/actual-working-day', 'Backend\WorkingDayController@index');
    Route::get('/actual-working-day/data', 'Backend\WorkingDayController@getData');
});