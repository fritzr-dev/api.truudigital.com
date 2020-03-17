<?php

namespace Modules\AcceloHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Modules\AcceloHub\Entities\HubstaffConnect;

class HubstaffController extends Controller
{

    private $serviceClientID        = 'HDATdk8oi6ZJjmw8o6rGl5XIa2g_tCnQPpo4xblsObc';
    private $serviceClientSecret    = 'Zjpy15HX_6Wvak5u8uEHIZbWEgtWx1OWIMuPyJGMPT65MkiVgKb9SNHKq-nR_hmQIJTGVO254fjGyrDT2tqpPw';
    private $serviceRedirectURL     = 'http://localhost:8000/hubstaff/oauth';
    private $serviceConnectURL      = 'http://localhost:8000/hubstaff/connect';
    // This is the endpoint our server will request an access token from
    private $tokenURL   = 'https://account.hubstaff.com/access_tokens';
    // This is the hubstaff base URL we can use to make authenticated API requests
    private $apiURLBase = 'https://api.hubstaff.com/v2/';
    private $organization_id = 239610;

    public function __construct()
    {
        if (!session_id()) session_start();
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('accelohub::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('accelohub::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('accelohub::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('accelohub::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    function getOrganizationMembers(){

        $data = HubstaffConnect::getOrganizationMembers();

        return response()->json($data);
    } //getOrganizationMembers

    function getClients(){

        $result = HubstaffConnect::getClients();

        return response()->json($data);
    } //getClients

    function getProjects(){

        $result = HubstaffConnect::getProjects();

        return response()->json($result);
    } //getProjects

    function getProject($id){

        $result = HubstaffConnect::getProject($id);

        return response()->json($result);
    } //getProject    

    function getTasks(){

        $result = HubstaffConnect::getTasks();

        return response()->json($result);
    } //getTasks

    function getActivities(){

        $result = HubstaffConnect::getActivities();

        return response()->json($result);
    } //getActivities
    
    function getTimesheets(){

        $result = HubstaffConnect::getTimesheets();

        return response()->json($result);
    } //getTimesheets
    
    function postHubstaff2AcceloActivities(){

        $result = HubstaffConnect::getActivities();

        return response()->json($result);
    } //postHubstaff2AcceloActivities


    /*DEVELOPET USE*/
    /*Refresh token HUBSTAFF*/
    public function refreshToken(){
        $code   = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6ImRlZmF1bHQifQ.eyJqdGkiOiJVRktFazcxSCIsImlzcyI6Imh0dHBzOi8vYWNjb3VudC5odWJzdGFmZi5jb20iLCJleHAiOjE1OTEyOTg1ODcsImlhdCI6MTU4MzUyNjE4Nywic2NvcGUiOiJvcGVuaWQgcHJvZmlsZSBlbWFpbCBodWJzdGFmZjpyZWFkIGh1YnN0YWZmOndyaXRlIn0.t2xwLfEIdklsQ_pEPwOSwxiYuaGiHZeNubEuSYhrOPEah6eJMfzTnXibMurygqV3NAXZSSi52db6c_dUJjfyDMafR9z0YDRPtgNCzmxyCSlpJAYv3IzfkPOC4qLkbyYI-6aG4NkD9M-Uh96IF-VEAzg5_nygFPIlqPf7671omJdhAF02llrrIrxkP3g1pCQfxB1Edz1f-iZzgY0Ob0Ni8OkSDzMPQVSzTXyw3txZmpADMuj1X-r6pK84c2Li3bslkO7uu5yldrOd5XL-IUydb-vB_3k44flXaYEgzRYl4DJVOvkhMTLrrMHRqnmAmKHLil8WvGP9AFv__AoUYBunhA';

        // Exchange the auth code for an access token
        $token = $this->apiRequest($this->tokenURL, array(
          'grant_type'    => 'refresh_token',
          'refresh_token' => $code
        ));

        if (isset($token['access_token'])) {
            $_SESSION['access_token'] = $token['access_token'];

            header('Location: ' . $this->serviceConnectURL);
            die();
        }  else if (isset($token['error'])) {
            $_SESSION['token_details'] = $token;
            header('Location: ' . $this->serviceConnectURL . '?error=invalid_grant');
        }

        dd($request->code);
    } //refreshToken

    /*connect to HUBSTAFF*/
    public function oauth(Request $request){
        $code   = $request->code;
        $state  = $request->state;

        $this->serviceClientID;

        #dd($_SESSION['state']);
        // When hubstaff redirects the user back here,
        // there will be a "code" and "state" parameter in the query string
        if($code) {
          // Verify the state matches our stored state
          if($_SESSION['state'] != $state) {
            header('Location: ' . $this->serviceConnectURL . '?error=invalid_state');
            die();
          }

          $client_credentials = base64_encode($this->serviceClientID.":".$this->serviceClientSecret);
          $client_credentials = $this->serviceClientID.":".$this->serviceClientSecret;

          // Exchange the auth code for an access token
          $token = $this->apiRequest($this->tokenURL, array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->serviceRedirectURL,
            'client_id'     => $this->serviceClientID,
            'client_secret' => $this->serviceClientSecret
          ));

            $_SESSION['token_details'] = $token;
            if (isset($token['access_token'])) {
                $_SESSION['access_token'] = $token['access_token'];

                header('Location: ' . $this->serviceConnectURL);
                die();
            }  else if (isset($token['error'])) {
                $_SESSION['token_details'] = $token;
                header('Location: ' . $this->serviceConnectURL . '?error=invalid_grant');
            }
        }

        dd($request->code);
    } //oauth
    
    public function connect() {
        // Fill these out with the values you got from hubstaff
        $serviceClientID        = $this->serviceClientID;
        $serviceClientSecret    = $this->serviceClientSecret;

        // This is the URL we'll send the user to first to get their authorization
        $authorizeURL = 'https://account.hubstaff.com/authorizations/new';

        // Start the login process by sending the user
        // to hubstaff's authorization page
        if(isset($_GET['action']) && $_GET['action'] == 'login') {
            unset($_SESSION['access_token']);

            // Generate a random hash and store in the session
            $_SESSION['state'] = 'oauth2';#bin2hex(random_bytes(16));
            $_SESSION['nonce'] = bin2hex(random_bytes(16));

            $params = array(
                "response_type" => "code",
                "redirect_uri"  => $this->serviceRedirectURL,
                "realm"         => "hubstaff", 
                "client_id"     => $serviceClientID,
                "scope"         => "openid profile email hubstaff:read hubstaff:write",
                "state"         => "oauth2",
                "nonce"         =>  $_SESSION['nonce']
            );

            // Redirect the user to hubstaff's authorization page
            header('Location: '.$authorizeURL.'?'.http_build_query($params));
            die();
        } else if(isset($_GET['action']) && $_GET['action'] == 'logout') {
          unset($_SESSION['access_token']);
          header('Location: '.$this->serviceConnectURL);
          die();
        }

        // If there is an access token in the session
        // the user is already logged in
        if(!isset($_GET['action'])) {
          if(!empty($_SESSION['access_token'])) {
            echo '<pre>';
            print_r($_SESSION['token_details']);
            echo '</pre>';
            echo '<h3>Logged In</h3>';
            echo '<p><a href="'.$this->serviceConnectURL.'?action=logout">Log Out</a></p>';
          } else {
            echo '<h3>Not logged in</h3>';
            echo '<p><a href="'.$this->serviceConnectURL.'?action=login">Log In</a></p>';
          }
          die();
        }
    }//connect

    // This helper function will make API requests to GitHub, setting
    // the appropriate headers GitHub expects, and decoding the JSON response
    function apiRequest($url, $post=FALSE, $headers=array()) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

      if($post)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

      $headers = [
        'Accept: application/json',
        'User-Agent: TruuDigital'
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

      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $response = curl_exec($ch);
      return json_decode($response, true);
    } //apiRequest

    /*DEVELOPET USE*/
    public function developer(){

    }

}
