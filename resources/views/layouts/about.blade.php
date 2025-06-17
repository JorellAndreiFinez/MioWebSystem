<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('layouts.about.head')

<body>

<!-- NAVBAR -->
@include('layouts.navbar');

<!-- HEADER  -->
@include('layouts.about.header');

<!-- CONTENT SECTION -->
@include('layouts.about.content');

<!----- FOOTER ----->
@extends('layouts.footer')

@include('layouts.about.about-styles')
@include('mio-styles')

@include('main-app-styles')

</body>
</html>
