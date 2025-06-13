<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Philippine Institute for the Deaf</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('storage/assets/images/dummy1.jpg') }}">

    @vite(['resources/css/cms/enroll.css', 'resources/js/cms/enroll.js', 'resources/css/cms/main-app.css'])

    @include('layouts.external-links')
</head>


<body>

<!-- NAVBAR -->
@include('layouts.navbar')

<!-- CONTENT SECTION -->
@include('layouts.enroll.processes.assessment-guide')

<!----- FOOTER ----->
@extends('layouts.footer')


</body>
</html>
