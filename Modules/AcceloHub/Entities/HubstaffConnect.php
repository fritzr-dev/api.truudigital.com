<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\AcceloHub\Entities\AcceloTasks;
use Modules\AcceloHub\Entities\AcceloSchedule;

Use Carbon\Carbon;

class HubstaffConnect extends Model
{

    static $return_error            = false;
    static $cUrl_run    = 0;
    static $postTask    = 0;

    static $retoken   = 0;
    static $user_agent = 'TruuDigital';
    static $apiCurl    = false;
    static $lastCurl   = [];

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
            $client_credentials = base64_encode(config('accelohub.serviceClientID').":".config('accelohub.serviceClientSecret'));
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

      #dd($response, $headers, $url);
      return json_decode($response, true);
    } //apiRequest

    public static function apiPost($url, $post=FALSE, $headers=array()) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_POST, TRUE);

      if($post)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

      $headers = [
        //'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: '.self::$user_agent,
      ];

        if(isset($_SESSION['access_token'])) {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
        }

      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $response = curl_exec($ch);
      self::countCurl();
      return json_decode($response, true);
    } //apiPost

    public static function session_start(){
        if (!session_id()) session_start();
    }//setCurl

    public static function countCurl(){
        self::$cUrl_run =  self::$cUrl_run + 1;
    }//countCurl
    public static function setCurl($ch){
        self::$apiCurl = $ch;
    }//setCurl

    public static function apiPostInitCurl($url, $post=FALSE, $type='') {
      #$ch = curl_init($url);
        $headers = [
        'Accept: application/json',
        'User-Agent: '.self::$user_agent
        ];

      $ch = self::$apiCurl;
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

      if($type == 'project'){
        $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: '.self::$user_agent
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
      } else {
          if($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
      }

        self::session_start();
        if(isset($_SESSION['access_token'])) {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $response = curl_exec($ch);
      self::$lastCurl = $response;
      return json_decode($response, true);
    } //apiPostInitCurl

    /*Refresh token HUBSTAFF*/
    public static function refreshToken(){
        $code   = config('accelohub.personal_access_tokens');

        // Exchange the auth code for an access token
        $token = self::apiRequest(config('accelohub.tokenURL'), array(
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
        /*check API ERROR*/
        if (isset($result['error']) && $result['error'] == 'invalid_token') {
            if (self::$retoken == 0) {
                self::$retoken = 1;
                self::refreshToken();
                $result = self::apiRequest($url);
            } 
        } else {
            self::$retoken = 0;
        }        
        /*check API ERROR END*/
        #dd($result);
        $data = [];
        if(isset($result['error'])) {            
			if(config('accelohub.return_error')) {
            	$data = $result;
			}
        } else {
            $data = isset($result[$list]) ? $result[$list] : reset($result);
        }

        return $data;
    }

    public static function getPageResults($url, $list, $next_page_start_id=''){

        self::getToken();
        #$url = $url."&page_limit=50";
        $url = $next_page_start_id ? $url."&page_start_id=$next_page_start_id" : $url;
        #echo "$url <br />:";
        $result = self::apiRequest($url);
        /*check API ERROR*/
        if (isset($result['error']) && $result['error'] == 'invalid_token') {
            if (self::$retoken == 0) {
                self::$retoken = 1;
                self::refreshToken();
                $result = self::apiRequest($url);
            } 
        } else {
            self::$retoken = 0;
        }        
        /*check API ERROR END*/
        #dd($result);
        $data = [];
        if(isset($result['error'])) {            
            if(config('accelohub.return_error')) {
                $data = $result;
            }
        } else {
            if(isset($result['pagination'])) {
                $next_page_start_id = $result['pagination']['next_page_start_id'];
                unset($result['pagination']);
                $data = isset($result[$list]) ? $result[$list] : reset($result);
                // get new page
                $page_records = self::getPageResults($url, $list, $next_page_start_id);

                $data = array_merge($data, $page_records);
            } else {
                $data = isset($result[$list]) ? $result[$list] : reset($result);
            }
        }

        return $data;
    }  //getPageResults  

    public static function getUser($user_id){

        $url = "https://api.hubstaff.com/v2/users/$user_id";

        $result = self::getResults($url, 'user');
        return $result;

    } //getOrganizationMembers

    public static function getOrganizationMembers(){

        $url = "https://api.hubstaff.com/v2/organizations/".config('accelohub.organization_id')."/members?page_limit=50";

        $result = self::getResults($url, 'members');

        return $result;

    } //getOrganizationMembers

    function getClients(){
        $url = "https://api.hubstaff.com/v2/organizations/".config('accelohub.organization_id')."/clients?status=active";
        $result = self::getResults($url, 'clients');

        return $result;
    } //getClients

    public static function getProjects(){
        $url = "https://api.hubstaff.com/v2/organizations/".config('accelohub.organization_id')."/projects";
        $result = self::getResults($url, 'projects');

        return $result;
    } //getProjects

    public static function getProject($id){
        $url = "https://api.hubstaff.com/v2/projects/".$id."/members";
        $result = self::getResults($url, 'members');

        return $result;
    } //getProject

    public static function postProject($accelo){

        
        $members = AcceloSchedule::$hubstaff_members;

        /*$manager = $accelo['manager'];
        $manager = AcceloMembers::get_HID_byAID($manager);
        if($manager) {
            $members[] = array("user_id" => $manager, "role"=> "manager");
        }*/
        #$manager = $manager ? $manager : config('accelohub.default_user');
        #$members = array();
        #$members[] = array("user_id" => config('accelohub.default_user'), "role"=> "user");

        #dd($members);

        $post = array(
                "name"          => "PRJ-".$accelo['id']." ".$accelo['title'], 
                "description"   => "Accelo ID:".$accelo['id'],
                "client_id"     => config('accelohub.default_client'),
                "members"       => $members
              );
        #dd($post);
        //manager company
        $url = "https://api.hubstaff.com/v2/organizations/".config('accelohub.organization_id')."/projects";
        $result = self::apiPostInitCurl($url, $post, 'project');
        if (isset($result['error'])) {
            if ($result['error'] == 'invalid_token') {
                if (self::$retoken == 0) {
                    self::$retoken = 1;
                    self::refreshToken();
                    $result = self::apiPostInitCurl($url, $post, 'project');
                } 
            }
            $result['post'] = $post;
        } else {
            self::$retoken = 0;
        }

        #dd($result, $post);
        return $result;
    } //postProject

    public static function postTasksDB($accelo_project_id, $accelo, $type='TASK'){
        $error = ''; $success = ''; $result = ''; $migrated = '';

        $accelo_id = $accelo['id'];
        $entry = AcceloTasks::where('accelo_task_id', $accelo_id)->first();

        $accelo_data = json_encode($accelo);
        if(!$entry){
            $post_task = [
                              'project_id'          => $accelo_project_id,
                              'accelo_task_id'      => $accelo_id,
                              'hubstaff_task_id'    => '',
                              'acceloTask_data'     => $accelo_data,
                              'hubstaffTask_data'   => '',
                              'type'                => $type,
                              'status'              => 0,
                            ];
            //dd($post_task);
            $new_task = AcceloTasks::create($post_task);
            if($new_task) {
                $success = $accelo;
            } else {
                $error = array('error' => 'Error in saving to hubstaff DB', 'api' => $accelo);
            }
        } else if($entry->status == 0) {
            $update_entry = AcceloTasks::find($entry->id);
            $update_entry->type             = $type;
            $update_entry->acceloTask_data  = json_encode($accelo);
            $update_entry->update();
            $migrated = array('error' => 'Pending Migration', 'api' => $accelo);
        } else {
            $migrated = array('error' => 'Already Migrated', 'api' => $accelo);
        }
        self::$postTask = self::$postTask +1;
        return array('success' => $success, 'error' => $error, 'migrated' => $migrated );
    } //postTasks

    public static function postTasks($accelo_project_id, $accelo, $type='TASK'){
        $postResult = array('success' => false, 'error' => false, 'data' => []);

        $assignee   = isset($accelo['assignee']) ? $accelo['assignee'] : $accelo['manager'];
        $members    = AcceloMembers::get_HID_byAID($assignee);

        $members    = $members ? $members : config('accelohub.default_user');

        $title = isset($accelo['title']) ? $accelo['title'] : '';
        $description = isset($accelo['description']) ? " Description: ".$accelo['description'] : '';
        $post = array(
              "name"        => $type."-".$accelo['id'].": ".$title, 
              "summary"     => $type."-".$accelo['id']." :: ".$title.$description,
              "description" => "Accelo $type ID:".$accelo['id'].". ".$description,
              'assignee_id' => $members 
            );
        $url = "https://api.hubstaff.com/v2/projects/$accelo_project_id/tasks";

        /*if ($type == 'PROJECT') {
            $style = 'style="padding-left: 50px; font-weight: bold; font-size: 15px;"';
        } else if ($type == 'MILESTONE') {
            $style = 'style="padding-left: 100px; font-style: italic; font-size: 14px;"';
        } else if ($type == 'TASK') {
            $style = 'style="padding-left: 150px;fonts-size: 11px;"';
        } else if ($type == 'TICKET') {
            $style = 'style="padding-left: 50px; font-weight: bold;font-style: italic;"';
        }
        #echo '<pre '.$style.'>'; print_r($post);echo '</pre>'; return '';
        echo "$url $assignee <br />"; print_r($post); return '';*/

        $result = self::apiPostInitCurl($url, $post); 

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
        #dd($accelo, $hubstaff, $url, $post);
        if(isset($hubstaff['task'])) {
            $postResult['success']  = true;
            $postResult['data']     = $hubstaff['task'];
        } else {
            $postResult['error'] = false;
            $post['api'] = $accelo;
            $postResult['data']  = array('api' => $hubstaff, 'post' => $post);
        }

        return $postResult;
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
    } //postTask

    public static function getTasks(){
        $url = "https://api.hubstaff.com/v2/organizations/".config('accelohub.organization_id')."/tasks";
        $result = self::getResults($url, 'tasks');

        return $result;
    } //getTasks

    /*developer use*/
    public static function getProjectActivities($project_id){
        $time_slot = array();
        $time_slot['start'] = date('Y-m-d\TH:i:sO', strtotime("-1 months"));
        $time_slot['stop']  = date('Y-m-d\TH:i:sO');

        echo $url = "https://api.hubstaff.com/v2/organizations/".config('accelohub.organization_id')."/activities?time_slot[start]=".date('Y-m-d\TH:i:sO', strtotime("-7 days"))."&time_slot[stop]=".date('Y-m-d\TH:i:sO');
        #dd($url);
        $result = self::getResults($url, 'activities');

        return $result;
    } //getProjectActivities

    public static function getActivities(){

        $start  = Carbon::now()->subDays(7); 
        $end    = Carbon::now(); 
        #dd("$start, $end");
        $end = date('Y-m-d\TH:i:sP');
        $start = date('Y-m-d', strtotime("-7 days"));
        $end = date('Y-m-d');
        $url = "https://api.hubstaff.com/v2/organizations/".config('accelohub.organization_id')."/activities?time_slot[start]=".$start."&time_slot[stop]=".$end;

        $result = self::getResults($url, 'activities');
        return $result;
    } //getActivities  

    public static function getNotes(){

        $start  = Carbon::now()->subDays(7); 
        $end    = Carbon::now(); 
        $start  = "2020-03-22";
        $end    = "2020-03-28";
        
        $start_time     = date(DATE_ISO8601, strtotime($start));
        $stop_time      = date(DATE_ISO8601, strtotime($end));

        $url = "https://api.hubstaff.com/v2/organizations/".config('accelohub.organization_id')."/notes?time_slot[start]=".$start_time."&time_slot[stop]=".$stop_time;

        $result = self::getResults($url, 'notes');
        dd("NOTES", $url, $result);
        return $result;
    } //getNotes  

    public static function getTimesheets(){

        $start  = Carbon::now()->subDays(7); 
        $end    = Carbon::now(); 

        $start  = "2020-03-26";
        $end    = "2020-03-26";
        $start_time     = date(DATE_ISO8601, strtotime($start));
        $stop_time      = date(DATE_ISO8601, strtotime($end));
        echo "$start $end <br /> <br />";

        echo $url = "https://api.hubstaff.com/v2/organizations/".config('accelohub.organization_id')."/timesheets?date[start]=".$start_time."&date[stop]=".$stop_time;
        $org_timesheets = self::getPageResults($url, 'timesheets');
        $timesheets_users = [];
        $timesheets = [];
        $user_ids   = [];
        $timesheets_ids   = [];
        foreach ($org_timesheets as $key => $data) {
            $data['activities'] = false;
            $timesheets[$data['id']] = $data;
            $user_id = $data['user_id'];
            $timesheets_users[$user_id][$data['id']] = $data;
            $timesheets_ids[] = $data['id'];
        }
        foreach ($timesheets_users as $user_id => $user) {
            $user_ids[] = $user_id;
        }

        $Cuser_ids = implode(",", $user_ids);
        $url = "https://api.hubstaff.com/v2/organizations/".config('accelohub.organization_id')."/activities?time_slot[start]=".$start."&time_slot[stop]=".$end."&user_ids=$Cuser_ids";
        $activities = self::getPageResults($url, 'activities');    

        /*$url = "https://api.hubstaff.com/v2/organizations/".config('accelohub.organization_id')."/notes?time_slot[start]=".$start."&time_slot[stop]=".$end."&user_ids=$Cuser_ids";
        $notes = self::getPageResults($url, 'notes');  
        $timesheet_notes = [];
        foreach ($notes as $key => $note) {

            $timesheet_id = $activity['timesheet_id'];
            $timesheet_activities[$timesheet_id][] = $activity;
        } */
        /*
        foreach ($activities as $key => $activity) {
            $timesheet_id = $activity['timesheet_id'];
            if(isset($timesheets[$timesheet_id])) {

                $t_activities = $timesheets[$timesheet_id]['activities'];
                $t_activities[] = $activity;
                $timesheets[$timesheet_id]['activities'] = $t_activities;
            } else {
                $timesheets[$timesheet_id]['activities'] = "no activity";
            }
            $timesheet_activities[$timesheet_id][] = $activity;
        }*/

        $timesheet_activities = [];
        foreach ($activities as $key => $activity) {

            $timesheet_id = $activity['timesheet_id'];
            $timesheet_activities[$timesheet_id][] = $activity;
        }  

        foreach ($timesheets_users as $user_id => $user) {
            foreach ($user as $t_id => $sheet) {
                if (isset($timesheet_activities[$t_id])) {
                    $timesheets_users[$user_id][$t_id]['activities'] = $timesheet_activities[$t_id];
                }
            }
        }

        /*$table = '';
        foreach ($timesheet_activities as $timesheet_id => $activities) {
            $row = '';
            foreach ($activities as $key => $values) {
                $td = '';
                $head = '';
                foreach ($values as $key => $value) {
                    $head .= "<th>$key</th>";
                    $td .= "<td>$value</td>";
                }
                $row .= "<tr>$td</tr>";
                $head = "<tr>$head</tr>";
            }
            $table .= "<h3>$timesheet_id</h3>
                    <table cellspacing='2' cellpadding='2' border='1'>
                        $head
                        $row   
                    </table>";
        }*/
        $table = '';
        $row = '';
        foreach ($org_timesheets as $key => $values) {
            $td = '';
            $head = '';
            foreach ($values as $key => $value) {
                $head .= "<th>$key</th>";
                $td .= "<td>$value</td>";
            }
            $row .= "<tr>$td</tr>";
            $head = "<tr>$head</tr>";
        }
        $table .= "
                <table cellspacing='2' cellpadding='2' border='1'>
                    $head
                    $row   
                </table>";
        echo $table;
        $table = '';
        $row = '';
        foreach ($activities as $key => $values) {
            $td = '';
            $head = '';
            foreach ($values as $key => $value) {
                $head .= "<th>$key</th>";
                $td .= "<td>$value</td>";
            }
            $row .= "<tr>$td</tr>";
            $head = "<tr>$head</tr>";
        }
        $table .= "
                <table cellspacing='2' cellpadding='2' border='1'>
                    $head
                    $row   
                </table>";
        echo $table;
        dd("USERS LOGS ",$timesheets_users, "ACTIVITIES GROUP BY timesheet ID", $timesheet_activities);
        return $result;


        $start  = '2020-03-16 12:24:45';
        $end    = date('Y-m-d H:i:s',strtotime('+7 hours',strtotime($start)));
        #echo "<br />DATE $start to $end <br />"; 
        $start  = strtotime($start);
        $end    = strtotime($end);
        $nonbillable = $end - $start;

        $timesheets = array();
        #1 client 3 Internal
        $class_id = 3;
        $timesheets[] = array(
                          'subject'     => 'Time Entry - #2619 Setup integration',
                          'against_id'  => '2619',
                          'task_id'     => '2619',
                          'against_type' => 'task',
                          'body'        => 'hubstaff to accelo via API',
                          'owner_id'    => '13',
                          'details'     => 'hubstaff to accelo via API',
                          'time_allocation' => '2619',
                          'medium'          => 'note',
                          'nonbillable'     => $nonbillable,
                          'visibility'      => 'all',
                          'date_started'    => $start,
                          //'date_logged'   => '1584361485',
                          'class_id'        => $class_id
                        );
        return $timesheets;

    } //getTimesheets    

}
