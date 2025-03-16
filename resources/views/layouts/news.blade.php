<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('layouts.news.head')

<body>

<!-- NAVBAR -->
@include('layouts.navbar');

<!-- HEADER  -->
@include('layouts.news.header');

<!-- CONTENT SECTION -->
@include('layouts.news.content');

<!----- FOOTER ----->
@extends('layouts.footer')


</body>
</html>
