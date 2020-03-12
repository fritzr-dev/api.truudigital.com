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

    public function postAccelo2HubstaffProjects(){
      $error = []; $success = [];

      $result  = AcceloConnect::getProjects();

      $projects = $result;
      $ch = curl_init();
      HubstaffConnect::setCurl($ch);
      foreach ($projects as $key => $accelo) {
        #dd($accelo);
        $post = array(
                "name"=> $accelo['title'], 
                "description"=> "Accelo Project ID:".$accelo['id']
                //"client_id"=> 0
              );
        $hubstaff = HubstaffConnect::postProject($post);
        /*"error" => "invalid_token"
        "error_description" => "The access token provided is expired, revoked, malformed or invalid for other reasons."*/

        $project = [];
        if (isset($hubstaff['project'])) {
          $hubstaff = $hubstaff['project'];
          $accelo_project_id   = $accelo['id'];
          $hubstaff_project_id = $hubstaff['id'];
          $acceloProj_data     = json_encode($accelo);
          $hubstaffProj_data   = json_encode($hubstaff);

            $entry = AcceloProjects::where('accelo_project_id', $accelo_project_id)->where('hubstaff_project_id', $hubstaff_project_id)->get();
            if($entry->isEmpty()){
              $project = AcceloProjects::create([
                'accelo_project_id'   => $accelo_project_id,
                'hubstaff_project_id' => $hubstaff_project_id,
                'acceloProj_data'     => $acceloProj_data,
                'hubstaffProj_data'   => $hubstaffProj_data
              ]);
            } else {
              $update_entry = AcceloProjects::find($entry->id);
              $update_entry->acceloProj_data     = $acceloProj_data;
              $update_entry->hubstaffProj_data   = $hubstaffProj_data;
              $update_entry->update();
            }
      
          $success[] = $accelo;
        } else {
          $error[] = array('error' => 'Error in posting to hubstaff', 'post' => $post, 'api' => $hubstaff);
        }
        /*saved to DB*/
        /*saved to DB END*/
        /*"members"=> [ \ 
         { \ 
           "user_id"=> 0, \ 
           "role"=> "string" \ 
         } \ 
        ], \ 
        "budget"=> { \ 
         "type"=> "cost", \ 
         "rate"=> "bill_rate", \ 
         "cost"=> 0, \ 
         "hours"=> 0, \ 
         "start_date"=> "2020-03-06", \ 
         "recurrence"=> "monthly", \ 
         "alerts"=> { \ 
           "near_limit"=> 0 \ 
         } \ 
        } \*/         
      }
      curl_close($ch);
      #dd($projects, $success, $error);
      #report to admin
      #dd($error);
      return response()->json(array('success' => $success, 'error' => $error ) );
    } //postAccelo2HubstaffProjects

    public function postAccelo2HubstaffTickets(){
      $error = []; $success = [];

      $result  = AcceloConnect::getTickets();

      $tickets = $result;
      if($tickets){

        $ch = curl_init();
        HubstaffConnect::setCurl($ch);
        $projectID = AcceloConnect::$project_ticket;
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
          $hubstaff = HubstaffConnect::postTask($projectID, $post);
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

    public function getAcceloTasks(){

      $result  = AcceloConnect::getAllTasks();

      return response()->json($result);
    } //getAcceloTasks

    public function postAccelo2HubstaffProjectMilestones(){

      $records = AcceloProjects::get();#->limit(1);
      foreach ($records as $key => $record) {
        $accelo_project_id    = $record->accelo_project_id;
        $hubstaff_project_id  = $record->hubstaff_project_id;

        $accelo_project_id = 290;
        $milestones  = AcceloConnect::getProjectMilestones($accelo_project_id);
        $ch = curl_init();
        HubstaffConnect::setCurl($ch);        
        foreach ($milestones as $key => $accelo) {
          $hubstaff = HubstaffConnect::postTasks($accelo_project_id, $accelo, 'MILESTONES');
          dd($hubstaff);
        }
        curl_close($ch);

              $ticket_task= AcceloTasks::create([
                'accelo_ticket_id'   => $accelo_ticket_id,
                'hubstaff_task_id' => $hubstaff_task_id,
                'acceloTicket_data'     => $acceloTicket_data,
                'hubstaffTask_data'   => $hubstaffTask_data
              ]);
        dd($accelo_project_id, $milestones);

        #get Milestones then Tasks
        #get Tasks
        break;
      }

      dd($records);
      #$result  = AcceloConnect::getTasks();
      return response()->json($result);
    } //postAcceloTasks

    public function getAcceloActivities(){

      $result  = AcceloConnect::getActivities();

      return response()->json($result);
    } //getAcceloActivities

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
