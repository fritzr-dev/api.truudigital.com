<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;
use Session;
use Request;

class AccelloConnect extends Model
{
    //protected $fillable = [];
    static $client_ID 		= '1177d277ef@truudigital.accelo.com';
    static $client_secret = 'hkOxhZz2BvbfCxJAFqgrw9Hs3ZOGigH8';
    static $client_token = [];
    static $access_token = '';
    static $return_error = false;

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

		#return self::oauth();
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
		$post["scope"] 		= "read(staff,companies,jobs,tasks,activities),write(activities)";
		$post_data = http_build_query($post);

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://truudigital.api.accelo.com/oauth2/v0/token",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,	
		  CURLOPT_CUSTOMREQUEST => "POST",
  		  #CURLOPT_POSTFIELDS => $post_data, 
  		  CURLOPT_POSTFIELDS => "grant_type=client_credentials&scope=read%28staff%2Ccompanies%2Cjobs%2Ctasks%2Cactivities%29%2Cwrite%28activities%29",
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

		return $data;
	}//getResult

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
		$post["_fields"] 	= "firstname, surname,mobile,email, position,standing,username";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/companies";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	return self::getResult($params);

	} //getCompanies

	public static function getProjects(){

		$post = [];
		$post["_limit"] 	= 50;
		$post["_fields"] 	= "firstname, surname,mobile,email, position,standing,username";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/jobs";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	return self::getResult($params);

	} //getProjects

	public static function getTasks(){

		$post = [];
		$post["_limit"] 	= 50;
		$post["_fields"] 	= "firstname, surname,mobile,email, position,standing,username";
		$post_data = http_build_query($post);

		$params 		= array();
		$params['url'] 	= "https://truudigital.api.accelo.com/api/v0/tasks";
		$params['type'] = "GET";
		$params['data']	= $post_data;

      	return self::getResult($params);

	} //getTasks

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