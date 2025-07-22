<?php

declare(strict_types=1);

$clientId = '';
$redirectUri = 'http://192.168.160.128/onthespot/src/user/callback.php';
$scope = 'user-read-private user-read-email user-top-read playlist-read-private';

$url = 'https://accounts.spotify.com/authorize?'.http_build_query([
    'response_type' => 'code',
    'client_id' => $clientId,
    'scope' => $scope,
    'redirect_uri' => $redirectUri,
]);

header('Location: '.$url);

exit;
