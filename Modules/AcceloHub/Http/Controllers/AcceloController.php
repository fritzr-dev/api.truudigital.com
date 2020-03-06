<?php

namespace Modules\AcceloHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use DB, Route;

use Modules\AcceloHub\Entities\AcceloMembers;
use Modules\AcceloHub\Entities\AccelloConnect;

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

      $result  = AccelloConnect::getStaff();

      return response()->json($result);
    } //getAcceloMembers

    public function getAcceloCompanies(){

      $result  = AccelloConnect::getCompanies();

      return response()->json($result);
    } //getAcceloCompanies

    public function getProjects(){

      $result  = AccelloConnect::getProjects();

      return response()->json($result);
    } //getAcceloJobs

    public function getAcceloTasks(){

      $result  = AccelloConnect::getTasks();

      return response()->json($result);
    } //getAcceloTasks

    public function getAcceloActivities(){

      $result  = AccelloConnect::getActivities();

      return response()->json($result);
    } //getAcceloActivities

    public function resetToken(){
      echo $result  = AccelloConnect::resetToken();
    } //resetToken

    public function developer(){

      $post = array();
      $post["grant_type"] = 'client_credentials';
      $post["scope"]      = "read(companies,jobs,tasks,activities),write(activities)";

      echo http_build_query($post);
    }
    public function status(){
      AccelloConnect::status();
    }

}
