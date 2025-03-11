<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('layouts.enroll.head')

<body>

<!-- NAVBAR -->
@include('layouts.navbar');

<!-- HEADER  -->
@include('layouts.enroll.header');

<!-- CONTENT SECTION -->
@include('layouts.enroll.content');

<!----- FOOTER ----->
@extends('layouts.footer')


</body>
</html>
