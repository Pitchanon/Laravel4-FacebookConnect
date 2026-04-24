<?php

return [
    'app_id'        => env('FACEBOOK_APP_ID'),
    'app_secret'    => env('FACEBOOK_APP_SECRET'),
    'graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v19.0'),
    'redirect_uri'  => env('FACEBOOK_REDIRECT_URI'),
    'default_scopes' => ['email', 'public_profile'],
    'http_timeout'  => 15,
];
