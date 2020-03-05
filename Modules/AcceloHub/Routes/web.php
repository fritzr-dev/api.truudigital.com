<?php

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

Route::group([
  'middleware' => 'admin', 
  'prefix' => 'admin', 
  //'namespace' => 'Modules\AcelloHub\Http\Controllers'
], function() {

	Route::prefix('accelohub')->group(function() {
	    Route::get('/', 'AcceloHubController@index');
	    Route::get('/members', 'AcceloHubController@members');
	    Route::get('/member/create', 'AcceloHubController@memberCreate');

	    Route::post('/member/save', 'AcceloHubController@memberSave');

	    Route::get('/member/{id}/delete', [
	      'uses' => 'AcceloHubController@memberDestroy',
	      'as' => 'module.admin.member.delete'
	    ]);

	    Route::get('/member/{id}/edit', [
	      'uses' => 'AcceloHubController@memberEdit'
	    ]);

	    Route::post('/member/update', 'AcceloHubController@memberUpdate');


	});

});

Route::prefix('accelo')->group(function() {
	Route::get('/', 'AcceloController@index');
	
	Route::get('/members', 'AcceloController@getAcceloMembers');
	Route::get('/companies', 'AcceloController@getAcceloCompanies');
	Route::get('/projects', 'AcceloController@getProjects');
	Route::get('/tasks', 'AcceloController@getAcceloTasks');
	Route::get('/activities', 'AcceloController@getAcceloActivities');
	Route::get('/reset', 'AcceloController@resetToken');

	Route::get('/status', 'AcceloController@status');
	Route::get('/developer', 'AcceloController@developer');
});


Route::prefix('hubstaff')->group(function() {
	Route::get('/', 'HubstaffController@index');
	
	Route::get('/members', 'HubstaffController@getAcceloMembers');
	Route::get('/companies', 'HubstaffController@getAcceloCompanies');
	Route::get('/projects', 'HubstaffController@getProjects');
	Route::get('/tasks', 'HubstaffController@getAcceloTasks');
	Route::get('/activities', 'HubstaffController@getAcceloActivities');
	Route::get('/reset', 'HubstaffController@resetToken');

	Route::get('/oauth', 'HubstaffController@oauth');
	
	Route::get('/status', 'HubstaffController@status');
	Route::get('/developer', 'HubstaffController@developer');
	Route::get('/connect', 'HubstaffController@connect');
});