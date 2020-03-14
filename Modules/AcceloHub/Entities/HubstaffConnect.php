<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\AcceloHub\Entities\AcceloTasks;

class HubstaffConnect extends Model
{
    public static $serviceClientID        = 'HDATdk8oi6ZJjmw8o6rGl5XIa2g_tCnQPpo4xblsObc';
    public static $serviceClientSecret    = 'Zjpy15HX_6Wvak5u8uEHIZbWEgtWx1OWIMuPyJGMPT65MkiVgKb9SNHKq-nR_hmQIJTGVO254fjGyrDT2tqpPw';
    public static $serviceRedirectURL     = 'http://localhost:8000/hubstaff/oauth';
    public static $serviceConnectURL      = 'http://localhost:8000/hubstaff/connect';
    // This is the endpoint our server will request an access token from
    public static $tokenURL   = 'https://account.hubstaff.com/access_tokens';
    // This is the hubstaff base URL we can use to make authenticated API requests
    public static $apiURLBase       = 'https://api.hubstaff.com/v2/';
    public static $organization_id  = 239610;
    public static $default_user     = 788805;
    static $return_error            = false;
    static $cUrl_run     = 0;

    static $personal_access_tokens  = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6ImRlZmF1bHQifQ.eyJqdGkiOiJVRktFazcxSCIsImlzcyI6Imh0dHBzOi8vYWNjb3VudC5odWJzdGFmZi5jb20iLCJleHAiOjE1OTEyOTg1ODcsImlhdCI6MTU4MzUyNjE4Nywic2NvcGUiOiJvcGVuaWQgcHJvZmlsZSBlbWFpbCBodWJzdGFmZjpyZWFkIGh1YnN0YWZmOndyaXRlIn0.t2xwLfEIdklsQ_pEPwOSwxiYuaGiHZeNubEuSYhrOPEah6eJMfzTnXibMurygqV3NAXZSSi52db6c_dUJjfyDMafR9z0YDRPtgNCzmxyCSlpJAYv3IzfkPOC4qLkbyYI-6aG4NkD9M-Uh96IF-VEAzg5_nygFPIlqPf7671omJdhAF02llrrIrxkP3g1pCQfxB1Edz1f-iZzgY0Ob0Ni8OkSDzMPQVSzTXyw3txZmpADMuj1X-r6pK84c2Li3bslkO7uu5yldrOd5XL-IUydb-vB_3k44flXaYEgzRYl4DJVOvkhMTLrrMHRqnmAmKHLil8WvGP9AFv__AoUYBunhA';

    static $retoken   = 0;
    static $user_agent = 'TruuDigital';
    static $apiCurl    = false;

    public function __construct()
    {
        if (!session_id()) session_start();
    }

    public static function apiRequest($url, $post=FALSE, $headers=array()) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

      if($post)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

      $headers = [
        'Accept: application/json',
        'User-Agent: '.self::$user_agent
      ];

        if(isset($post['grant_type']) && $post['grant_type'] == 'authorization_code' ){
            $client_credentials = base64_encode($this->serviceClientID.":".$this->serviceClientSecret);
            $headers[] = 'Authorization: Basic ' . $client_credentials;
        } else if(isset($post['grant_type']) && $post['grant_type'] == 'refresh_token' ){
            $client_credentials = base64_encode($post['refresh_token']);
            $headers[] = 'Authorization: Basic ' . $client_credentials;
        } else if(isset($_SESSION['access_token'])) {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
        }
        
      curl_setopt($ch, CURLOPT_TIMEOUT, 0);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $response = curl_exec($ch);
      return json_decode($response, true);
    } //apiRequest

    public static function apiPost($url, $post=FALSE, $headers=array()) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

      if($post)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

      $headers = [
        'Accept: application/json',
        'User-Agent: '.self::$user_agent
      ];

        if(isset($_SESSION['access_token'])) {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
        }

      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $response = curl_exec($ch);
      return json_decode($response, true);
    } //apiRequest

    public static function setCurl($ch){
        self::$apiCurl = $ch;
    }//setCurl
    public static function apiPostInitCurl($url, $post=FALSE, $headers=array()) {
      #$ch = curl_init($url);
      $ch = self::$apiCurl;
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

      if($post)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

      $headers = [
        'Accept: application/json',
        'User-Agent: '.self::$user_agent
      ];

        if(isset($_SESSION['access_token'])) {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
        }

      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $response = curl_exec($ch);
      self::$cUrl_run =  self::$cUrl_run + 1;
      return json_decode($response, true);
    } //apiRequest

    /*Refresh token HUBSTAFF*/
    public static function refreshToken(){
        $code   = self::$personal_access_tokens;

        // Exchange the auth code for an access token
        $token = self::apiRequest(self::$tokenURL, array(
          'grant_type'    => 'refresh_token',
          'refresh_token' => $code
        ));
        $access_token = '';
        if (isset($token['access_token'])) {
            $_SESSION['access_token'] = $token['access_token'];
            $access_token = $token['access_token'];
        }  else if (isset($token['error'])) {
            $_SESSION['token_details'] = $token;
        }
        return $access_token;
    }//refreshToken()

    public static function getToken(){
        if (isset($_SESSION['access_token'])) {
            return $_SESSION['access_token'];
        }  else if (isset($token['error'])) {
            return self::refreshToken();
        }
    } //getToken()

    public static function getResults($url, $list){

        self::getToken();

    	$result = self::apiRequest($url);

        $data = [];
        if(isset($result['error'])) {
			if(self::$return_error) {
            	$data = $result;
			}
        } else {
            $data = $result[$list];
        }

        return $data;
    }

    public static function getUser($user_id){

        $url = "https://api.hubstaff.com/v2/users/$user_id";

        $result = self::getResults($url, 'user');

        return $result;

    } //getOrganizationMembers

    public static function getOrganizationMembers(){

        $url = "https://api.hubstaff.com/v2/organizations/".self::$organization_id."/members?page_limit=50";

        $result = self::getResults($url, 'members');

        return $result;

    } //getOrganizationMembers

    function getClients(){
        $url = "https://api.hubstaff.com/v2/organizations/".self::$organization_id."/clients?status=active";
        $result = self::getResults($url, 'clients');

        return $result;
    } //getClients

    public static function getProjects(){
        $url = "https://api.hubstaff.com/v2/organizations/".self::$organization_id."/projects";
        $result = self::getResults($url, 'projects');

        return $result;
    } //getProjects

    public static function postProject($accelo){

        $manager = $accelo['manager'];
        $manager = AcceloMembers::get_HID_byAID($manager);
        $manager = $manager ? $manager : self::$default_user;

        $members = array();
        $members[] = array("user_id" => $manager, "role"=> "manager");
        $post = array(
                "name"          => "P-".$accelo['id']." ".$accelo['title'], 
                "description"   => "Accelo ID:".$accelo['id'],
                //"members"       => $members,
                //"client_id"=> 0
              );
        //manager company
        $url = "https://api.hubstaff.com/v2/organizations/".self::$organization_id."/projects";
        $result = self::apiPost($url, $post);
        if (isset($result['error']) && $result['error'] == 'invalid_token') {
            if (self::$retoken == 0) {
                self::$retoken = 1;
                self::refreshToken();
                $result = self::apiPostInitCurl($url, $post);
            } 
        } else {
            self::$retoken = 0;
        }

        return $result;
    } //postProject


    public static function postTasks($accelo_project_id, $accelo, $type='TASK'){
        $error = ''; $success = ''; $result = ''; $migrated = '';

        $accelo_id = $accelo['id'];
        $new_entry = false; $update_entry = false;

        $entry = AcceloTasks::where('accelo_task_id', $accelo_id)->first();

        if(!$entry){
            $new_entry = true;
        } else if($entry->status == 0) {
            $update_entry = true;
        }
        if($new_entry || $update_entry){

            $assignee   = isset($accelo['manager']) ? $accelo['manager'] : $accelo['assignee'];
            $members    = AcceloMembers::get_HID_byAID($assignee);
            $members    = $members ? $members : self::$default_user;

            $title = isset($accelo['title']) ? $accelo['title'] : '';
            $description = isset($accelo['description']) ? " Description: ".$accelo['description'] : '';
            $post = array(
                  "name"        => $type."-".$accelo['id'].": ".$title, 
                  "summary"     => $type."-".$accelo['id']." :: ".$title.$description,
                  "description" => "Accelo Ticket ID:".$accelo['id'].". ".$description,
                  'assignee_id' => $members 
                );

            /*if ($type == 'PROJECT') {
                $style = 'style="padding-left: 50px; font-weight: bold; font-size: 15px;"';
            } else if ($type == 'MILESTONE') {
                $style = 'style="padding-left: 100px; font-style: italic; font-size: 14px;"';
            } else if ($type == 'TASK') {
                $style = 'style="padding-left: 150px;fonts-size: 11px;"';
            } else if ($type == 'TICKET') {
                $style = 'style="padding-left: 50px; font-weight: bold;font-style: italic;"';
            }
            echo '<pre '.$style.'>'; print_r($post);echo '</pre>'; return '';*/
            #dd($post, $accelo);

            $url = "https://api.hubstaff.com/v2/projects/$accelo_project_id/tasks";
            $result = self::apiPost($url, $post);
            if (isset($result['error']) && $result['error'] == 'invalid_token') {
                if (self::$retoken == 0) {
                    self::$retoken = 1;
                    self::refreshToken();
                    $result = self::apiPostInitCurl($url, $post);
                } 
            } else {
                self::$retoken = 0;
            }
            $hubstaff = $result;
            #dd($hubstaff);
            if(isset($hubstaff['tasks'])) {

                $hubstaff = $hubstaff['tasks'];
                $accelo_id      = $accelo['id'];
                $hubstaff_id    = $hubstaff['id'];
                $accelo_data    = json_encode($accelo);
                $hubstaff_data  = json_encode($hubstaff);

                $hubstaff_project_id = 1;
                if($new_entry){

                    AcceloTasks::create([
                      'project_id'          => $accelo_project_id,
                      'accelo_task_id'      => $accelo_id,
                      'hubstaff_task_id'    => $hubstaff_id,
                      'acceloTask_data'     => $accelo_data,
                      'hubstaffTask_data'   => $hubstaff_data,
                      'type'                => $type,
                      'status'              => 1,
                    ]);

                    $success = $hubstaff;

                } else if ($update_entry) {
                    $update_entry = AcceloTasks::find($accelo->id);
                    $update_entry->acceloTask_data  = json_encode($accelo);
                    $update_entry->update();
                    $migrated = array('error' => 'Migrated', 'api' => $hubstaff);
                }
            } else {
                $error = array('error' => 'Error in posting to hubstaff', 'api' => $accelo);
            }

        } else {
            $migrated = array('error' => 'Already Migrated', 'api' => $accelo);
        }
        return array('success' => $success, 'error' => $error, 'migrated' => $migrated );
    } //postTasks

    public static function postTask($project_id, $post){

        $url = "https://api.hubstaff.com/v2/projects/$project_id/tasks";
        #dd($post);
        $result = self::apiPost($url, $post);
        if (isset($result['error']) && $result['error'] == 'invalid_token') {
            if (self::$retoken == 0) {
                self::$retoken = 1;
                self::refreshToken();
                $result = self::apiPostInitCurl($url, $post);
            } 
        } else {
            self::$retoken = 0;
        }

        return $result;
    } //postProject
    public static function getTasks(){
        $url = "https://api.hubstaff.com/v2/organizations/".self::$organization_id."/tasks";
        $result = self::getResults($url, 'tasks');

        return $result;
    } //getTasks

    public static function getActivities(){
        $time_slot = array();
        $time_slot['start'] = date('Y-m-d\TH:i:sO', strtotime("-1 months"));
        $time_slot['stop']  = date('Y-m-d\TH:i:sO');

        $url = "https://api.hubstaff.com/v2/organizations/".self::$organization_id."/activities?time_slot[start]=".date('Y-m-d\TH:i:sO', strtotime("-7 days"))."&time_slot[stop]=".date('Y-m-d\TH:i:sO');
        #dd($url);
        $result = self::getResults($url, 'activities');

        return $result;
    } //getActivities

}
