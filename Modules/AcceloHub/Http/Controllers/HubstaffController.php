<?php

namespace Modules\AcceloHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class HubstaffController extends Controller
{
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

    public function oauth(){
        dd($_POST);
    }
    public function developer()
    {
        // Fill these out with the values you got from Github
        $serviceClientID = 'HDATdk8oi6ZJjmw8o6rGl5XIa2g_tCnQPpo4xblsObc';
        $serviceClientSecret = 'Zjpy15HX_6Wvak5u8uEHIZbWEgtWx1OWIMuPyJGMPT65MkiVgKb9SNHKq-nR_hmQIJTGVO254fjGyrDT2tqpPw';

        // This is the URL we'll send the user to first to get their authorization
        $authorizeURL = 'https://account.hubstaff.com/authorizations/new';

        // This is the endpoint our server will request an access token from
        $tokenURL = 'https://account.hubstaff.com/access_tokens';

        // This is the Github base URL we can use to make authenticated API requests
        $apiURLBase = 'https://api.hubstaff.com/v2/';

        // The URL for this script, used as the redirect URL
        $baseURL = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
        $baseURL = 'http://localhost:8000/hubstaff/oauth';

        // Start a session so we have a place to store things between redirects
        session_start();


        // Start the login process by sending the user
        // to Github's authorization page
        if(isset($_GET['action']) && $_GET['action'] == 'login') {
          unset($_SESSION['access_token']);

          // Generate a random hash and store in the session
          $_SESSION['state'] = bin2hex(random_bytes(16));




$someJSON = '{"issuer":"https://account.hubstaff.com","authorization_endpoint":"https://account.hubstaff.com/authorizations/new","jwks_uri":"https://account.hubstaff.com/jwks.json","response_types_supported":["code"],"subject_types_supported":["public","pairwise"],"id_token_signing_alg_values_supported":["RS256"],"token_endpoint":"https://account.hubstaff.com/access_tokens","userinfo_endpoint":"https://account.hubstaff.com/user_info","scopes_supported":["openid","profile","email","tasks:read","tasks:write","hubstaff:read","hubstaff:write"],"grant_types_supported":["authorization_code","refresh_token"],"request_object_signing_alg_values_supported":["HS256","HS384","HS512"],"token_endpoint_auth_methods_supported":["client_secret_basic","client_secret_post"],"claims_supported":["sub","iss","name","email"]}';

  $someArray = json_decode($someJSON, true);
#dd($someArray);
          $params = array(
                "response_type" => "code",
                "redirect_uri"  => $baseURL,
                "realm"         => "hubstaff", 
                "client_id"     => $serviceClientID,
                "scope"         => "openid profile email",
                "state"         => "oauth2",
                "nonce"         =>  $_SESSION['state']
          );

          // Redirect the user to Github's authorization page
          header('Location: '.$authorizeURL.'?'.http_build_query($params));
          die();
        }


        if(isset($_GET['action']) && $_GET['action'] == 'logout') {
          unset($_SESSION['access_token']);
          header('Location: '.$baseURL);
          die();
        }

        // When Github redirects the user back here,
        // there will be a "code" and "state" parameter in the query string
        if(isset($_GET['code'])) {
          // Verify the state matches our stored state
          if(!isset($_GET['state'])
            || $_SESSION['state'] != $_GET['state']) {

            header('Location: ' . $baseURL . '?error=invalid_state');
            die();
          }

          // Exchange the auth code for an access token
          $token = $this->apiRequest($tokenURL, array(
            'grant_type' => 'authorization_code',
            'client_id' => $serviceClientID,
            'client_secret' => $serviceClientSecret,
            'redirect_uri' => $baseURL,
            'code' => $_GET['code']
          ));


          $_SESSION['access_token'] = $token['access_token'];

          header('Location: ' . $baseURL);
          die();
        }


        // If there is an access token in the session
        // the user is already logged in
        if(!isset($_GET['action'])) {
          if(!empty($_SESSION['access_token'])) {
            echo '<h3>Logged In</h3>';
            echo '<p><a href="?action=logout">Log Out</a></p>';
          } else {
            echo '<h3>Not logged in</h3>';
            echo '<p><a href="?action=login">Log In</a></p>';
          }
          die();
        }



    }

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

      if(isset($_SESSION['access_token']))
        $headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];

      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $response = curl_exec($ch);
      return json_decode($response, true);
    }

}
