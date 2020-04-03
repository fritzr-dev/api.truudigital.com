<?php

return [
    'name' 				=> 'AcceloHub',

    // accelo
    'client_ID' 		=> '1177d277ef@truudigital.accelo.com',
    'client_secret' 	=> 'hkOxhZz2BvbfCxJAFqgrw9Hs3ZOGigH8',

    //hubstaff
    'organization_id'  	=> 230119,
    'default_client'    => 94224,
    'project_ticket' 	=> 968633,
    'default_user'     	=> 3079,
    //'default_team'     	=> 7092,

    'serviceClientID'        => 'vaQZ3O3oNd_fATpLFlgZPupWFkQOIUaHv895Vna_cMs',
    'serviceClientSecret'    => 'ji_KF70MAQBJNIMlhABGZnzcLViVtb9MrGA1d58ay2nWnJi1byUmeJwUeBr5sJKuMJ2Pc9pt2vREu8AMkfdPQw',
    'serviceRedirectURL'     => 'http://localhost:8000/hubstaff/oauth',
    'serviceConnectURL'      => 'http://localhost:8000/hubstaff/connect',
    // This is the endpoint our server will request an access token from
    'tokenURL'   			 => 'https://account.hubstaff.com/access_tokens',
    // This is the hubstaff base URL we can use to make authenticated API requests
    'apiURLBase'       => 'https://api.hubstaff.com/v2/',

    'personal_access_tokens'  => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6ImRlZmF1bHQifQ.eyJqdGkiOiJVRktFcS85RSIsImlzcyI6Imh0dHBzOi8vYWNjb3VudC5odWJzdGFmZi5jb20iLCJleHAiOjE1OTMzOTQ5NTUsImlhdCI6MTU4NTYxODk1NSwic2NvcGUiOiJvcGVuaWQgaHVic3RhZmY6cmVhZCBodWJzdGFmZjp3cml0ZSJ9.Tlg9XPiyE5jP08wTq2iKB28yUS_0mh4qmPp8X_ayoWzMa7G3v1ujbxBcyUBkMPm8RYeTcmKrJNRh1UAX6clQcgxVP99HZ46Msg-_mX3weujcfRFis6wVfoJ-IUoRYWhjO_XwvJr2LWKTxdvBFa29w3rM8DwzZC2uXBhYLPuY56TofPhFB0o8zskdy-03MWPjtftDPOkCtxqNPP0KHddLvu5GlqfN3vXXL76c5BDfPBZ_KhRSuf6KpHNgwuroEWrWZmGxnKjKE0ZPXDZuG2Tk-8XYeVFAzbahpzC1Fb_lPDUo4cdTIDXbdbwtPzUzrCsHqg4m0z4H_Zca8J93DTVjog',

    'limit'				=> 50,
    'cLimit'			=> 50,
    'return_error'      => false,
    'pLimit'             => 20,
    'cLimit'             => 5,
];
