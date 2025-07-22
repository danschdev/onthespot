<?php

declare(strict_types=1);

$code = $_GET['code'];
$clientId = '';
$clientSecret = '';
$redirectUri = 'http://192.168.160.128/onthespot/src/user/callback.php';

$response = file_get_contents('https://accounts.spotify.com/api/token', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Authorization: Basic " . base64_encode("$clientId:$clientSecret") . "\r\n" .
                    "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]),
    ]
]));

$data = json_decode($response, true);
$accessToken = $data['access_token'];
$refreshToken = $data['refresh_token'];

die($data);
