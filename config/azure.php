<?php
// Copyright (c) Microsoft Corporation.
// Licensed under the MIT License.

// Access environment through the config helper
// This will avoid issues when using Laravel's config caching
// https://laravel.com/docs/8.x/configuration#configuration-caching
return [
  'appId'             => env('OAUTH_APP_ID', 'b6d1242b-6b4c-4d20-a4ed-e14125685951'),
  'appSecret'         => env('OAUTH_APP_SECRET', 'TM28Q~tLC37JgFHDrAnm0FQ6o9FjI.M1VpWdIcch'),
  'redirectUri'       => env('OAUTH_REDIRECT_URI', 'http://localhost:8000/callback'),
  'scopes'            => env('OAUTH_SCOPES', 'offline_access User.Read Calendars.ReadWrite'),
  'authority'         => env('OAUTH_AUTHORITY', 'https://login.microsoftonline.com/common'),
  'authorizeEndpoint' => env('OAUTH_AUTHORIZE_ENDPOINT', '/oauth2/v2.0/authorize'),
  'tokenEndpoint'     => env('OAUTH_TOKEN_ENDPOINT', '/oauth2/v2.0/token'),
  'grantType'         => env('OAUTH_GRANT_TYPE', 'client_credentials'),
];
