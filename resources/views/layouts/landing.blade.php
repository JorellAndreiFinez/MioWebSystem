<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('layouts.head')

<body>

<!-- NAVBAR -->
@include('layouts.navbar');

<!-- HEADER  -->
@include('layouts.landing.header');

<!-- CONTENT SECTION -->
@include('layouts.landing.content');

<!----- FOOTER ----->
@extends('layouts.footer')


</body>
</html>
