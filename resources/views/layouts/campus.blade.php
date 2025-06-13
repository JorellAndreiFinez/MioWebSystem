<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('layouts.campus.head')

<body>

<!-- NAVBAR -->
@include('layouts.navbar')

<!-- HEADER  -->
@include('layouts.campus.header')

<!-- CONTENT SECTION -->
@include('layouts.campus.content')

<!----- FOOTER ----->
@extends('layouts.footer')


</body>
</html>
