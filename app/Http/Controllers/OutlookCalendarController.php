<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
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
            'client_id' => 'b6d1242b-6b4c-4d20-a4ed-e14125685951',
            'scope' => 'offline_access User.Read Calendars.ReadWrite',
            'response_type' => 'code',
            'redirect_uri' => 'http://localhost:8000/callback', // Your redirect URL
            'response_mode' => 'query',
            'state' => bin2hex(random_bytes(16)),
        ]);

        return redirect("$url?$query");
    }

    public function callback(Request $request)
{
    // Extract the authorization code from the callback request
    $authorizationCode = $request->input('code');

    // Replace 'YOUR_CLIENT_SECRET' with your actual client secret
    $clientSecret = 'TM28Q~tLC37JgFHDrAnm0FQ6o9FjI.M1VpWdIcch'; // Replace with your actual client secret

    // Prepare the parameters for the token request
    $tokenParams = [
        'grant_type' => 'authorization_code',
        'client_id' => 'b6d1242b-6b4c-4d20-a4ed-e14125685951', // Replace with your actual client ID
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
                        

                        // Return the events data as JSON
                        //return response()->json($responseData1);
                        //return view('events',$responseData1);
                        $editEventId = 0;
                        return view('events', ['eventsData' => $eventsData,'editEventId' => $editEventId]);
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


    public function createEvent1(Request $request)
    {
        // Replace these with your own values
        $access_token = "EwBoA8l6BAAUAOyDv0l6PcCVu89kmzvqZmkWABkAAVPPHgKurVXVJfG4ylZOq1ejgteQFc/PT9k8gi4mYRuiqMZT+x2qVrYyZH2thtNZo+YPJwKA00pXrCc8c7CE/gNIn4pljtK1s9rBLlCUcU/zfoSNoUD+ju6Hjv86ysq+mXxXHuy+RPLwagYMlEaS9BykXJebK/PNYHDxzORgVLYoMJjXl30ipWljdUBzV4KTnCler7fQr47OhCe4Y5fNY5i/OkcrYE42HihEZkEVRLeVL1UAx4u20K3d+XQgszfxrkq/kfQXVTDDJNuhRyvuzcZFLtPx/VQ5SxQ/zFU5+M87O6OCkc/I9omHrohd2XUpvwJKTQ0gdGvgzMXjTet4Ni4DZgAACMXyGo+GIIBYOAKdj+4ex2hFza9PxD781cLur7BAyxi3p7Nc4QxuQH6+7otML/5K3IA/0nV/9tkZkUWe8ufX4Z9YemxJneVBbJjOasa2v07Z8IPC96aCd3EZhi0hlUcXvlQL0dpyyOWlsjzZKN7S5vc/qF5srceuDicKs3/ofBLrzJuOv5DMRzYP+gabjvQ80VnInddtfa3Uh/gTWBJBPwtQhI4kbkKkdxJwmk+gJof1ecsDAgCy2SniZCYJ56x6LPER+rYeWl6ABgoMk+0s6c7uD6aNOpQ8Mf0OuXIoNZvz4ulDHEscy4Jqhtqsh1VeG5fTdBa4y6U59gpixtDBS5uS/FJg3wMZ2SRA/6+q8TB7ci5xJUHEf+tHJxOt1/cfHAMbN0tfV7R9BPCxMU14S4yMUgw0aWNOidEq6OJp3DwfboFmNAxNvHd7e1vWIyy2r4OiklA2k+Z8uc0HDSGG64dLUIQc0xcTJ8WLZ6cV0IMw4McKFLPfQjcnQlFrAS6yJgZ5AT5ewgXdp5CbIP3iJKmlzFXrpp7Ppl990UEHaGEMhkiiIH6x+qxoCtFN8Qym21su7tFyeBwjtj2y0Mbvl16/ZR8nURNJO5RwHhXySewzLQk1/qovnH8o7RvNQojR4jHjdIWUB0RTPj8vFb4Bz8qHp8h38Z2tz0uSfAGYAb9TRGFTktNC6XidjZVcduMKPVQlyB0ugmQz/52knLRzhGJ4WUvPRP8ixG6Keh5D1WhJRPs8tv7p7pYmTH64J6O9b/cSgwI=Event ID: AQMkADAwATM0MDAAMS00Njc4LWI1MGItMDACLTAwCgBGAAAD9w3QyxI0h0aYkzJFyPdneQcA96mqKRJcE0Wy-9eK52XQpgAAAgENAAAA96mqKRJcE0Wy-9eK52XQpgABJnGl6gAAAA==";
        $event_data = [
                    "subject" => "Meeting with Client",
                    "start" => [
                    "dateTime" => "2023-09-23T21:00:00",
                    "timeZone" => "Asia/Kolkata"
                    ],
                    "end" => [
                    "dateTime" => "2023-09-23T21:30:00",
                    "timeZone" => "Asia/Kolkata"
                    ],
                    "location" => [
                    "displayName" => "Client Office"
                    ],
                    "body" => [
                    "contentType" => "HTML",
                    "content" => "Discuss project details."
                    ],
                    "attendees" => [
                    [
                        "emailAddress" => [
                            "address" => "john@example.com",
                            "name" => "John Doe"
                        ],
                        "type" => "required"
                    ],
                    [
                        "emailAddress" => [
                            "address" => "jane@example.com",
                            "name" => "Jane Smith"
                        ],
                        "type" => "required"
                    ]
                    ]
            ];


        $client = new Client([
            'verify' => false, // Disable SSL verification if needed
        ]);

        try {
            $response = $client->post('https://graph.microsoft.com/v1.0/me/events', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
                ],
                'json' => $event_data,
            ]);

            // Handle the response as needed (e.g., check for success)
            $statusCode = $response->getStatusCode();
            print_r($statusCode);
            if ($statusCode === 201) {
                // Event created successfully
                return response()->json(['message' => 'Event created successfully']);
            } else {
                // Handle error response
                return response()->json(['error' => 'Event creation failed'], $statusCode);
            }
        } catch (\Exception $e) {
            // Handle exceptions (e.g., token expiration)
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function createEvent(Request $request)
{
    // Get the data from the POST request body
    $requestData = $request->json()->all();

    // Extract the data from the request body
    $accessToken = $requestData['accesstoken'];
    $subject = $requestData['subject'];
    $startDateTime = $requestData['start'];
    $endDateTime = $requestData['end'];
    $timeZone = $requestData['timezone'];
    $location = $requestData['location'];
    $content = $requestData['content'];
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
            return response()->json(['message' => 'Event created successfully']);
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
}


