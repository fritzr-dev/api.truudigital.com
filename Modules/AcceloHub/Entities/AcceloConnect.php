<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;
use Session;
use Request;

class AcceloConnect extends Model
{
    //protected $fillable = [];
    //static $client_ID 		= '1177d277ef@truudigital.accelo.com';
    //static $client_secret 	= 'hkOxhZz2BvbfCxJAFqgrw9Hs3ZOGigH8';
    //static $project_ticket 	= '940266';
    //static $return_error = false;

    static $client_token = [];
    static $access_token = '';
    static $apiCurl      = false;
    //static $limit      	 = 50;
    static $cUrl_run     = 0;
    static $cUrl_error   = '';
    static $lastCurl   = [];

    public function __construct()
    {
        if (!session_id()) session_start();
    }

	public static function getToken(){
		#$token = session('ACCELO_TOKEN');
		if (!session_id()) session_start();

		$token = isset($_SESSION['ACCELO_TOKEN'])? $_SESSION['ACCELO_TOKEN'] : '';

		if($token) {
			self::$access_token = $_SESSION['ACCELO_TOKEN'];
			return $token;
		} else {
			return self::oauth();
		}
	} //getToken

	public static function resetToken(){
		if (!session_id()) session_start();

		if (isset($_SESSION['ACCELO_TOKEN'])) {
			unset($_SESSION['ACCELO_TOKEN']);
		}

		/*Session::forget('ACCELO_CLIENT');
		Session::forget('ACCELO_TOKEN');*/
		self::$client_token = '';
		self::$access_token = '';

		return self::oauth();
	} //resetToken

	public static function status(){
		if (!session_id()) session_start();

		echo '<pre>';
		if (isset($_SESSION['ACCELO_TOKEN'])) {
			print_r($_SESSION['ACCELO_TOKEN']);
		}
		if (isset($_SESSION['ACCELO_CLIENT'])) {
			print_r($_SESSION['ACCELO_CLIENT']);
		}

		echo '</pre>';

		#return self::oauth();
	} //resetToken

	public static function oauth(){

		$client_credentials = base64_encode(config('accelohub.client_ID').":".config('accelohub.client_secret'));

		$curl = curl_init();

		$post = [];
		$post["grant_type"] = 'client_credentials';
		$post["scope"] 		= "read(staff,companies,jobs,tasks,activities,milestones,issues),write(activities)";
		$post_data = http_build_query($post);
		$post_data = "grant_type=client_credentials&scope=read%28staff%2Ccompanies%2Cjobs%2Ctasks%2Cactivities%2Cmilestones%2Cissues%29%2Cwrite%28activities%29";

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://truudigital.api.accelo.com/oauth2/v0/token",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,	
		  CURLOPT_CUSTOMREQUEST => "POST",
  		  CURLOPT_POSTFIELDS => $post_data, 
		  CURLOPT_HTTPHEADER => array(
		    "Content-Type: application/x-www-form-urlencoded",
		    "authorization: Basic $client_credentials"
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$result = (json_decode($response, true));

		self::$client_token = $result;
		self::$access_token = $result['access_token'];

		if (!session_id()) session_start();

		$_SESSION['ACCELO_CLIENT'] = self::$client_token;
		$_SESSION['ACCELO_TOKEN'] = self::$access_token;

		return self::$access_token;

		Session::put('ACCELO_CLIENT', self::$client_token);
		Session::put('ACCELO_TOKEN', self::$access_token);

	} //oauth

	public static function logCurl($post_url){
      if (isset($_SESSION['CURL_URLS'])) {
      	$urls = $_SESSION['CURL_URLS'];
      	$urls[] = $post_url;
      } else {
      	$urls = array(); 
      	$urls[] = $post_url;
      }
		$_SESSION['CURL_URLS'] = $urls;		
	} //logCurl
	public static function curlAccelo($params = array()){

		$access_token = self::getToken();

		$post_url 	= $params['url'];
		$post_type 	= $params['type'];
		$post_data 	= $params['data'];

      	$curl = curl_init();

		  curl_setopt_array($curl, array(
		    CURLOPT_URL => $post_url,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_ENCODING => "",
		    CURLOPT_MAXREDIRS => 10,
		    CURLOPT_TIMEOUT => 0,
		    CURLOPT_FOLLOWLOCATION => true,
		    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		    CURLOPT_CUSTOMREQUEST => $post_type,
			CURLOPT_POSTFIELDS => $post_data,
		    CURLOPT_HTTPHEADER => array(
		      "Content-Type: application/x-www-form-urlencoded",
		      "Authorization: Bearer $access_token"
		    ),
		  ));

      $response = curl_exec($curl);

      curl_close($curl);
      #dd($response);
      $result = (json_decode($response, true));

      self::logCurl($post_url);
      
      return $result;
	}//curlAccelo

	public static function getResult($params){

		$result = self::curlAccelo($params);
		$data = [];
		if(isset($result['meta']['status']) && $result['meta']['status'] == 'ok') {
				$data = $result['response'];
		} else {
			if(config('accelohub.return_error')) {
				$data = $result;
			}
		}
		#dd(count($data));
		return $data;
	}//getResult

	public static function MultiplecurlAccelo($params = array()){

		$access_token = self::getToken();

		$post_url 	= $params['url'];
		$post_type 	= $params['type'];
		$post_data 	= $params['data'];

      	$curl = self::$apiCurl;

		  curl_setopt_array($curl, array(
		    CURLOPT_URL => $post_url,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_ENCODING => "",
		    CURLOPT_MAXREDIRS => 10,
		    CURLOPT_TIMEOUT => 0,
		    CURLOPT_FOLLOWLOCATION => true,
		    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		    CURLOPT_CUSTOMREQUEST => $post_type,
			CURLOPT_POSTFIELDS => $post_data,
		    CURLOPT_HTTPHEADER => array(
		      "Content-Type: application/x-www-form-urlencoded",
		      "Authorization: Bearer $access_token"
		    ),
		  ));

      $response = curl_exec($curl);
      $result = (json_decode($response, true));
      #dd($post_url, $result);
		$data = [];
		if(isset($result['meta']['status']) && $result['meta']['status'] == 'ok') {
				$data = $result['response'];
		} else {
			if(config('accelohub.return_error')) {
				$data = $result;
			}
			self::$cUrl_error = $result['meta']['message'];
		}

        self::$cUrl_run =  self::$cUrl_run + 1;
		self::logCurl($post_url);
		self::$lastCurl = $result;

		return $data;
	}//curlAccelo

    public static function setCurl($ch){
        self::$apiCurl = $ch;
    }//setCurl

	public static function getStaff(){

		$post = [];
		$post["_limit"] 	= config('accelohub.limit');
		$post["_fields"] 	= "firstname, surname,mobile,email, position,standing,username";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/staff";
		$params['type'] = "GET";
		$params['data']	= $post_data;

		return self::getResult($params);

	} //getStaff

	public static function getCompanies(){

		$post = [];
		$post["_limit"] 	= config('accelohub.limit');
		$post["_fields"] 	= "_ALL";
		$post["_filters"] 	= "standing(active)";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/companies";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	return self::getResult($params);

	} //getCompanies

	public static function getProject($id){

		$post = [];
		$post["_fields"] 	= "_ALL";
		//$post["_filters"] 	= "standing(active)";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/jobs/$id";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	return self::getResult($params);

	} //getProjects

	public static function getProjectMilestones($project_id){

		$limit = config('accelohub.limit');

		$post = [];
		$post["_filters"] 	= "job($project_id)";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/milestones/count";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	$count = self::getResult($params);
      	#$count = $count['count'];

      	$records = array();
  		$pages = ceil($count / $limit);

      	$ch = curl_init();
      	self::setCurl($ch);

      	for ($p=0; $p < $pages; $p++) {
			$post = [];
			$post["_fields"] 	= "_ALL";
			$post["_limit"] 	= $limit;
			$post["_filters"] 	= "job($project_id)";

			$post_data = http_build_query($post);

			$params 		= array();
			$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/milestones?_page=$p";
			$params['type'] = "GET";
			$params['data']	= $post_data;

			$new_records = self::MultiplecurlAccelo($params);
			$records = array_merge($records, $new_records);
      	}
  		curl_close($ch);

      	return $records;

	} //getProjectMilestones	

	public static function getMilestoneTasks($id){
		$limit = config('accelohub.limit');

		$post = [];
		$post["_filters"] 	= "against_id($id)";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/tasks/count";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	$count = self::getResult($params);
      	$count = $count['count'];

      	$tasks = array();
  		$pages = ceil($count / $limit);

      	$ch = curl_init();
      	self::setCurl($ch);

      	for ($p=0; $p < $pages; $p++) {
			$post = [];
			$post["_fields"] 	= "_ALL";
			$post["_limit"] 	= $limit;
			$post["_filters"] 	= "against_id($id)";

			$post_data = http_build_query($post);

			$params 		= array();
			$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/tasks?_page=$p";
			$params['type'] = "GET";
			$params['data']	= $post_data;

			$tasks_page = self::MultiplecurlAccelo($params);

			$tasks = array_merge($tasks, $tasks_page);
			#echo "PAGE: $p ".count($tasks_page)."<br />";
      	}
  		curl_close($ch);

      	return $tasks;
	} //getProjectMilestones

	public static function getLastMilestoneTasks($id, $accelo_last_task = ''){

		$limit = 10; config('accelohub.limit');

      	$ch = curl_init();
      	self::setCurl($ch);

		$post = [];
		$post["_fields"] 	= "_ALL";
		$post["_limit"] 	= 10;
		if($accelo_last_task) {
			$post["_filters"] 	= "against_id($id),order_by_asc(date_created),date_created_after($accelo_last_task)";
		} else {
			$post["_filters"] 	= "against_id($id),order_by_asc(date_created)";
		}

		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/tasks";
		$params['type'] = "GET";
		$params['data']	= $post_data;

		$tasks = self::MultiplecurlAccelo($params);

		return $tasks;
	} //getLastMilestoneTasks

	public static function getProjects(){

		$limit = config('accelohub.limit');

		$post = [];
		$post["_filters"] 	= "standing(active)";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/jobs/count";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	$count = self::getResult($params);
      	$count = $count['count'];

      	$records = array();
  		$pages = ceil($count / $limit);

      	$ch = curl_init();
      	self::setCurl($ch);

      	for ($p=0; $p < $pages; $p++) {
			$post = [];
			$post["_fields"] 	= "_ALL";
			$post["_limit"] 	= $limit;
			$post["_filters"] 	= "standing(active)";

			$post_data = http_build_query($post);

			$params 		= array();
			$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/jobs?_page=$p";
			$params['type'] = "GET";
			$params['data']	= $post_data;

			$new_records = self::MultiplecurlAccelo($params);
			$records = array_merge($records, $new_records);
      	}
  		curl_close($ch);

      	return $records;
	} //getProjects

	public static function getAllTasks(){
		$limit = config('accelohub.limit');

		$post = [];
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/tasks/count";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	$count = self::getResult($params);
      	$count = $count['count'];

      	$tasks = array();
  		$pages = ceil($count / $limit);

      	$ch = curl_init();
      	self::setCurl($ch);

      	for ($p=0; $p < $pages; $p++) {
			$post = [];
			$post["_fields"] 	= "_ALL";
			$post["_limit"] 	= $limit;

			$post_data = http_build_query($post);

			$params 		= array();
			$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/tasks?_page=$p";
			$params['type'] = "GET";
			$params['data']	= $post_data;

			$tasks_page = self::MultiplecurlAccelo($params);

			$tasks = array_merge($tasks, $tasks_page);
			#echo "PAGE: $p ".count($tasks_page)."<br />";
      	}
  		curl_close($ch);

      	return $tasks;
	} //getAllTasks

	public static function getProjectTasks($project_id){
		$limit = config('accelohub.limit');

		$post = [];
		$post["_filters"] 	= "child_of_job($project_id)";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/tasks/count";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	$count = self::getResult($params);
      	$count = isset($count['count']) ? $count['count'] : 0;

      	$tasks = array();
  		$pages = ceil($count / $limit);

      	$ch = curl_init();
      	self::setCurl($ch);

      	for ($p=0; $p < $pages; $p++) {
			$post = [];
			$post["_fields"] 	= "_ALL";
			$post["_limit"] 	= $limit;
			$post["_filters"] 	= "child_of_job($project_id)";

			$post_data = http_build_query($post);

			$params 		= array();
			$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/tasks?_page=$p";
			$params['type'] = "GET";
			$params['data']	= $post_data;

			$tasks_page = self::MultiplecurlAccelo($params);

			$tasks = array_merge($tasks, $tasks_page);
			#echo "PAGE: $p ".count($tasks_page)."<br />";
      	}
  		curl_close($ch);

      	return $tasks;
	} //getProjectTasks

	public static function getLastProjectTasks($project_id, $accelo_last_task = ''){
		$limit = 10; #config('accelohub.limit');

      	$ch = curl_init();
      	self::setCurl($ch);

		$post = [];
		$post["_fields"] 	= "_ALL";
		$post["_limit"] 	= 10;
		if($accelo_last_task) {
			$post["_filters"] 	= "child_of_job($project_id),order_by_asc(date_created),date_created_after($accelo_last_task)";
		} else {
			$post["_filters"] 	= "child_of_job($project_id),order_by_asc(date_created)";
		}

		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/tasks";
		$params['type'] = "GET";
		$params['data']	= $post_data;

		$tasks = self::MultiplecurlAccelo($params);

		return $tasks;
		foreach ($tasks as $key => $task) {
			echo "<br/>".date("Y/m/d H:i:s", $task['date_created'])." :: ". $task['id']." ::  ".$task['title'];
			$date_created = $task['date_created']; 
		}

		$post["_filters"] 	= "child_of_job($project_id),order_by_asc(date_created),date_created_after($date_created)";

		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/tasks";
		$params['type'] = "GET";
		$params['data']	= $post_data;

		echo "<hr />";
		$tasks2 = self::MultiplecurlAccelo($params);
		foreach ($tasks2 as $key => $task) {
			echo "<br/>".date("Y/m/d H:i:s", $task['date_created'])." :: ". $task['id']." ::  ".$task['title'];
			$date_created2 = $task['date_created']; 
		}

		$post["_filters"] 	= "child_of_job($project_id),order_by_asc(date_created),date_created_after($date_created2)";

		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/tasks";
		$params['type'] = "GET";
		$params['data']	= $post_data;

		echo "<hr />";
		$tasks2 = self::MultiplecurlAccelo($params);
		foreach ($tasks2 as $key => $task) {
			echo "<br/>".date("Y/m/d H:i:s", $task['date_created'])." :: ". $task['id']." ::  ".$task['title'];
			$date_created2 = $task['date_created']; 
		}

		dd($date_created, $tasks,$date_created2, $tasks2);
  		curl_close($ch);

      	return $tasks;
	} //getLastProjectTasks

	public static function getTickets($p=0){
		$ticket = config('accelohub.project_ticket');

		$limit = config('accelohub.limit');

		$post = [];
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/issues/count";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	$count = self::getResult($params);
      	$count = $count['count'];

      	$records = array();
  		$pages = ceil($count / $limit);

      	$ch = curl_init();
      	self::setCurl($ch);

      	for ($p=0; $p < $pages; $p++) {
			$post = [];
			$post["_fields"] 	= "_ALL";
			$post["_limit"] 	= $limit;

			$post_data = http_build_query($post);

			$params 		= array();
			$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/issues?_page=$p";
			$params['type'] = "GET";
			$params['data']	= $post_data;

			$new_records = self::MultiplecurlAccelo($params);
			$records = array_merge($records, $new_records);
      	}
  		curl_close($ch);

      	return $records;
	} //getTickets

	public static function getActivities(){

		$post = [];
		$post["_limit"] 	= config('accelohub.limit');
		$post["_fields"] 	= "_ALL";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/activities";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	return self::getResult($params);

	} //getActivities

	public static function getTimers($p=0){

		$post = [];
		$post["_limit"] 	= config('accelohub.limit');
		$post["_fields"] 	= "_ALL";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/timers";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	return self::getResult($params);

	} //getTimers

	public static function postTimesheets($timesheets){
		$error = []; $success = [];
        $ch = curl_init();
        AcceloConnect::setCurl($ch);
        $records = array();
        foreach ($timesheets as $key => $time_log) {

			$post = json_decode($time_log->acceloPost_data, true);

			#$post['against_type'] = 'task';
			$post_data = http_build_query($post);

			$params 		= array();
			$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/activities";
			$params['type'] = "POST";
			$params['data']	= $post_data;

			$new_records = self::MultiplecurlAccelo($params);
			#echo self::$cUrl_error;
			#dd(self::$lastCurl);

			if(isset($new_records['id']) && $new_records['id']) {
	        	$time_log->accelo_activity_id 	= $new_records['id'];
	        	$time_log->acceloActivity_data 	= json_encode($new_records);
	        	$time_log->status 				= 1;
	        	$time_log->update();	
	        	$success[] = $new_records;		
			} else {
	        	$time_log->api_error = self::$cUrl_error;
	        	$time_log->update();				
				$error[] = array('error' => 'Error in posting timesheet:: '.self::$cUrl_error, 'api' => $post);
			}
			#$records = array_merge($records, $new_records);          
			#dd($post, $new_records);
        }
        curl_close($ch);     
        #dd($records);
        #dd(array('CURL POST'=> self::$cUrl_run, 'success' => $success, 'error' => $error ));
        $apiResult = array('CURL POST'=> self::$cUrl_run, 'success' => $success, 'error' => $error );

        return $apiResult;
    
	    /*$start  = '2020-03-16 12:24:45';
	    $end    = date('Y-m-d H:i:s',strtotime('+7 hours',strtotime($start)));
		echo "<br />DATE $start to $end <br />"; 
	    $start  = strtotime($start);
	    $end  = strtotime($end);
	    $nonbillable = $end - $start;
	    dd($nonbillable);
	    /*echo "non billable: $nonbillable"."<br />LOGS $start to $end <br />"; 
	    $nonbillable = 7 * 3600;*

      	$timesheets = array();
      	#1 client 3 Internal
      	$timesheets[] = array(
						  'subject' 	=> 'Time Entry - #2619 Setup integration client',
						  'against_id' 	=> '2619',
						  'task_id' 	=> '2619',
						  'against_type' => 'task',
						  'body' 		=> 'post timelog via api client class',
						  'owner_id' 	=> '13',
						  'details' 	=> 'post timelog via api client class details',
						  'time_allocation' => '2619',
						  'medium' 			=> 'note',
						  'nonbillable' 	=> $nonbillable,
						  'visibility' 		=> 'all',
						  'date_started' 	=> $start,
						  //'date_logged' 	=> '1584361485',
						  'class_id' 		=> '3'
						);

      	$ch = curl_init();
      	self::setCurl($ch);

      	foreach ($timesheets as $key => $logs) {

			$post = [];
			$post["_fields"] 	= "_ALL";
			$post["_limit"] 	= $limit;
			$post["_filters"] 	= "job($project_id)";

			$post_data = http_build_query($post);

			$params 		= array();
			$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/milestones?_page=$p";
			$params['type'] = "GET";
			$params['data']	= $post_data;

			$new_records = self::MultiplecurlAccelo($params);
			$records = array_merge($records, $new_records);
      	}
  		curl_close($ch);

		$post = [];
		$post["_limit"] 	= config('accelohub.limit');
		$post["_fields"] 	= "_ALL";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/activities";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	return self::getResult($params);*/

	} //postTimesheets
}
