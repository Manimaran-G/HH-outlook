<?php

namespace App\Http\Controllers;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Event;

use Illuminate\Http\Request;

class GraphController extends Controller
{
     public function getGraph(){
        

        // Create a new instance of the Graph client
            $graph = new Graph();
            $graph->setAccessToken($accessToken); // Set the access token obtained during authentication

            // Define the event details
            $event = new Event([
                'subject' => 'Meeting with Client',
                'start' => [
                    'dateTime' => '2023-09-01T10:00:00',
                    'timeZone' => 'UTC', // Use the appropriate time zone
                ],
                'end' => [
                    'dateTime' => '2023-09-01T11:00:00',
                    'timeZone' => 'UTC', // Use the appropriate time zone
                ],
            ]);

            // Create the event in the user's calendar
            $response = $graph->createRequest('POST', '/me/events')
                ->attachBody($event)
                ->execute();

            // Handle the response as needed
            $createdEvent = $response->getBody();

     }
}


 


