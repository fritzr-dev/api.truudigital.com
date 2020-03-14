<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;
use Session;
use Request;

class AcceloConnect extends Model
{
    //protected $fillable = [];
    static $client_ID 		= '1177d277ef@truudigital.accelo.com';
    static $client_secret 	= 'hkOxhZz2BvbfCxJAFqgrw9Hs3ZOGigH8';
    static $project_ticket 	= '932390';

    static $client_token = [];
    static $access_token = '';
    static $return_error = false;
    static $apiCurl      = false;
    static $limit      	 = 50;
    static $cUrl_run     = 0;

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

		$client_credentials = base64_encode(self::$client_ID.":".self::$client_secret);

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

       return $result;
	}//curlAccelo

	public static function getResult($params){

		$result = self::curlAccelo($params);
		$data = [];
		if(isset($result['meta']['status']) && $result['meta']['status'] == 'ok') {
				$data = $result['response'];
		} else {
			if(self::$return_error) {
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
      self::$cUrl_run =  self::$cUrl_run + 1;

      $result = (json_decode($response, true));
      #dd($result);
		$data = [];
		if(isset($result['meta']['status']) && $result['meta']['status'] == 'ok') {
				$data = $result['response'];
		} else {
			if(self::$return_error) {
				$data = $result;
			}
		}
		return $data;
	}//curlAccelo

    public static function setCurl($ch){
        self::$apiCurl = $ch;
    }//setCurl

	public static function getStaff(){

		$post = [];
		$post["_limit"] 	= 50;
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
		$post["_limit"] 	= 50;
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

		$limit = self::$limit;

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
		$limit = self::$limit;

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

	public static function getProjects(){

		$limit = self::$limit;

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
		$limit = self::$limit;

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
		$limit = self::$limit;

		$post = [];
		$post["_filters"] 	= "child_of_job($project_id)";
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

	public static function getTickets($p=0){
		$ticket = self::$project_ticket;

		$limit = self::$limit;

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
		$post["_limit"] 	= 50;
		$post["_fields"] 	= "firstname, surname,mobile,email, position,standing,username";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/activities";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	return self::getResult($params);

	} //getActivities

}
