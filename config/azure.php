<?php
// Copyright (c) Microsoft Corporation.
// Licensed under the MIT License.

// Access environment through the config helper
// This will avoid issues when using Laravel's config caching
// https://laravel.com/docs/8.x/configuration#configuration-caching
return [
  'appId'             => env('OAUTH_APP_ID', 'dc6130f8-bb1f-4b09-ba4f-c7795ce9948e'),
  'appSecret'         => env('OAUTH_APP_SECRET', 'oJ48Q~QrRB7h~t3zrnyESXhpdkAkKLHT7JI5Wc5P'),
  'redirectUri'       => env('OAUTH_REDIRECT_URI', 'http://localhost/callback'),
  'scopes'            => env('OAUTH_SCOPES', ''),
  'authority'         => env('OAUTH_AUTHORITY', 'https://login.microsoftonline.com/common'),
  'authorizeEndpoint' => env('OAUTH_AUTHORIZE_ENDPOINT', '/oauth2/v2.0/authorize'),
  'tokenEndpoint'     => env('OAUTH_TOKEN_ENDPOINT', '/oauth2/v2.0/token'),
  'grantType'         => env('OAUTH_GRANT_TYPE', 'client_credentials'),
];
