<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

use Modules\AcceloHub\Entities\AcceloConnect;
use Modules\AcceloHub\Entities\HubstaffConnect;
use Modules\AcceloHub\Entities\HubstaffActivity;
use Modules\AcceloHub\Entities\AcceloProjects;
use Modules\AcceloHub\Entities\AcceloTasks;
use Modules\AcceloHub\Entities\AcceloSync;
use Modules\AcceloHub\Entities\AcceloMembers;

class AcceloSchedule extends Model
{
    //protected $fillable = [];
  
  static $hubstaff_members   = [];

	#Route::get('/projects', 'AcceloController@postAccelo2HubstaffProjects'); 
  public static function projects(){
    $error = []; $success = []; $result = []; $migrated = [];$updated = [];

    $projects  = AcceloConnect::getProjects();
    $member_ids = AcceloMembers::assign_hubstaff_ids();
    self::$hubstaff_members = $member_ids;
    
    $ch = curl_init();
    HubstaffConnect::setCurl($ch);
    foreach ($projects as $key => $project) {
      $accelo = $project;
      $accelo_project_id    = $accelo['id'];
      #if($accelo_project_id != 299)  continue;

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

        $proj_data = json_decode($entry->acceloProj_data, true);
        if ($proj_data['title'] != $accelo['title']) {
          $hubstaff = HubstaffConnect::updateProject($entry->hubstaff_project_id, $accelo);
          if($hubstaff) {
            $update_entry = AcceloProjects::find($entry->id);
            $update_entry->acceloProj_data     = json_encode($accelo);
            $update_entry->hubstaffProj_data   = json_encode($hubstaff);
            $update_entry->update();

            $updated = $accelo;
          }
        }
        
        $migrated[] = array('error' => 'Already Migrated', 'api' => $accelo);
      }
    }//foreach

    $result = array('success' => $success, 'error' => $error, 'migrated' => $migrated, 'updated' => $updated );
    #dd($result);
    AcceloSync::newLog( 'projects', $result );
    return $result;
  }//projects

  #Route::get('/tickets/schedule', 'AcceloController@postAccelo2DBTickets'); 
  public static function getTickets(){
    $error = []; $success = []; $result = []; $migrated = []; $updated = [];

    $result  = AcceloConnect::getTickets();

    $tickets = $result;
    if($tickets){

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
        } else if (isset($new_task['updated']) && $new_task['updated']) {
          $updated[] = $new_task['updated'];
        }
      } // foreach

    }
    $result = array('success' => $success, 'error' => $error, 'migrated' => $migrated, 'updated' => $updated );
    
    AcceloSync::newLog( 'ticket2DB', $result );  
    #dd($result);
    return $result;
  }//getTickets

  #Route::get('/projects/tasks/schedule', 'AcceloController@postAccelo2DBProjectTasks');
  public static function getProjectTasks($task_status = ''){
    $error = []; $success = []; $result = [];
    

    $records = AcceloProjects::getAcceloDBProjects();
    $project_task_updated = '';
    $project_atask = [];
    $date_created = '';
    foreach ($records as $key => $record) {
      $project_id = $record->id;
      
      $accelo_project_id    = $record->accelo_project_id;
      $hubstaff_project_id  = $record->hubstaff_project_id;
      $project              = json_decode($record->acceloProj_data);

      if ($task_status == 'updates') {
        $accelo_last_task = $record->accelo_last_task_updated;
      } else {
        $accelo_last_task = $record->accelo_last_task;
      }

      /*project task*/
      $project_atask[$project_id] = [];
      $tasks  = AcceloConnect::getLastProjectTasks($accelo_project_id, $accelo_last_task);

      if ($tasks) {
        foreach ($tasks as $key => $task) {
          $project_atask[$project_id][] = $task;

          $new_task = array('success' => '', 'migrated' => '', 'success' => 'error');
          
          $new_task = HubstaffConnect::postTasksDB($project_id, $task, 'TASK');
          if (isset($new_task['success']) && $new_task['success']) {
            $success[] = $new_task['success'];
          } else if (isset($new_task['migrated']) && $new_task['migrated']) {
            $migrated[] = $new_task['migrated'];
          } else if (isset($new_task['error']) && $new_task['error']) {
            $error[] = $new_task['error'];
          }
        }        
        $last_task    = end($tasks);

        if (isset($last_task['date_created'])) {
          $date_created = $last_task['date_created'];

          if ($task_status == 'updates') {
            $record->accelo_last_task_updated     = $date_created;
          } else {
            $record->accelo_last_task     = $date_created;
          }

          $record->update(); 
        }

      } //if ($tasks) {
      /*project task*/

      $milestones  = AcceloConnect::getProjectMilestones($accelo_project_id);
      
      foreach ($milestones as $key => $accelo) {
        $milestone_id = $accelo['id'];
        $tasks = AcceloConnect::getLastMilestoneTasks($milestone_id, $accelo_last_task);
        foreach ($tasks as $key => $task) {    
               
          if ($task['job']) {$project_atask[$project_id][] = $task;}
          /*post task to hubbstaff*/
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

      $project_task_updated = $project_task_updated .", ".$project_id."::".$accelo_last_task." Task Count".count($project_atask[$project_id]);
    }
    
    $result = array('CURL POST'=> HubstaffConnect::$cUrl_run, 'Project Updates'=> $project_task_updated, 'Last Date'=> $date_created, 'success' => $success, 'error' => $error );
    
    AcceloSync::newLog( 'task2DB', $result ); 
    return $result; 
  }//getProjectTasks

  #Route::get('/projects/tasks', 'AcceloController@postAccelo2HubstaffProjectTasks'); 
  public static function postProjectTasks($type=''){
    $error = []; $success = []; $result = [];

    $new_task = array('success' => '', 'migrated' => '', 'success' => 'error');
    /*project task*/
    $DBTasks = AcceloTasks::getAcceloDBTasks($type);

    $ch = curl_init();
    HubstaffConnect::setCurl($ch);     
    HubstaffConnect::refreshToken(); 
    foreach ($DBTasks as $key => $DBTask) {

      $hubstaff_project_id = $DBTask->hubstaff_project_id;
      $task = json_decode($DBTask->acceloTask_data, true);

      $new_task = HubstaffConnect::postTasks($hubstaff_project_id, $task, 'TASK');
      #continue;
      if ($new_task['success']) {

        $hubstaff       = $new_task['data'];
        $hubstaff_id    = $hubstaff['id'];

        /*$update_entry = AcceloTasks::find($DBTask->id);
        $update_entry->hubstaff_task_id   = $hubstaff_id;
        $update_entry->hubstaffTask_data  = json_encode($hubstaff);
        $update_entry->status             = 1;
        $update_entry->update();*/

        unset($DBTask->accelo_project_id);
        unset($DBTask->hubstaff_project_id);

        $DBTask->hubstaff_task_id   = $hubstaff_id;
        $DBTask->hubstaffTask_data  = json_encode($hubstaff);
        $DBTask->status             = 1;
        $DBTask->update();

        $success[] = $hubstaff;          
      } else {
        $error[] = $new_task['data'];
      }

    }
    /*project task*/
    //echo $curl = AcceloConnect::$cUrl_run + HubstaffConnect::$cUrl_run;
    $result = array('success' => $success, 'error' => $error );
    #$result_c = array('success' => count($success), 'error' => count($error) );
    AcceloSync::newLog( $type.'task2Hubstaff', $result ); 
    #dd($result);
    return $result;
  }//postProjectTasks

  public static function updateProjectTasks($type=''){
    $error = []; $success = []; $result = [];

    $new_task = array('success' => '', 'migrated' => '', 'success' => 'error');
    /*project task*/
    $DBTasks = AcceloTasks::getAcceloDBTasks($type, 2);

    $ch = curl_init();
    HubstaffConnect::setCurl($ch);     
    HubstaffConnect::refreshToken(); 
    foreach ($DBTasks as $key => $DBTask) {

      $hubstaff_task_id = $DBTask->hubstaff_task_id;
      $task = json_decode($DBTask->acceloTask_data, true);
      $new_task = HubstaffConnect::updateTasks($hubstaff_task_id, $task, 'TASK');
      #continue;
      if ($new_task['success']) {

        $hubstaff       = $new_task['data'];
        $hubstaff_id    = $hubstaff['id'];

        /*$update_entry = AcceloTasks::find($DBTask->id);
        $update_entry->hubstaff_task_id   = $hubstaff_id;
        $update_entry->hubstaffTask_data  = json_encode($hubstaff);
        $update_entry->status             = 1;
        $update_entry->update();*/

        unset($DBTask->accelo_project_id);
        unset($DBTask->hubstaff_project_id);

        $DBTask->hubstaffTask_data  = json_encode($hubstaff);
        $DBTask->status             = 1;
        $DBTask->update();

        $success[] = $hubstaff;          
      } else {
        $error[] = $new_task['data'];
      }

    }
    /*project task*/
    //echo $curl = AcceloConnect::$cUrl_run + HubstaffConnect::$cUrl_run;
    $result = array('success' => $success, 'error' => $error );
    #$result_c = array('success' => count($success), 'error' => count($error) );
    AcceloSync::newLog( 'taskupdate2Hubstaff', $result ); 
    #dd($result);
    return $result;
  }//updateProjectTasks

  #Route::get('/timesheets/', 'HubstaffController@postHubstaff2DBTimesheets');
  public static function timesheets(){

    $time_logs = HubstaffActivity::where('status', 0)->limit(config('accelohub.cLimit'))->get();

    $result = AcceloConnect::postTimesheets($time_logs);   

    AcceloSync::newLog( 'timesheet2Accelo', $result );  
    return $result;
  }//timesheets

  public static function getProjectTasksV1(){
    $error = []; $success = []; $result = [];
    
    /*if (!session_id()) session_start(); 
    dd($_SESSION['CURL_URLS']);*/
    
    $records = AcceloProjects::getAcceloDBProjects();
    $project_task_updated = '';
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
        #if ($task['job']) {}
        $new_task = HubstaffConnect::postTasksDB($project_id, $task, 'TASK');
        if (isset($new_task['success']) && $new_task['success']) {
          $success[] = $new_task['success'];
        } else if (isset($new_task['migrated']) && $new_task['migrated']) {
          $migrated[] = $new_task['migrated'];
        } else if (isset($new_task['error']) && $new_task['error']) {
          $error[] = $new_task['error'];
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

      $project_task_updated = $project_task_updated .", ".$project_id;
    }

    $result = array('CURL POST'=> HubstaffConnect::$cUrl_run, 'Project Updates'=> $project_task_updated, 'success' => $success, 'error' => $error );
    
    AcceloSync::newLog( 'task2DB', $result ); 
    return $result; 
  }//getProjectTasks

}
