<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

use Modules\AcceloHub\Entities\AcceloConnect;
use Modules\AcceloHub\Entities\HubstaffConnect;
use Modules\AcceloHub\Entities\HubstaffActivity;
use Modules\AcceloHub\Entities\AcceloProjects;
use Modules\AcceloHub\Entities\AcceloTasks;
use Modules\AcceloHub\Entities\AcceloSync;

class AcceloSchedule extends Model
{
    //protected $fillable = [];
    
	#Route::get('/projects', 'AcceloController@postAccelo2HubstaffProjects'); 
    public static function projects(){
      $error = []; $success = []; $result = []; $migrated = [];

      $projects  = AcceloConnect::getProjects();

      $ch = curl_init();
      HubstaffConnect::setCurl($ch);
      foreach ($projects as $key => $project) {
        $accelo = $project;
        $accelo_project_id    = $accelo['id'];

        #$project_task = HubstaffConnect::postTasks($accelo_project_id, $accelo, 'PROJECT');
        $entry = AcceloProjects::where('accelo_project_id', $accelo_project_id)->first();
        if(!$entry){
          /*store project*/
          $hubstaff = HubstaffConnect::postProject($accelo);

          if(isset($hubstaff['project'])) {
            $hubstaff = $hubstaff['project'];
            $accelo_project_id   = $accelo['id'];
            $hubstaff_project_id = $hubstaff['id'];
            $acceloProj_data     = json_encode($accelo);
            $hubstaffProj_data   = json_encode($hubstaff);

            $project = AcceloProjects::create([
              'accelo_project_id'   => $accelo_project_id,
              'hubstaff_project_id' => $hubstaff_project_id,
              'acceloProj_data'     => $acceloProj_data,
              'hubstaffProj_data'   => $hubstaffProj_data,
              'status'              => 0,
            ]);
            $success[] = $accelo;
          } else {
            $error[] = array('error' => 'Error in posting to hubstaff', 'api' => $hubstaff);
          }
        } else {
          $update_entry = AcceloProjects::find($entry->id);
          $update_entry->acceloProj_data     = json_encode($accelo);
          $update_entry->update();
          $migrated[] = array('error' => 'Already Migrated', 'api' => $accelo);
        }
      }//foreach

      $result = array('success' => $success, 'error' => $error, 'migrated' => $migrated );
      
      AcceloSync::newLog( 'projects', $result );
      return $result;
    }//projects

    #Route::get('/tickets/schedule', 'AcceloController@postAccelo2DBTickets'); 
    public static function getTickets(){
      $error = []; $success = []; $result = []; $migrated = [];

      $result  = AcceloConnect::getTickets();

      $tickets = $result;
      if($tickets){

        //$project_TICKET = config('accelohub.project_ticket');
        $project_TICKET = AcceloProjects::getTicketProject();
        foreach ($tickets as $key => $accelo) {
          /*saved to DB*/
          $new_task = HubstaffConnect::postTasksDB($project_TICKET, $accelo, 'TICKET');
          /*saved to DB END*/

          if (isset($new_task['success']) && $new_task['success']) {
            $success[] = $new_task['success'];
          } else if (isset($new_task['migrated']) && $new_task['migrated']) {
            $migrated[] = $new_task['migrated'];
          } else if (isset($new_task['error']) && $new_task['error']) {
            $error[] = $new_task['error'];
          }
        } // foreach

      }
      $result = array('success' => $success, 'error' => $error, 'migrated' => $migrated );
      
      AcceloSync::newLog( 'ticket2DB', $result );  
      return $result;
    }//getTickets

    #Route::get('/projects/tasks/schedule', 'AcceloController@postAccelo2DBProjectTasks');
    public static function getProjectTasks(){
      $error = []; $success = []; $result = [];
      
      /*if (!session_id()) session_start(); 
      dd($_SESSION['CURL_URLS']);*/
      
      $records = AcceloProjects::getAcceloDBProjects();

      foreach ($records as $key => $record) {
        $project_id = $record->id;

        $accelo_project_id    = $record->accelo_project_id;
        $hubstaff_project_id  = $record->hubstaff_project_id;
        $project              = json_decode($record->acceloProj_data);

        #$project_task = HubstaffConnect::postTasks($hubstaff_project_id, (array) $project, 'PROJECT');
        /*project task*/
        $tasks  = AcceloConnect::getProjectTasks($accelo_project_id);
        foreach ($tasks as $key => $task) {
          if(HubstaffConnect::$postTask >=10){
            break;
          }
          $new_task = array('success' => '', 'migrated' => '', 'success' => 'error');
          if ($task['job']) {
            $new_task = HubstaffConnect::postTasksDB($project_id, $task, 'TASK');
            if (isset($new_task['success']) && $new_task['success']) {
              $success[] = $new_task['success'];
            } else if (isset($new_task['migrated']) && $new_task['migrated']) {
              $migrated[] = $new_task['migrated'];
            } else if (isset($new_task['error']) && $new_task['error']) {
              $error[] = $new_task['error'];
            }
          }

        }
        /*project task*/

        $milestones  = AcceloConnect::getProjectMilestones($accelo_project_id);
        #dd($milestones);
        #echo "PROJECT: ".$project->title."[".$project->id."]<br />";
        foreach ($milestones as $key => $accelo) {
          #echo "--MILESTONE: ".$accelo['title']."<br />";
          $milestone_id = $accelo['id'];

          #$milestone_task = HubstaffConnect::postTasks($hubstaff_project_id, $accelo, 'MILESTONE');
          $tasks = AcceloConnect::getMilestoneTasks($milestone_id);
          foreach ($tasks as $key => $task) {          
            /*post task to hubbstaff*/
            $new_task = HubstaffConnect::postTasksDB($project_id, $task, 'TASK');
            if (isset($new_task['success']) && $new_task['success']) {
              $success[] = $new_task['success'];
            } else if (isset($new_task['migrated']) && $new_task['migrated']) {
              $migrated[] = $new_task['migrated'];
            } else if (isset($new_task['error']) && $new_task['error']) {
              $error[] = $new_task['error'];
            }      
            if(HubstaffConnect::$postTask >=10){
              break;
            }              
          }
        }

        $update_entry = AcceloProjects::find($project_id);
        $update_entry->status     = 1;
        $update_entry->update();     

      }

      $result = array('CURL POST'=> HubstaffConnect::$cUrl_run, 'success' => $success, 'error' => $error );
      
      AcceloSync::newLog( 'task2DB', $result ); 
      return $result; 
    }//getProjectTasks

    #Route::get('/projects/tasks', 'AcceloController@postAccelo2HubstaffProjectTasks'); 
    public static function postTasks(){
      $error = []; $success = []; $result = [];

      $new_task = array('success' => '', 'migrated' => '', 'success' => 'error');
      /*project task*/
      $DBTasks = AcceloTasks::getAcceloDBTasks();
      $ch = curl_init();
      HubstaffConnect::setCurl($ch);      
      foreach ($DBTasks as $key => $DBTask) {

        $hubstaff_project_id = $DBTask->hubstaff_project_id;
        $task = json_decode($DBTask->acceloTask_data, true);

        $new_task = HubstaffConnect::postTasks($hubstaff_project_id, $task, 'TASK');
        #continue;
        if ($new_task['success']) {

          $hubstaff       = $new_task['data'];
          $hubstaff_id    = $hubstaff['id'];

          $update_entry = AcceloTasks::find($DBTask->id);
          $update_entry->hubstaff_task_id   = $hubstaff_id;
          $update_entry->hubstaffTask_data  = json_encode($hubstaff);
          $update_entry->status             = 1;
          $update_entry->update();

          $success[] = $hubstaff;          
        } else {
          $error[] = $new_task['data'];
        }

      }
      /*project task*/
      //echo $curl = AcceloConnect::$cUrl_run + HubstaffConnect::$cUrl_run;
      $result = array('success' => $success, 'error' => $error );
      AcceloSync::newLog( 'task2Hubstaff', $result ); 
    }//getProjectTasks

    #Route::get('/timesheets/', 'HubstaffController@postHubstaff2DBTimesheets');
    public static function timesheets(){

      $time_logs = HubstaffActivity::where('status', 0)->limit(config('accelohub.cLimit'))->get();

      $result = AcceloConnect::postTimesheets($time_logs);   

      AcceloSync::newLog( 'task2DB', $result );  
      return $result;
    }//timesheets

}
