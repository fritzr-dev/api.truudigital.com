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
	    Route::get('/activities', 'AcceloHubController@activities');
	    Route::post('/activities/import', 'AcceloHubController@importTimesheets');

		Route::post('/activities/import', [
	      'uses' => 'AcceloHubController@importTimesheets',
	      'as' => 'activities.import'
	    ]);
	    Route::get('/logs', 'AcceloHubController@migrationLogs');

	});

});

Route::prefix('_clear')->group(function() {
	Route::get('/', 'AcceloHubController@index');
	Route::get('/members', 'AcceloHubController@ClearSessionMembers');
});

Route::prefix('accelo')->group(function() {
	Route::get('/', 'AcceloController@index');
	
	#Route::get('/members', 'AcceloController@getAcceloMembers');
	#Route::get('/companies', 'AcceloController@getAcceloCompanies');
	#Route::get('/projects', 'AcceloController@getProjects');
	#Route::get('/tickets', 'AcceloController@getTickets');
	#Route::get('/projects/{id}', 'AcceloController@getProject');
	#Route::get('/projects/{id}/milestones', 'AcceloController@getMilestones');
	#Route::get('/tasks', 'AcceloController@getAcceloTasks');
	#Route::get('/activities', 'AcceloController@getAcceloActivities');
	Route::get('/timesheets/post', 'AcceloController@postTimesheets');
	Route::get('/reset', 'AcceloController@resetToken');

	Route::get('/status', 'AcceloController@status');
	Route::get('/developer', 'AcceloController@developer');
});

Route::prefix('hubstaff')->group(function() {
	Route::get('/', 'HubstaffController@index');

	Route::get('/timesheets', 'HubstaffController@getTimesheets');
	Route::get('/notes', 'HubstaffController@getNotes');
	Route::get('/activities', 'HubstaffController@getActivities');

	/*Route::get('/members', 'HubstaffController@getOrganizationMembers');
	Route::get('/companies', 'HubstaffController@getClients');
	Route::get('/projects', 'HubstaffController@getProjects');
	Route::get('/projects/{id}', 'HubstaffController@getProject');
	Route::get('/tasks', 'HubstaffController@getTasks');*/
	Route::get('/reset', 'HubstaffController@resetToken');

	Route::get('/oauth', 'HubstaffController@oauth');
	Route::get('/connect', 'HubstaffController@connect');
	Route::get('/refreshToken', 'HubstaffController@refreshtoken');

	Route::get('/status', 'HubstaffController@status');
	Route::get('/developer', 'HubstaffController@developer');
});


Route::prefix('accelotohub')->group(function() {
	Route::get('/', 'AcceloController@index');

	/*
		1. get accelo project 
		2. post data to hubstaff  */
	Route::get('/projects', 'AcceloController@postAccelo2HubstaffProjects'); 
	/*
		1. get accelo ticket 
		2. post data to DB for schedule as task type TICKETS  
		notes: the accelo ticket will post to hubstaff as todo's/task under _TICKETS project */	
	Route::get('/tickets/schedule', 'AcceloController@postAccelo2DBTickets'); 
	/*
		1. get accelo project from DB
		2. get accelo task  
		2. post data to DB for schedule as task type TASK */		
	Route::get('/projects/tasks/schedule', 'AcceloController@postAccelo2DBProjectTasks');
	/*
		1. get accelo task from DB
		2. post data to hubstaff projects task */		
	Route::get('/projects/tasks', 'AcceloController@postAccelo2HubstaffProjectTasks'); 


	#Route::get('/tickets', 'AcceloController@postAccelo2HubstaffTickets');
	#Route::get('/projects/milestones', 'AcceloController@postAccelo2HubstaffProjectMilestone');
	#Route::get('/projects/milestones/tasks', 'AcceloController@postAccelo2HubstaffProjectMilestoneTask');
	
	/*
		1. get hubstaff timesheets
		2. post data to DB for schedule */	
	//Route::get('/timesheets/schedule', 'HubstaffController@postHubstaff2DBTimesheets');
	/*
		1. get hubstaff timesheets from DB
		2. post data accelo timesheets */	
	Route::get('/timesheets/', 'HubstaffController@postHubstaff2DBTimesheets');
});