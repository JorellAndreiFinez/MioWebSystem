<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('layouts.campus.head')

<body>

<!-- NAVBAR -->
@include('layouts.navbar');

<!-- HEADER  -->
@include('layouts.campus.header');

<!-- CONTENT SECTION -->
@include('layouts.campus.content');

<!----- FOOTER ----->
@extends('layouts.footer')

@include('layouts.campus.campus-styles')
@include('mio-styles')
@include('main-app-styles')


</body>
</html>
