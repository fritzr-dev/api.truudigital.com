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

      $data = [];
      if(isset($result['meta']['status']) && $result['meta']['status'] == 'ok') {
        $data = $result['response'];
      } else {
        $data = $result;
      }

      return response()->json($data);
    } //getAcceloMembers

    public function getAcceloCompanies(){

      $result  = AccelloConnect::getCompanies();

      $data = [];
      if(isset($result['meta']['status']) && $result['meta']['status'] == 'ok') {
        $data = $result['response'];
      } else {
        $data = $result;
      }

      return response()->json($data);
    } //getAcceloCompanies

    public function getProjects(){

      $result  = AccelloConnect::getProjects();

      $data = [];
      if(isset($result['meta']['status']) && $result['meta']['status'] == 'ok') {
        $data = $result['response'];
      } else {
        $data = $result;
      }

      return response()->json($data);
    } //getAcceloJobs

    public function getAcceloTasks(){

      $result  = AccelloConnect::getTasks();

      $data = [];
      if(isset($result['meta']['status']) && $result['meta']['status'] == 'ok') {
        $data = $result['response'];
      } else {
        $data = $result;
      }

      return response()->json($data);
    } //getAcceloTasks

    public function getAcceloActivities(){

      $result  = AccelloConnect::getActivities();

      $data = [];
      if(isset($result['meta']['status']) && $result['meta']['status'] == 'ok') {
        $data = $result['response'];
      } else {
        $data = $result;
      }

      return response()->json($data);
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
