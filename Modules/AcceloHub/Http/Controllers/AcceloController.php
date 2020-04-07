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
use Modules\AcceloHub\Entities\AcceloSchedule;

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

    public function getProjectsWithTask(){

      /*    $project_id = 288;
          $ch = curl_init();
          AcceloConnect::setCurl($ch);

      $accelo_last_task = '';
      $post = [];
      $post["_fields"]  = "_ALL";
      $post["_limit"]   = 10;
      if($accelo_last_task) {
        $post["_filters"]   = "child_of_job($project_id),order_by_asc(date_created),date_created_after($accelo_last_task)";
      } else {
        $post["_filters"]   = "child_of_job($project_id),order_by_asc(date_created)";
      }

      $post_data = http_build_query($post);

      $params     = array();
      $params['url']  = "https://truudigital.api.accelo.com/api/v0/tasks";
      $params['type'] = "GET";
      $params['data'] = $post_data;

      $tasks = AcceloConnect::MultiplecurlAccelo($params);

      foreach ($tasks as $key => $task) {
        echo "<br/>".date("Y/m/d H:i:s", $task['date_created'])." :: ". $task['id']." ::  ".$task['title'];
        $date_created = $task['date_created']; 
      }

      $post["_filters"]   = "child_of_job($project_id),order_by_asc(date_created),date_created_after($date_created)";

      $post_data = http_build_query($post);

      $params     = array();
      $params['url']  = "https://truudigital.api.accelo.com/api/v0/tasks";
      $params['type'] = "GET";
      $params['data'] = $post_data;

      echo "<hr />";
      $tasks2 = AcceloConnect::MultiplecurlAccelo($params);
      foreach ($tasks2 as $key => $task) {
        echo "<br/>".date("Y/m/d H:i:s", $task['date_created'])." :: ". $task['id']." ::  ".$task['title'];
        $date_created2 = $task['date_created']; 
      }

      $post["_filters"]   = "child_of_job($project_id),order_by_asc(date_created),date_created_after($date_created2)";

      $post_data = http_build_query($post);

      $params     = array();
      $params['url']  = "https://truudigital.api.accelo.com/api/v0/tasks";
      $params['type'] = "GET";
      $params['data'] = $post_data;

      echo "<hr />";
      $tasks2 = AcceloConnect::MultiplecurlAccelo($params);
      foreach ($tasks2 as $key => $task) {
        echo "<br/>".date("Y/m/d H:i:s", $task['date_created'])." :: ". $task['id']." ::  ".$task['title'];
        $date_created2 = $task['date_created']; 
      }

      dd($date_created, $tasks,$date_created2, $tasks2);
      curl_close($ch);*/

      $projects  = AcceloConnect::getProjects();
      
      foreach ($projects as $key => $project) {
        $project_id   = $project['id'];
        $project_name = $project['title'];
        $post = [];
        $post["_filters"]   = "child_of_job($project_id)";
        $post_data = http_build_query($post);

        $params     = array();
        $params['url']  = "https://truudigital.api.accelo.com/api/v0/tasks/count";
        $params['type'] = "GET";
        $params['data'] = $post_data;

        $count = AcceloConnect::getResult($params);
        echo "<br /><hr />PROJECT: $project_name  <br />TASKS COUNT:".(isset($count['count']) ? $count['count'] : 0);
        
        /*$tasks = AcceloConnect::getProjectTasks($project_id);
        $table = '';
        foreach ($tasks as $key => $task) {
          $table .= '<tr>
                      <td>'.($key+1).'</td>
                      <td>'.$task['id'].'</td>
                      <td>'.$task['title'].'</td>
                    </tr>';
        }
        echo "<table>$table</table>";*/
        
      }
    }

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

    public function postAccelo2DBTickets(){

      $result = AcceloSchedule::getTickets();     
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
      
      $result = AcceloSchedule::projects();
      return response()->json( $result );
    } //postAccelo2HubstaffProjects

    public function postAccelo2HubstaffProjectTasks(){

      $result = AcceloSchedule::postProjectTasks();           
      return response()->json( $result );
    } //postAccelo2HubstaffProjectTasks

    public function updateAccelo2HubstaffProjectTasks(){

      $result = AcceloSchedule::updateProjectTasks();           
      return response()->json( $result );
    } //updateAccelo2HubstaffProjectTasks

    public function postAccelo2HubstaffProjectTasksTickets(){

      $result = AcceloSchedule::postProjectTasks('TICKET');           
      return response()->json( $result );
    } //postAccelo2HubstaffProjectTasksTickets

    public function postAccelo2HubstaffProjectMilestone(){
      $error = []; $success = [];

      $records = AcceloProjects::getAcceloDBProjects();
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
      $result = AcceloSchedule::getProjectTasks(); 
      return response()->json( $result );
    } //postAccelo2DBProjectTasks

    public function updateAccelo2DBProjectTasks(){
      $result = AcceloSchedule::getProjectTasks('updates'); 
      return response()->json( $result );
    } //updateAccelo2DBProjectTasks

    public function postAccelo2HubstaffProjectMilestoneTask(){
      $error = []; $success = [];
      $records = AcceloProjects::getAcceloDBProjects();
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
