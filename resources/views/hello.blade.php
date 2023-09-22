@extends('layout')

@section('content')
<div class="jumbotron">
    <p class="lead"> Microsoft Graph API to access a user's data from PHP</p>
    @if(isset($userName))
        <h4>Welcome {{ $userName }}!</h4>
        <p>Use the navigation bar at the top of the page to get started.</p>
    @else
        <a href="/hello" class="btn btn-primary btn-large">Click here to sign in</a>
    @endif
</div>

@if(isset($events))
<div class="container">
    <h2>Events</h2>
    <ul>
        @foreach($events as $event)
        <li>
            <strong>Title:</strong> {{ $event['title'] }}<br>
            <strong>Start:</strong> {{ $event['start'] }}<br>
            <strong>End:</strong> {{ $event['end'] }}<br>
        </li>
        @endforeach
    </ul>
</div>
@endif

@endsection
