<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('layouts.events.head')

<body>

<!-- NAVBAR -->
@include('layouts.navbar')

<!-- HEADER  -->
@include('layouts.events.header')

<!-- CONTENT SECTION -->
@include('layouts.events.content')

<!----- FOOTER ----->
@extends('layouts.footer')


</body>
</html>
