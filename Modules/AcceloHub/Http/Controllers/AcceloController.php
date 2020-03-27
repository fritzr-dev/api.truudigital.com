<?php

namespace Modules\AcceloHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use DB, Route, Config;

use Modules\AcceloHub\Entities\AcceloProjects;
use Modules\AcceloHub\Entities\AcceloMembers;
use Modules\AcceloHub\Entities\AcceloConnect;
use Modules\AcceloHub\Entities\HubstaffConnect;
use Modules\AcceloHub\Entities\AcceloTickets;
use Modules\AcceloHub\Entities\AcceloTasks;
use Modules\AcceloHub\Entities\HubstaffActivity;
use Modules\AcceloHub\Entities\AcceloSync;

class AcceloController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('accelohub::index');
    }

    public function getAcceloMembers(){

      $result  = AcceloConnect::getStaff();

      return response()->json($result);
    } //getAcceloMembers

    public function getAcceloCompanies(){

      $result  = AcceloConnect::getCompanies();

      return response()->json($result);
    } //getAcceloCompanies


    public function getProjects(){
      $result  = AcceloConnect::getProjects();

      return response()->json($result);
    } //getAcceloCompanies

    public function getProject($id){

      $result  = AcceloConnect::getProject($id);

      return response()->json($result);
    } //getAcceloCompanies        

    public function getMilestones($id){

      $result  = AcceloConnect::getProjectMilestones($id);

      return response()->json($result);
    } //getMilestones    

    public function getTickets(){

      $result  = AcceloConnect::getTickets();

      return response()->json($result);
    } //getTickets    

    public function getAcceloTasks(){

      $result  = AcceloConnect::getAllTasks(); //task_job

      return response()->json($result);
    } //getAcceloTasks

    public function getAcceloActivities(){

      $result  = AcceloConnect::getActivities();

      return response()->json($result);
    } //getAcceloActivities

    public function getAcceloDBProjects(){
      #developer demo
      /*$accelo_project_id = 290;
      $records = AcceloProjects::where('accelo_project_id', $accelo_project_id)->get();
      return $records;*/
      $records = AcceloProjects::where('status', 0)->limit(config('accelohub.cLimit'))->get();
      #$records = AcceloProjects::get();#->limit(1);
      return $records;
    } //getAcceloDBProjects

    public function getAcceloDBTasks(){
      #developer demo
      /*$project_id = 35;
      $records = AcceloTasks::where('project_id', $project_id)->where('status', 0)->get()->map(function($task){
          $project = AcceloProjects::where('id', $task->project_id)->first();
          $task['accelo_project_id']    = $project->accelo_project_id;
          $task['hubstaff_project_id']  = $project->hubstaff_project_id;
          return $task;
       });          

      return $records;*/
      
      $records = AcceloTasks::where('accelo_project_id','!=', 1)->where('status', 0)->limit(config('accelohub.cLimit'))->get()->map(function($task){
          $project = AcceloProjects::where('id', $task->project_id)->first();
          $task['accelo_project_id']    = $project->accelo_project_id;
          $task['hubstaff_project_id']  = $project->hubstaff_project_id;
          return $task;
       });          
      return $records;
    } //getAcceloDBTasks    

    public function getTicketProject(){
      #$records = AcceloProjects::get();#->limit(1);
      #developer demo
      $hubstaff_project_id = config('accelohub.project_ticket');
      $record = AcceloProjects::where('hubstaff_project_id', $hubstaff_project_id)->first();
      if($record) {
        return $record->id;
      } else {
        $project = AcceloProjects::create([
          'accelo_project_id'   => 1,
          'hubstaff_project_id' => $hubstaff_project_id,
          'acceloProj_data'     => '',
          'hubstaffProj_data'   => '',
          'status'              => 1,
        ]);
        return $project ? $project->id : 0;
      }

    } //getAcceloDBProjects

    public function postAccelo2DBTickets(){

      $error = []; $success = []; $result = []; $migrated = [];

      $result  = AcceloConnect::getTickets();

      $tickets = $result;
      if($tickets){

        //$project_TICKET = config('accelohub.project_ticket');
        $project_TICKET = self::getTicketProject();
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
      return response()->json( $result  );
    } //postAccelo2HubstaffTickets

    public function postAccelo2HubstaffTickets(){

      $error = []; $success = []; $result = []; $migrated = [];

      $result  = AcceloConnect::getTickets();

      $tickets = $result;
      if($tickets){

        $ch = curl_init();
        HubstaffConnect::setCurl($ch);
        $project_TICKET = config('accelohub.project_ticket');
        foreach ($tickets as $key => $accelo) {
          /*saved to DB*/
          $new_task = HubstaffConnect::postTasks($project_TICKET, $accelo, 'TICKET');
          /*saved to DB END*/

          if (isset($new_task['success']) && $new_task['success']) {
            $success[] = $new_task['success'];
          } else if (isset($new_task['migrated']) && $new_task['migrated']) {
            $migrated[] = $new_task['migrated'];
          } else if (isset($new_task['error']) && $new_task['error']) {
            $error[] = $new_task['error'];
          }
        } // foreach
        curl_close($ch);
        //dd($ticket_task, $success, $error);
      }
      #report to admin
      #dd($error);
      echo $curl = AcceloConnect::$cUrl_run + HubstaffConnect::$cUrl_run;
      return response()->json( array('success' => $success, 'error' => $error, 'migrated' => $migrated ) );
    } //postAccelo2HubstaffTickets

    public function postAccelo2HubstaffProjects(){
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
      return response()->json( $result );
    } //postAccelo2HubstaffProjects

    public function postAccelo2HubstaffProjectTasks(){
      $error = []; $success = []; $result = [];

      $new_task = array('success' => '', 'migrated' => '', 'success' => 'error');
      /*project task*/
      $DBTasks = $this->getAcceloDBTasks();
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
           
      return response()->json( $result );
    } //postAccelo2HubstaffProjectTasks

    public function postAccelo2HubstaffProjectMilestone(){
      $error = []; $success = [];

      $records = $this->getAcceloDBProjects();
      $result = [];
      foreach ($records as $key => $record) {
        $accelo_project_id    = $record->accelo_project_id;
        $hubstaff_project_id  = $record->hubstaff_project_id;

        #$project_task = HubstaffConnect::postTasks($accelo_project_id, (array) $project, 'PROJECT');
        /*project task*/
        /*$tasks  = AcceloConnect::getProjectTasks($accelo_project_id);

        foreach ($tasks as $key => $task) {

          $new_task = array('success' => '', 'migrated' => '', 'success' => 'error');
          if ($task['job']) {
            $new_task = HubstaffConnect::postTasks($accelo_project_id, $task, 'TASK');
            if (isset($new_task['success']) && $new_task['success']) {
              $success[] = $new_task['success'];
            } else if (isset($new_task['migrated']) && $new_task['migrated']) {
              $migrated[] = $new_task['migrated'];
            } else if (isset($new_task['error']) && $new_task['error']) {
              $error[] = $new_task['error'];
            }
          }

        }*/
        /*project task*/

        $milestones  = AcceloConnect::getProjectMilestones($accelo_project_id);
        /*store Milestone*/
        #dd($milestones);
        $ch = curl_init();
        HubstaffConnect::setCurl($ch);
        foreach ($milestones as $key => $accelo) {
          $milestone_id = $accelo['id'];
          $new_task = HubstaffConnect::postTasks($accelo_project_id, $accelo, 'MILESTONE');
          /*store milestone*/
          if (isset($new_task['success']) && $new_task['success']) {
            $success[] = $new_task['success'];
          } else if (isset($new_task['migrated']) && $new_task['migrated']) {
            $migrated[] = $new_task['migrated'];
          } else if (isset($new_task['error']) && $new_task['error']) {
            $error[] = $new_task['error'];
          }        
        }
      }

      return response()->json( array('success' => $success, 'error' => $error ) );
    } //postAccelo2HubstaffProjectMilestone

    public function postAccelo2DBProjectTasks(){
      $error = []; $success = []; $result = [];
      
      /*if (!session_id()) session_start(); 
      dd($_SESSION['CURL_URLS']);*/
      
      $records = $this->getAcceloDBProjects();
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
      return response()->json( $result );
    } //postAccelo2DBProjectTasks

    public function postAccelo2HubstaffProjectMilestoneTask(){
      $error = []; $success = [];
      $records = $this->getAcceloDBProjects();
      $result = [];
      foreach ($records as $key => $record) {

        $accelo_project_id    = $record->accelo_project_id;
        $hubstaff_project_id  = $record->hubstaff_project_id;
        $project              = json_decode($record->acceloProj_data);

        #$project_task = HubstaffConnect::postTasks($hubstaff_project_id, (array) $project, 'PROJECT');
        /*project task*/
        $tasks  = AcceloConnect::getProjectTasks($accelo_project_id);

        foreach ($tasks as $key => $task) {

          $new_task = array('success' => '', 'migrated' => '', 'success' => 'error');
          if ($task['job']) {
            $new_task = HubstaffConnect::postTasks($hubstaff_project_id, $task, 'TASK');
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
        $ch = curl_init();
        HubstaffConnect::setCurl($ch);
        #echo "PROJECT: ".$project->title."[".$project->id."]<br />";
        foreach ($milestones as $key => $accelo) {
          #echo "--MILESTONE: ".$accelo['title']."<br />";
          $milestone_id = $accelo['id'];

          #$milestone_task = HubstaffConnect::postTasks($hubstaff_project_id, $accelo, 'MILESTONE');
          $tasks = AcceloConnect::getMilestoneTasks($milestone_id);
          foreach ($tasks as $key => $task) {
            /*post task to hubbstaff*/
            $new_task = HubstaffConnect::postTasks($hubstaff_project_id, $task, 'TASK');
            if ($new_task) {
              $success[] = $new_task;
            } else {
              $error[] = array('error' => 'Error in posting to hubstaff', 'api' => $task);
            }       
          }
        }
      }
      /*echo 'CURL POST TASK: '. HubstaffConnect::$cUrl_run;
      return '';*/
      return response()->json( array('CURL POST'=> HubstaffConnect::$cUrl_run, 'success' => $success, 'error' => $error ) );
    } //postAccelo2HubstaffProjectMilestones

    /*public function postHubstaff2DBTimesheets(){

      $timesheets   = HubstaffActivity::where('status', 0)->limit(config('accelohub.cLimit'))->get();
      dd('ddd');
      $result       = AcceloConnect::postTimesheets($timesheets);
      return response()->json($result);
    } //getAcceloCompanies*/

    public function resetToken(){
      echo $result  = AcceloConnect::resetToken();
    } //resetToken

    public function status(){
      AcceloConnect::status();
    }

    public function developer(){

      $post = array();
      $post["grant_type"] = 'client_credentials';
      $post["scope"]      = "read(companies,jobs,tasks,activities),write(activities)";

      echo http_build_query($post);
    }

}
