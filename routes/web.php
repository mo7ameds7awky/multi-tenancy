<?php

use App\Http\Middleware\IdentifyTenant;
use App\Tenant;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group([
    'middleware' => IdentifyTenant::class,
    'as' => 'tenant:',
    'prefix' => '/{tenant}'
], function () {
    $tenant = Tenant::first();
    Route::get('hi', function () use ($tenant) {
        App\Jobs\TenantDatabase::dispatch($tenant, app(App\Services\TenantManager::class));
        return 'done';
    });
});
