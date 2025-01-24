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


</body>
</html>
