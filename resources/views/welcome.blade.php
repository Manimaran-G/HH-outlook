<!-- Copyright (c) Microsoft Corporation.
     Licensed under the MIT License. -->

@extends('layout')

@section('content')
<div class="jumbotron">
  
  <p class="lead"> Microsoft Graph API to access a user's data from PHP</p>
  @if(isset($userName))
    <h4>Welcome {{ $userName }}!</h4>
    <p>Use the navigation bar at the top of the page to get started.</p>
  @else
    <a href="/signin" class="btn btn-primary btn-large">Click here to sign in</a>
  @endif
</div>
@endsection
