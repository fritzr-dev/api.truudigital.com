<?php

namespace Modules\AcceloHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use DB, Route;

use Modules\AcceloHub\Entities\AcceloProjects;
use Modules\AcceloHub\Entities\AcceloMembers;
use Modules\AcceloHub\Entities\AcceloConnect;
use Modules\AcceloHub\Entities\HubstaffConnect;
use Modules\AcceloHub\Entities\AcceloTickets;
use Modules\AcceloHub\Entities\AcceloTasks;

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

    public function getAcceloProjects(){
      #$records = AcceloProjects::get();#->limit(1);
      #developer demo
      $accelo_project_id = 290;
      $records = AcceloProjects::where('accelo_project_id', $accelo_project_id)->get();
      return $records;
    } //getAcceloProjects

    public function postAccelo2HubstaffTickets(){
      $error = []; $success = [];

      $result  = AcceloConnect::getTickets();
      dd($result);
      $tickets = $result;
      if($tickets){

        $ch = curl_init();
        HubstaffConnect::setCurl($ch);
        $project_TICKET = AcceloConnect::$project_ticket;
        foreach ($tickets as $key => $accelo) {
          $assignee = $accelo['assignee'];
          $members = AcceloMembers::get_HID_byAID($assignee);

          $post = array(
                  "name"        => "TICKET-".$accelo['id'].": ".$accelo['title'], 
                  "description" => "Accelo Ticket ID:".$accelo['id'].". ".$accelo['description'],
                  "summary"     => "TICKET-".$accelo['id']." :: Title:".$accelo['title']."  Description:".$accelo['description'],
                  "assignee_id" => $members
                  //"client_id"=> 0
                );
          $hubstaff = HubstaffConnect::postTask($project_TICKET, $post);
          #dd($post, $accelo, $hubstaff);

          /*saved to DB*/
          $ticket_task= [];
          if (isset($hubstaff['task'])) {
            $hubstaff = $hubstaff['task'];
            $accelo_ticket_id   = $accelo['id'];
            $hubstaff_task_id = $hubstaff['id'];
            $acceloTicket_data     = json_encode($accelo);
            $hubstaffTask_data   = json_encode($hubstaff);
              $entry = AcceloTickets::where('accelo_ticket_id', $accelo_ticket_id)->first();#->where('hubstaff_task_id', $hubstaff_task_id)
              if(!$entry){
                $ticket_task= AcceloTickets::create([
                  'accelo_ticket_id'   => $accelo_ticket_id,
                  'hubstaff_task_id' => $hubstaff_task_id,
                  'acceloTicket_data'     => $acceloTicket_data,
                  'hubstaffTask_data'   => $hubstaffTask_data
                ]);
              } else {
                $update_entry = AcceloTickets::find($entry->id);
                $update_entry->acceloTicket_data = $acceloTicket_data;
                $update_entry->hubstaffTask_data = $hubstaffTask_data;
                $update_entry->hubstaff_task_id  = $hubstaff_task_id;
                $update_entry->update();
              }

            $success[] = $accelo;
          } else {
            $error[] = array('error' => 'Error in posting to hubstaff', 'post' => $post, 'api' => $hubstaff);
          }
          /*saved to DB END*/

        } // foreach
        curl_close($ch);
        //dd($ticket_task, $success, $error);
        return response()->json(array('success' => $success, 'error' => $error ) );
      }
      #report to admin
      #dd($error);
      #return response()->json($result);
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
              'hubstaffProj_data'   => $hubstaffProj_data
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

      return response()->json( array('success' => $success, 'error' => $error, 'migrated' => $migrated ) );
    } //postAccelo2HubstaffProjects

    public function postAccelo2HubstaffProjectTasks(){
      $error = []; $success = [];

      $records = $this->getAcceloProjects();
      foreach ($records as $key => $record) {
        $accelo_project_id    = $record->accelo_project_id;
        $hubstaff_project_id  = $record->hubstaff_project_id;
        $tasks  = AcceloConnect::getProjectTasks($accelo_project_id);
        foreach ($tasks as $key => $task) {
          $new_task = false;
          if ($task['job']) {
            $new_task = HubstaffConnect::postTasks($accelo_project_id, $task, 'TASK');
          }
          if ($new_task) {
            $success[] = $new_task;
          } else {
            $error[] = array('error' => 'Error in posting to hubstaff', 'api' => $task);
          }
        }
      }
      return response()->json( array('success' => $success, 'error' => $error ) );
    } //postAccelo2HubstaffProjectTasks

    public function postAccelo2HubstaffProjectMilestone(){
      $error = []; $success = [];

      $records = $this->getAcceloProjects();
      $result = [];
      foreach ($records as $key => $record) {
        $accelo_project_id    = $record->accelo_project_id;
        $hubstaff_project_id  = $record->hubstaff_project_id;
        $project              = json_decode($record->acceloProj_data);

        #$project_task = HubstaffConnect::postTasks($accelo_project_id, (array) $project, 'PROJECT');
        $milestones  = AcceloConnect::getProjectMilestones($accelo_project_id);
        /*store Milestone*/
        #dd($milestones);
        $ch = curl_init();
        HubstaffConnect::setCurl($ch);
        foreach ($milestones as $key => $accelo) {
          $milestone_id = $accelo['id'];
          $milestone_task = HubstaffConnect::postTasks($accelo_project_id, $accelo, 'MILESTONE');
          /*store milestone*/
          if ($milestone_task) {
            $success[] = $milestone_task;
          } else {
            $error[] = array('error' => 'Error in posting to hubstaff', 'api' => $accelo);
          }          
        }
      }
      return response()->json( array('success' => $success, 'error' => $error ) );
    } //postAccelo2HubstaffProjectMilestone

    public function postAccelo2HubstaffProjectMilestoneTask(){
      $error = []; $success = [];
      $records = $this->getAcceloProjects();
      $result = [];
      foreach ($records as $key => $record) {
        $accelo_project_id    = $record->accelo_project_id;
        $hubstaff_project_id  = $record->hubstaff_project_id;
        $project              = json_decode($record->acceloProj_data);

        $project_task = HubstaffConnect::postTasks($accelo_project_id, (array) $project, 'PROJECT');
        $tasks  = AcceloConnect::getProjectTasks($accelo_project_id);
        foreach ($tasks as $key => $task) {
          $new_task = false;
          if ($task['job']) {
            $new_task = HubstaffConnect::postTasks($accelo_project_id, $task, 'TASK');
          }
          if ($new_task) {
            $success[] = $new_task;
          } else {
            $error[] = array('error' => 'Error in posting to hubstaff', 'api' => $task);
          }
        }        
        $milestones  = AcceloConnect::getProjectMilestones($accelo_project_id);
        #dd($milestones);
        $ch = curl_init();
        HubstaffConnect::setCurl($ch);
        #echo "PROJECT: ".$project->title."[".$project->id."]<br />";
        foreach ($milestones as $key => $accelo) {
          #echo "--MILESTONE: ".$accelo['title']."<br />";
          $milestone_id = $accelo['id'];

          $milestone_task = HubstaffConnect::postTasks($accelo_project_id, $accelo, 'MILESTONE');
          $tasks = AcceloConnect::getMilestoneTasks($milestone_id);
          foreach ($tasks as $key => $task) {
            #echo "-------TASK ".($key+1).": ".$task['title']."<br />";
            #echo "-------ASSIGNEE: ".$task['assignee']."<br />";
            #echo "<hr />";
            /*post task to hubbstaff*/
            $new_task = HubstaffConnect::postTasks($accelo_project_id, $task, 'TASK');
            if ($new_task) {
              $success[] = $new_task;
            } else {
              $error[] = array('error' => 'Error in posting to hubstaff', 'api' => $task);
            }       
          }
        }
      }

      //return response()->json( array('success' => $success, 'error' => $error ) );
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
