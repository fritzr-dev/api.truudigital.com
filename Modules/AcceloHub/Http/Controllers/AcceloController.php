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
              $entry->acceloProj_data     = $acceloProj_data;
              $entry->hubstaffProj_data   = $hubstaffProj_data;
              $entry->save();
            }
      
          $success[] = $accelo;
        } else {
          $error[] = $accelo;
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
      dd($projects, $success, $error);
      #report to admin
      #dd($error);
      #return response()->json($result);
    } //postAccelo2HubstaffProjects

    public function getAcceloTasks(){

      $result  = AcceloConnect::getAllTasks();

      return response()->json($result);
    } //getAcceloTasks

    public function postAccelo2HubstaffProjectTasks(){

      $records = AcceloProjects::get();#->limit(1);
      foreach ($records as $key => $record) {
        $accelo_project_id    = $record->accelo_project_id;
        $hubstaff_project_id  = $record->hubstaff_project_id;
        dd($record);
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
