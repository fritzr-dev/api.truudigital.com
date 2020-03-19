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
	    Route::get('/projects', 'AcceloHubController@projects');
	    Route::get('/tasks', 'AcceloHubController@tasks');
	    Route::get('/tickets', 'AcceloHubController@tickets');

	    Route::get('/organization', 'AcceloHubController@organization');

	});

});

Route::prefix('_clear')->group(function() {
	Route::get('/', 'AcceloHubController@index');
	Route::get('/members', 'AcceloHubController@ClearSessionMembers');
});

Route::prefix('accelo')->group(function() {
	Route::get('/', 'AcceloController@index');
	
	Route::get('/members', 'AcceloController@getAcceloMembers');
	Route::get('/companies', 'AcceloController@getAcceloCompanies');
	Route::get('/projects', 'AcceloController@getProjects');
	Route::get('/tickets', 'AcceloController@getTickets');
	Route::get('/projects/{id}', 'AcceloController@getProject');
	Route::get('/projects/{id}/milestones', 'AcceloController@getMilestones');
	Route::get('/tasks', 'AcceloController@getAcceloTasks');
	Route::get('/activities', 'AcceloController@getAcceloActivities');
	Route::get('/timesheets/post', 'AcceloController@postTimesheets');
	Route::get('/reset', 'AcceloController@resetToken');

	Route::get('/status', 'AcceloController@status');
	Route::get('/developer', 'AcceloController@developer');
});

Route::prefix('hubstaff')->group(function() {
	Route::get('/', 'HubstaffController@index');

	Route::get('/timesheets', 'HubstaffController@getTimesheets');;

	/*Route::get('/members', 'HubstaffController@getOrganizationMembers');
	Route::get('/companies', 'HubstaffController@getClients');
	Route::get('/projects', 'HubstaffController@getProjects');
	Route::get('/projects/{id}', 'HubstaffController@getProject');
	Route::get('/tasks', 'HubstaffController@getTasks');
	Route::get('/activities', 'HubstaffController@getActivities');*/
	Route::get('/reset', 'HubstaffController@resetToken');

	Route::get('/oauth', 'HubstaffController@oauth');
	Route::get('/connect', 'HubstaffController@connect');
	Route::get('/refreshToken', 'HubstaffController@refreshtoken');

	Route::get('/status', 'HubstaffController@status');
	Route::get('/developer', 'HubstaffController@developer');
});


Route::prefix('accelotohub')->group(function() {
	Route::get('/', 'AcceloController@index');

	Route::get('/projects', 'AcceloController@postAccelo2HubstaffProjects'); // get accelo project and post to hubstaff immediately
	Route::get('/tickets/schedule', 'AcceloController@postAccelo2DBTickets'); // schedule new tickets as _TICKET task
	Route::get('/projects/tasks/schedule', 'AcceloController@postAccelo2DBProjectTasks'); // schedule project task

	Route::get('/tickets', 'AcceloController@postAccelo2HubstaffTickets');
	Route::get('/projects/tasks', 'AcceloController@postAccelo2HubstaffProjectTasks');
	#Route::get('/projects/milestones', 'AcceloController@postAccelo2HubstaffProjectMilestone');
	Route::get('/projects/milestones/tasks', 'AcceloController@postAccelo2HubstaffProjectMilestoneTask');
	

	Route::get('/timesheets', 'HubstaffController@postHubstaff2DBTimesheets');

});