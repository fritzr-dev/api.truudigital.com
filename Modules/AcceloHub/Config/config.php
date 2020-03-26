<?php

return [
    'name' 				=> 'AcceloHub',

    // accelo
    'client_ID' 		=> '1177d277ef@truudigital.accelo.com',
    'client_secret' 	=> 'hkOxhZz2BvbfCxJAFqgrw9Hs3ZOGigH8',

    //hubstaff
    'organization_id'  	=> 242946,
    'project_ticket' 	=> 940266,
    'default_user'     	=> 802748,
    'default_team'     	=> 7092,
    'default_client'    => 98359,
    'serviceClientID'        => 'vaQZ3O3oNd_fATpLFlgZPupWFkQOIUaHv895Vna_cMs',
    'serviceClientSecret'    => 'ji_KF70MAQBJNIMlhABGZnzcLViVtb9MrGA1d58ay2nWnJi1byUmeJwUeBr5sJKuMJ2Pc9pt2vREu8AMkfdPQw',
    'serviceRedirectURL'     => 'http://localhost:8000/hubstaff/oauth',
    'serviceConnectURL'      => 'http://localhost:8000/hubstaff/connect',
    // This is the endpoint our server will request an access token from
    'tokenURL'   			 => 'https://account.hubstaff.com/access_tokens',
    // This is the hubstaff base URL we can use to make authenticated API requests
    'apiURLBase'       => 'https://api.hubstaff.com/v2/',

    'personal_access_tokens'  => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6ImRlZmF1bHQifQ.eyJqdGkiOiJVRktFbTcxbSIsImlzcyI6Imh0dHBzOi8vY WNjb3VudC5odWJzdGFmZi5jb20iLCJleHAiOjE1OTIxNzE3NDEsImlhdCI6MTU4NDM5NTc0MSwic2NvcGUiOiJvcGVuaWQgcHJvZmlsZSBlbWFpbCBodWJzdGFmZjpyZWFkIGh1YnN0YWZmOndyaXRlIn0.s7a2BKM4XkLhgnkQkWfTb7NWd0pju2x0Rk--oKoW81mJkU27daLDEUGW8fLDZ9WWo5kFFTQAMYb6p2RQx9cuHBttq00a9xSBzh8mEIlyDL3v5Babry_jDNasRBP-IskA74wI3efpOY3CNb9iPoSlJBunsTEZQjA7W8UBMwCMrZ-QzC7olF55XXQOWFdEzDNzWBgYU1Reda3NFbwSj-QBRb1yZS8QsOzjcJ7QtdpS7yjJoAYT2jr5DtrGLYrXoQIx8aQsp62Of11dFZDXtc51puqsAEhfYLIhNd5ECAiZZIs9Omvw-u3gzDF3cWpEv9E3_p7wLzcshNsgEE91eY-E5Q',

    'limit'				=> 50,
    'cLimit'			=> 50,
    'return_error'      => false,
    'pLimit'             => 15,
    'cLimit'             => 10,
];
