<?php

namespace App\Http\Controllers;
use Firebase\JWT\JWT;
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Microsoft\Graph\Model\User;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Microsoft\Graph\Model\OnlineMeeting;
use Microsoft\Graph\Model\IdentitySet;
use Microsoft\Graph\Model\Event;
use Microsoft\Graph\Model\DateTimeTimeZone;
use Microsoft\Graph\Model\Location;
use Microsoft\Graph\Model\Attendee;
use Microsoft\Graph\Model\EmailAddress;
use Microsoft\Graph\Model\ItemBody;
use Illuminate\Support\Facades\Http; 
use Microsoft\Graph\Model\AttendeeType;class OutlookCalendarController extends Controller
{
    const accesstoken="";
    public function index()
    {
        // Redirect the user to Microsoft login to obtain an authorization code
        $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
        $query = http_build_query([
            'client_id' => '228ebf41-ca17-4127-b019-3798edb9e2b9',
            'scope' => 'offline_access User.Read Calendars.ReadWrite',
            'response_type' => 'code',
            'redirect_uri' => 'http://localhost:8000/callback', // Your redirect URL
            'response_mode' => 'query',
            'state' => bin2hex(random_bytes(16)),
        ]);
       // return view('events', compact('url','query'));
        return redirect("$url?$query");
    }

    public function callback(Request $request)
{
    // Extract the authorization code from the callback request
    $authorizationCode = $request->input('code');

    // Replace 'YOUR_CLIENT_SECRET' with your actual client secret
    $clientSecret = 'rzw8Q~0o5Qv2lUW2jB~3J1GCOegeC5JIvPRwabeH'; // Replace with your actual client secret

    // Prepare the parameters for the token request
    $tokenParams = [
        'grant_type' => 'authorization_code',
        'client_id' => '228ebf41-ca17-4127-b019-3798edb9e2b9', // Replace with your actual client ID
        'scope' => 'offline_access User.Read Calendars.ReadWrite', // Adjust scope as needed
        'code' => $authorizationCode,
        'redirect_uri' => 'http://localhost:8000/callback', // Your redirect URL
        'client_secret' => $clientSecret,
    ];

    // Make a POST request to the token endpoint
    $client = new Client([
        'verify' => false, // Disable SSL verification if needed
    ]);

    try {
        $response = $client->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'form_params' => $tokenParams,
        ]);
        $responseBody = $response->getBody()->getContents();

        // Handle the response from the token endpoint
        if ($response->getStatusCode() === 200) {
            // Decode the JSON response into an array
            $responseData = json_decode($responseBody, true);
            //print_r($responseData['access_token']);
            if (isset($responseData['access_token'])) {
                $accessToken = $responseData['access_token'];
                $refreshToken = $responseData['refresh_token'];
                
                //print_r($accessToken);
                $apiEndpoint = 'https://graph.microsoft.com/v1.0/me/events';
                $headers = [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ];

                $response1 = $client->get($apiEndpoint, [
                    'headers' => $headers,
                ]);

                $filePath ='D:\Gopi_WorkSpace\HH-outlook-main\token.json';

                if (file_exists($filePath)) {
                    
                    $jsonData = file_get_contents($filePath);
                    $data = json_decode($jsonData, true);
                    $newaccessToken = $accessToken;
                    $newrefreshToken = $refreshToken;
                    // Check if the file contains an access token
                    if ($data && isset($data['access_token'])) {
                        // Replace the existing access token
                        $data['access_token'] = $newaccessToken; // Replace with your new access token
                    } else {
                        // If no access token exists, add it to the data
                        $data['access_token'] =  $newaccessToken; // Replace with your new access token
                    }
                    if (isset($data['refresh_token'])) {
                        // Replace the existing refresh token
                        $data['refresh_token'] = $newrefreshToken;
                    } else {
                        // If no refresh token exists, add it to the data
                        $data['refresh_token'] = $newrefreshToken;
                    }
                } 
                // Convert the data to JSON format
                $jsonData = json_encode($data);
                // Save the JSON data to the file
                file_put_contents($filePath, $jsonData);

                if ($response1->getStatusCode() === 200) {
                    // Retrieve and process the response body
                    $responseData1 = json_decode($response1->getBody()->getContents(), true);
 
                    if (isset($responseData1['value'])) {
                        // Initialize an array to store the events data
                        $eventsData = [];
                       // print_r(responseData1);
                        // Loop through each event in the "value" array
                        foreach ($responseData1['value'] as $eventData) {
                            // Extract event details and add them to the $eventsData array
                            $eventsData[] = [
                                'title' => $eventData['subject'],
                                'start' => $eventData['start']['dateTime'],
                                'end' => $eventData['end']['dateTime'],
                                'access_token' => $accessToken,
                                'event_id' => $eventData['id'],
                                'location' => $eventData['location']['displayName'],
                                'timezone' => $eventData['start']['timeZone'],
                                'content' => $eventData['body']['content'],
                                'attendees_required' => [],
                                'attendees_optional' => []
                            ];
                            
                            // Extract attendees
                            foreach ($eventData['attendees'] as $attendee) {
                                $attendeeType = $attendee['type'];
                                $emailAddress = $attendee['emailAddress']['address'];
                        
                                if ($attendeeType === 'required') {
                                    $eventsData[count($eventsData) - 1]['attendees_required'][] = $emailAddress;
                                } elseif ($attendeeType === 'optional') {
                                    $eventsData[count($eventsData) - 1]['attendees_optional'][] = $emailAddress;
                                }
                            }
                        }
                        Log::info('Event data:', $eventsData);

                        // Return the events data as JSON
                        //return response()->json($responseData1);
                        //return view('events',$responseData1);
                         $editEventId = 0;
                        $request->session()->flash('eventsData', $eventsData);
                        $request->session()->flash('editEventId', $editEventId);
                        $request->session()->flash('accessToken',$accessToken);
                        $daaa = session(['eventsData' => $eventsData, 'editEventId' => $editEventId, 'accessToken' => $accessToken]);
                        //return redirect()->route('goEvents', ['eventsData' => $eventsData]);
                        return redirect()->route('goEvents');
                        //return view ('events');
                        // return view('events', ['eventsData' => $eventsData,'editEventId' => $editEventId]);
                    } else {
                        // Handle the case where there are no events in the response
                        return response()->json([]);
                    }
                } else {
                    // Handle error response here (e.g., display an error message)
                    return response()->json(['error' => 'Error fetching events'], 500);
                }
            } else {
                // Handle the error when 'access_token' is not present in the response
                return response()->json(['error' => 'Access token not found in response'], 500);
            }
        } else {
            // Handle the error response from the token endpoint
            return response()->json(['error' => 'Token request failed'], 500);
        }
    } catch (\Exception $e) {
        // Handle exceptions such as network errors
        return response()->json(['error' => 'Token request exception'], 500);
    }
}
public function getAllEvents(Request $request)
{
    $accessToken = $this->getAccessTokenFromJsonFile();
    $client = new Client([
        'verify' => false, // Disable SSL verification if needed
    ]);
    $apiEndpoint = 'https://graph.microsoft.com/v1.0/me/events';
    $headers = [
        'Authorization' => 'Bearer ' . $accessToken,
        'Content-Type' => 'application/json',
    ];
    $response = $client->get($apiEndpoint, [
        'headers' => $headers,
    ]);
    if ($response->getStatusCode() === 200) {
        // Retrieve and process the response body
        $responseData1 = json_decode($response->getBody()->getContents(), true);

        if (isset($responseData1['value'])) {
            // Initialize an array to store the events data
            $eventsData = [];
            // Loop through each event in the "value" array
            foreach ($responseData1['value'] as $eventData) {
                // Extract event details and add them to the $eventsData array
                $eventsData[] = [
                    'title' => $eventData['subject'],
                    'start' => $eventData['start']['dateTime'],
                    'end' => $eventData['end']['dateTime'],
                    'access_token' => $accessToken,
                    'event_id' => $eventData['id'],
                    'location' => $eventData['location']['displayName'],
                    'timezone' => $eventData['start']['timeZone'],
                    'content' => $eventData['body']['content'],
                    'attendees_required' => [],
                    'attendees_optional' => []
                ];

                // Extract attendees
                foreach ($eventData['attendees'] as $attendee) {
                    $attendeeType = $attendee['type'];
                    $emailAddress = $attendee['emailAddress']['address'];

                    if ($attendeeType === 'required') {
                        $eventsData[count($eventsData) - 1]['attendees_required'][] = $emailAddress;
                    } elseif ($attendeeType === 'optional') {
                        $eventsData[count($eventsData) - 1]['attendees_optional'][] = $emailAddress;
                    }
                }
            }    
                $editEventId = 0;
                $request->session()->flash('eventsData', $eventsData);
                $request->session()->flash('editEventId', $editEventId);
                $request->session()->flash('accessToken',$accessToken);

                
                //Log::info('Event data:', $eventsData);
                //Session::put('eventsData', $eventsData);
                return redirect()->route('goEvents');
            
        }
    }

    // Return an empty array or handle the error as needed
    return [];
}

public function refreshToken() {
    $refreshToken = $this->getRefreshTokenFromJsonFile();
    
    $client = new Client([
        'verify' => false, // Disable SSL verification if needed
    ]);
    $tenant = 'f8cdef31-a31e-4b4a-93e4-5f571e91255a';
    //$tenant = 'f8cdef31-a31e-4b4a-93e4-5f571e91255a';
    $tokenResponse = $client->post("https://login.microsoftonline.com/common/oauth2/v2.0/token", [
        'form_params' => [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => '228ebf41-ca17-4127-b019-3798edb9e2b9',
            'client_secret' => 'rzw8Q~0o5Qv2lUW2jB~3J1GCOegeC5JIvPRwabeH',
            'scope' => 'https://graph.microsoft.com/.default',
        ],
    ]);


    if ($tokenResponse->getStatusCode() === 200) {
        $tokenData = json_decode($tokenResponse->getBody(), true);
        $newAccessToken = $tokenData['access_token'];
        // Use the newAccessToken to make requests to Microsoft Graph.
        Log::info('Access token refreshed: ' . $newAccessToken);
        $filePath ='D:\Gopi_WorkSpace\HH-outlook-main\token.json';

        if (file_exists($filePath)) {
            
            $jsonData = file_get_contents($filePath);
            $data = json_decode($jsonData, true);
            $accessToken = $newAccessToken;
            // Check if the file contains an access token
            if ($data && isset($data['access_token'])) {
                // Replace the existing access token
                $data['access_token'] = $accessToken; // Replace with your new access token
            } else {
                // If no access token exists, add it to the data
                $data['access_token'] =  $accessToken; // Replace with your new access token
            }
        } 
        // Convert the data to JSON format
        $jsonData = json_encode($data);
        // Save the JSON data to the file
        file_put_contents($filePath, $jsonData);

        return $newAccessToken;
    } else {
        // Handle the error, e.g., by requesting user reauthorization.
        return null;
    }
}


public function refreshTokenPeriodically() {
    while (true) {
        $newAccessToken = $this->refreshToken(); // Call the refreshToken function
       // Log::info('Access token refreshed: ' . $newAccessToken);
        if ($newAccessToken) {
            // Successfully refreshed the access token; you can use it for requests.
            // You may want to store the access token securely and use it in your application.
        } else {
            // Handle the case where the access token couldn't be refreshed (e.g., reauthorization).
        }

        sleep(5); 
    }
}




public function getAccessTokenFromJsonFile()
{
    // Read the access token from the token.json file
    $tokenFilePath = 'D:\Gopi_WorkSpace\HH-outlook-main\token.json';
    if (file_exists($tokenFilePath)) {
        $tokenData = json_decode(file_get_contents($tokenFilePath), true);
        if (isset($tokenData['access_token'])) {
            return $tokenData['access_token'];
        }
    }

    // Handle the case where the access token is not found or invalid
    return null;
}

public function getRefreshTokenFromJsonFile()
{
    // Read the access token from the token.json file
    $tokenFilePath = 'D:\Gopi_WorkSpace\HH-outlook-main\token.json';
    if (file_exists($tokenFilePath)) {
        $tokenData = json_decode(file_get_contents($tokenFilePath), true);
        if (isset($tokenData['refresh_token'])) {
            return $tokenData['refresh_token'];
        }
    }

    // Handle the case where the access token is not found or invalid
    return null;
}
public function goEvents() {
    $eventsData = session('eventsData');
    //Log::info('sessionData:', ['eventsData' => $eventsData]);
    $editEventId = session('editEventId');
    $accessToken = $this->getAccessTokenFromJsonFile();
    
    return view('events', ['eventsData' => $eventsData, 'editEventId' => $editEventId, 'accessToken' => $accessToken]);
}


    public function createEvent(Request $request)
{
    // Get the data from the POST request body
    $requestData = $request->json()->all();

    if (isset($requestData['onlineMeetingProvider'])) {
        $onlineMeetingProvider = $requestData['onlineMeetingProvider'];
    } else {
        $onlineMeetingProvider = 'default_value'; 
    }
    // Extract the data from the request body
    $accessToken = $requestData['accesstoken'];
    $subject = $requestData['subject'];
    $startDateTime = $requestData['start'];
    $endDateTime = $requestData['end'];
    $timeZone = $requestData['timezone'];
    $location = $requestData['location'];
    $content = $requestData['content'];
    $isOnlineMeeting =$requestData['isOnlineMeeting'];
    $requiredAttendees = $requestData['attendeesRequired'];
    $optionalAttendees = $requestData['attendeesOptional'];

    // Create the event data array
    $event_data = [
        "subject" => $subject,
        "start" => [
            "dateTime" => $startDateTime,
            "timeZone" => $timeZone
        ],
        "end" => [
            "dateTime" => $endDateTime,
            "timeZone" => $timeZone
        ],
        "location" => [
            "displayName" => $location
        ],
        "body" => [
            "contentType" => "HTML",
            "content" => $content
        ],
        "attendees" => [],
        "isOnlineMeeting" => $isOnlineMeeting,
    ];
    if ($isOnlineMeeting) {
        $event_data["onlineMeetingProvider"] = $onlineMeetingProvider; 
    }
    // Add required attendees to the event data
    foreach ($requiredAttendees as $email) {
        $event_data['attendees'][] = [
            "emailAddress" => [
                "address" => $email,
                "name" => ""
            ],
            "type" => "required"
        ];
    }

    // Add optional attendees to the event data
    foreach ($optionalAttendees as $email) {
        $event_data['attendees'][] = [
            "emailAddress" => [
                "address" => $email,
                "name" => ""
            ],
            "type" => "optional"
        ];
    }
    //echo($event_data);
    // Initialize the HTTP client
    $client = new Client([
        'verify' => false, // Disable SSL verification if needed (not recommended for production)
    ]);
    Log::info('Event data:', $event_data);
    try {
        $response = $client->post('https://graph.microsoft.com/v1.0/me/events', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $event_data,
        ]);
        // Handle the response as needed (e.g., check for success)
        $statusCode = $response->getStatusCode();
    
        if ($statusCode === 201) {
            // Event created successfully
            //return response()->json(['message' => 'Event created successfully']);
            //return response()->json();
            return response()->json();
        } else {
            // Handle error response
            return response()->json(['error' => 'Event creation failed'], $statusCode);
        }
    } catch (\Exception $e) {
        // Handle exceptions (e.g., token expiration)
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
public function updateEvent(Request $request)
{
    // Get the event ID from the request payload
   
    // Get the updated event data from the request body
    $updatedEventData = $request->json()->all();

    // Replace this with your actual access token retrieval logic
    $accessToken = $updatedEventData['accesstoken'];
    $eventId = $updatedEventData['eventId'];
    $subject = $updatedEventData['subject'];
    $startDateTime = $updatedEventData['start'];
    $endDateTime = $updatedEventData['end'];
    $timeZone = $updatedEventData['timezone'];
    $location = $updatedEventData['location'];
    $content = $updatedEventData['content'];
    $requiredAttendees = $updatedEventData['attendeesRequired'];
    $optionalAttendees = $updatedEventData['attendeesOptional'];

    // Create the event data array
    $event_data = [
        "subject" => $subject,
        "start" => [
            "dateTime" => $startDateTime,
            "timeZone" => $timeZone
        ],
        "end" => [
            "dateTime" => $endDateTime,
            "timeZone" => $timeZone
        ],
        "location" => [
            "displayName" => $location
        ],
        "body" => [
            "contentType" => "HTML",
            "content" => $content
        ],
        "attendees" => [],
    ];

    // Add required attendees to the event data
    foreach ($requiredAttendees as $email) {
        $event_data['attendees'][] = [
            "emailAddress" => [
                "address" => $email,
                "name" => ""
            ],
            "type" => "required"
        ];
    }

    // Add optional attendees to the event data
    foreach ($optionalAttendees as $email) {
        $event_data['attendees'][] = [
            "emailAddress" => [
                "address" => $email,
                "name" => ""
            ],
            "type" => "optional"
        ];
    }
    // Initialize the HTTP client
    $client = new Client([
        'verify' => false, // Disable SSL verification if needed (not recommended for production)
    ]);

    try {
        // Make the PATCH request to update the event
        $response = $client->patch("https://graph.microsoft.com/v1.0/me/events/$eventId", [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $event_data,
        ]);

        // Handle the response and return a JSON response
        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            // Event updated successfully
            return response()->json(['message' => 'Event updated successfully']);
        } else {
            // Handle error response
            return response()->json(['error' => 'Event update failed'], $statusCode);
        }
    } catch (\Exception $e) {
        // Handle exceptions (e.g., token expiration)
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
public function cancelEvent(Request $request,)
{
    $updatedEventData = $request->json()->all();

    $accessToken = $updatedEventData['accesstoken'];
    $eventId = $updatedEventData['eventId'];
    // Initialize the HTTP client
    $client = new Client([
        'verify' => false, // Disable SSL verification if needed (not recommended for production)
    ]);

    try {
        // Set the data to cancel the event
        $cancelEventData = [
            'isCancelled' => true,
        ];

        // Send a PATCH request to update the event
        $response = $client->post("https://graph.microsoft.com/v1.0/me/events/$eventId/cancel", [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $cancelEventData,
        ]);

        // Handle the response as needed (e.g., check for success)
        $statusCode = $response->getStatusCode();

        if ($statusCode === 202) {
            // Event canceled successfully
            return response()->json(['message' => 'Event canceled successfully']);
        } else {
            // Handle error response
            return response()->json(['error' => 'Event cancelation failed'], $statusCode);
        }
    } catch (\Exception $e) {
        // Handle exceptions (e.g., token expiration)
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function deleteEvent(Request $request)
{
    // Get the access token from the request
    $updatedEventData = $request->json()->all();

    $accessToken = $updatedEventData['accesstoken'];
    $eventId = $updatedEventData['eventId'];

    // Initialize the HTTP client
    $client = new Client([
        'verify' => false, // Disable SSL verification if needed (not recommended for production)
    ]);

    try {
        // Send a DELETE request to remove the event
        $response = $client->delete("https://graph.microsoft.com/v1.0/me/events/$eventId", [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        // Handle the response as needed (e.g., check for success)
        $statusCode = $response->getStatusCode();

        if ($statusCode === 204) {
            // Event deleted successfully
            return response()->json(['message' => 'Event deleted successfully']);
        } else {
            // Handle error response
            return response()->json(['error' => 'Event deletion failed'], $statusCode);
        }
    } catch (\Exception $e) {
        // Handle exceptions (e.g., token expiration)
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function listEvents(Request $request)
{
    
    
    $client = new Client([
        'verify' => false, // Disable SSL verification if needed
    ]);

    try {
            $responseData = $request->json()->all();
            if (isset($responseData['access_token'])) {
                $accessToken = $responseData['access_token'];
                $apiEndpoint = 'https://graph.microsoft.com/v1.0/me/events';
                $headers = [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ];

                $response1 = $client->get($apiEndpoint, [
                    'headers' => $headers,
                ]);

                if ($response1->getStatusCode() === 200) {
                    // Retrieve and process the response body
                    $responseData1 = json_decode($response1->getBody()->getContents(), true);

                    if (isset($responseData1['value'])) {
                        // Initialize an array to store the events data
                        $eventsData = [];

                        // Loop through each event in the "value" array
                        foreach ($responseData1['value'] as $eventData) {
                            // Extract event details and add them to the $eventsData array
                            $eventsData[] = [
                                'title' => $eventData['subject'],
                                'start' => $eventData['start']['dateTime'],
                                'end' => $eventData['end']['dateTime'],
                                'access_token' => $accessToken,
                                'event_id' => $eventData['id'],
                                'location' => $eventData['location']['displayName'],
                                'timezone' => $eventData['start']['timeZone'],
                                'content' => $eventData['body']['content'],
                                'attendees_required' => [],
                                'attendees_optional' => []
                            ];
                        
                            // Extract attendees
                            foreach ($eventData['attendees'] as $attendee) {
                                $attendeeType = $attendee['type'];
                                $emailAddress = $attendee['emailAddress']['address'];
                        
                                if ($attendeeType === 'required') {
                                    $eventsData[count($eventsData) - 1]['attendees_required'][] = $emailAddress;
                                } elseif ($attendeeType === 'optional') {
                                    $eventsData[count($eventsData) - 1]['attendees_optional'][] = $emailAddress;
                                }
                            }
                        }
                        

                        // Return the events data as JSON
                        return response()->json($eventsData);
                        // $eventData=response()->json($eventsData);
                        //return view('events',$responseData1);
                        // $editEventId=0;
                        // return view('events', ['eventsData' => $eventsData,'editEventId'=>$editEventId]);
                    } else {
                        // Handle the case where there are no events in the response
                        return response()->json([]);
                    }
                } else {
                    // Handle error response here (e.g., display an error message)
                    return response()->json(['error' => 'Error fetching events'], 500);
                }
            } else {
                // Handle the error when 'access_token' is not present in the response
                return response()->json(['error' => 'Access token not found in response'], 500);
            }
        
    } catch (\Exception $e) {
        // Handle exceptions such as network errors
        return response()->json(['error' => 'Token request exception'], 500);
    }
}

public function refreshEvents(Request $request)
{
    
    
    $client = new Client([
        'verify' => false, // Disable SSL verification if needed
    ]);

    try {
            $responseData = $request->json()->all();
            if (isset($responseData['access_token'])) {
                $accessToken = $responseData['access_token'];
                $apiEndpoint = 'https://graph.microsoft.com/v1.0/me/events';
                $headers = [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ];

                $response1 = $client->get($apiEndpoint, [
                    'headers' => $headers,
                ]);

                if ($response1->getStatusCode() === 200) {
                    // Retrieve and process the response body
                    $responseData1 = json_decode($response1->getBody()->getContents(), true);

                    if (isset($responseData1['value'])) {
                        // Initialize an array to store the events data
                        $eventsData = [];

                        // Loop through each event in the "value" array
                        foreach ($responseData1['value'] as $eventData) {
                            // Extract event details and add them to the $eventsData array
                            $eventsData[] = [
                                'title' => $eventData['subject'],
                                'start' => $eventData['start']['dateTime'],
                                'end' => $eventData['end']['dateTime'],
                                'access_token' => $accessToken,
                                'event_id' => $eventData['id'],
                                'location' => $eventData['location']['displayName'],
                                'timezone' => $eventData['start']['timeZone'],
                                'content' => $eventData['body']['content'],
                                'attendees_required' => [],
                                'attendees_optional' => []
                            ];
                        
                            // Extract attendees
                            foreach ($eventData['attendees'] as $attendee) {
                                $attendeeType = $attendee['type'];
                                $emailAddress = $attendee['emailAddress']['address'];
                        
                                if ($attendeeType === 'required') {
                                    $eventsData[count($eventsData) - 1]['attendees_required'][] = $emailAddress;
                                } elseif ($attendeeType === 'optional') {
                                    $eventsData[count($eventsData) - 1]['attendees_optional'][] = $emailAddress;
                                }
                            }
                        }
                        

                        // Return the events data as JSON
                        return response()->json($eventsData);
                        //return view('events',$responseData1);
                        //return view('events', ['eventsData' => $eventsData]);
                    } else {
                        // Handle the case where there are no events in the response
                        return response()->json([]);
                    }
                } else {
                    // Handle error response here (e.g., display an error message)
                    return response()->json(['error' => 'Error fetching events'], 500);
                }
            } else {
                // Handle the error when 'access_token' is not present in the response
                return response()->json(['error' => 'Access token not found in response'], 500);
            }
        
    } catch (\Exception $e) {
        // Handle exceptions such as network errors
        return response()->json(['error' => 'Token request exception'], 500);
    }
}

public function createMeeting()
{
   
$accessToken ='EwBoA8l6BAAUAOyDv0l6PcCVu89kmzvqZmkWABkAAbpCuNgHsjZlT0Xkm1qyWxBeVjvO0E+zqVXtZ0M7MKzMVZdHFhl/QLEVhIy2KaqkCfBrWqGRIvI/ntyQvYluqH8EuRlBcbrsHhT1qbjY8O/TQ8odfO7Ux9M7/5ucw2dC60zXu3UvHCgXamDrJa5lEcYyqf2STNNFYyEA2iUUeymD3tXUhPGokB/v73gyMd90mMKRlzRleu3ly82vZ+2CE970Bd7KlgwTp5t9uxikC8E2LM7z1gRpqjD5yz5RyyipbYrtHJGMb07IaFnSvYtCcpI0PUAxR4MfdGJsNQ6nckb2+2ZV54POciNJ2vjahvF+nLCcrcWXW+1PbyDXp8ZwA4QDZgAACK+qGXNsOF52OALDPhzRp01Cn8DXQS5mNlB/MPHVq72/nQ75wBIbg6nwqsh4MTdFZe4w9lmOAtLwQ8TWe1cIzxBpvGsSrC35o/B//fDd+r5r4prhUZRkkgcNiAXAAGzpKDOcKrrP1QGPvTWgPghxyM5NU6eal6StnfLwLI9P60WlAB4xI/X7OAAWVD8Ufu5Auq44aJnqYGwNrn9i+hEWZF/UGz7SBY1H3S3NLepNuJkZCA75xUA8sb8jSxw99Je5oiQuqESRFEclKpNLcrnxJ2rgWAho2eLAzDQXlk/5pwMv0HEmudzupW2nc5wiFL0/4TV+gu7k7HOWNv1eqR6PvOm640ZMRjWiQLhnq+j2lp/GOeW/9pRorxzim022KApe81eoH8C0FiSRbTqz5jho23i0edeD2cY6tPeIs8SdQhKBHwaWtg+2aKizCnkO489UuZ44h8RO3DA5+Q5m39ijLmQ/2VX8aWk5yuU/XVe0+xt+kWcHDukLEeqfcwc/3qq9fYCJLxqIBchoR8mO1cJ/LC7wpirEGGgThjky90JA3JkdAJTXDxlpQr6Gq7EYCqjH5dIuv/BGynO67j4HG9tczmK+401QxNqaMmXgczagStJYFXTz+wyEfjp3Ln2/uzMHWr/A1Ev5fe5lXOXoHT8uOZFwEXPHLssen/9GCt2+hBTnRaOFbC0ya25t7GOnxU09l3F7XZlKPYw+U2L0u/GaLJ6smyCmhruiP6OeC/MM84Y/TarVvfTrg7bQYcyiNp6GKrpEgwI=';
// Acquire an access token with appropriate permissions


// Create a meeting in Microsoft Teams
$client = new Client([
    'verify' => false, // Disable SSL verification if needed
]);
$response = $client->post('https://graph.microsoft.com/v1.0/me/events', [
    'headers' => [
        'Authorization' => 'Bearer ' . $accessToken,
        'Content-Type' => 'application/json',
    ],
    'json' => [
        "subject" => "Let's go for lunch",
        "body" => [
            "contentType" => "HTML",
            "content" => "Does noon work for you?"
        ],
        "start" => [
            "dateTime" => "2023-10-26T12:00:00",
            "timeZone" => "Pacific Standard Time"
        ],
        "end" => [
            "dateTime" => "2023-10-26T14:00:00",
            "timeZone" => "Pacific Standard Time"
        ],
        "location" => [
            "displayName" => "Harry's Bar"
        ],
        "attendees" => [
            [
                "emailAddress" => [
                    "address" => "gopi.smiksystems@gmail.com",
                    "name" => ""
                ],
                "type" => "required"
            ]
        ],
        //"allowNewTimeProposals" => true,
        "isOnlineMeeting" => true,
        //"onlineMeetingProvider" => "teamsForBusiness",
        //"AllowNewTimeProposals" => true,  
        //OnlineMeetingProvider OnlineMeetingProviderType.TeamsForBusiness  
    ],
]);


// Parse the response and retrieve the meeting link
$data = json_decode($response->getBody(), true);
return $data;
// $meetingLink now contains the Microsoft Teams meeting link

}






}


