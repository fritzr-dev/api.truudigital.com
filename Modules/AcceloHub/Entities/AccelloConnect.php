<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AccelloConnect extends Model
{
    //protected $fillable = [];
    static $client_ID 		= '1177d277ef@truudigital.accelo.com';
    static $client_secret = 'hkOxhZz2BvbfCxJAFqgrw9Hs3ZOGigH8';
    static $client_token = [];
    static $access_token = '';

	public static function getToken(){

		$client_credentials = base64_encode(self::$client_ID.":".self::$client_secret);

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://truudigital.api.accelo.com/oauth2/v0/token",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "grant_type=client_credentials&scope=read%28staff%29",
		  CURLOPT_HTTPHEADER => array(
		    "Content-Type: application/x-www-form-urlencoded",
		    "authorization: Basic  $client_credentials"
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$result = (json_decode($response, true));
		self::$client_token = $result;
		self::$access_token = $result['access_token'];
	} //getToken

	public static function getStaff(){
      self::getToken();

      $access_token = self::$access_token;

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://truudigital.api.accelo.com/api/v0/staff",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "Content-Type: application/x-www-form-urlencoded",
          "Authorization: Bearer $access_token"
        ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      
      $result = (json_decode($response, true));
      dd($result);

	}

}
