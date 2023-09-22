<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// app/Http/Controllers/MicrosoftGraphController.php


use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
class MicrosoftGraphController extends Controller
{
    public function index()
    {
        $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
        $query = http_build_query([
            'client_id' => 'b22f97cd-317a-4d79-8db9-be592dca7f40',
            'scope' => 'openid profile offline_access User.Read Mail.Read',
            'response_type' => 'code',
            'redirect_uri' => 'http://localhost:8000/callback',
            'response_mode' => 'query',
            'state' => Str::random(40),
        ]);

        return redirect("$url?$query");
    }

    public function callback(Request $request)
    {
        // Extract the authorization code from the callback request
        $authorizationCode = $request->input('code');
         // URL-encode the 'redirect_uri'
         $redirectUri = urlencode("http://localhost:8000/callback");
        // Make a POST request to the Microsoft token endpoint
        $client = new Client([
            'verify' => false, // Disable SSL verification
        ]);
        $response = $client->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'form_params' => [
                'client_id' => 'b22f97cd-317a-4d79-8db9-be592dca7f40',
                'client_secret' => 'c67ee3c0-ab60-47b5-921a-86942e2226cb',
                'code' => $authorizationCode,
                'redirect_uri' => 'http://localhost:8000/callback',
                'grant_type' => 'authorization_code',
            ],
            'verify' => false,
        ]);
        Log::error('Token request response: ' . $response->getBody()->getContents());
    
        // Handle the response from the token endpoint
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody(), true);
            $accessToken = $data['access_token'];
            
            // You can now use the $accessToken to make Microsoft Graph API requests
            // Example: List the user's messages
            $graph = new Graph();
            $graph->setAccessToken($accessToken);
            $messages = $graph->createRequest('GET', '/me/messages')
                ->setReturnType(Model\Message::class)
                ->execute();

            // Handle the response (e.g., display messages to the user)
            return view('outlook.messages', ['messages' => $messages]);
        } else {
            // Handle the error response from the token endpoint
            $error = json_decode($response->getBody(), true);
            // Log or display the error details
            Log::error('Token request failed: ' . json_encode($error));
            return "Integration failed: Unable to obtain an access token.";
        }
    }
    private function getAccessToken($code)
    {
        // Implement code to exchange the code for an access token
        // This will involve making a POST request to the token endpoint

        // Example code to exchange the code for an access token:
        // $response = // Make a POST request to the token endpoint

        // Parse the response to extract the access token
        // $data = json_decode($response->getBody(), true);
        // return $data['access_token'];

        // You should add proper error handling here
        return null;
    }
}

