<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('layouts.program.head')

<body>

<!-- NAVBAR -->
@include('layouts.navbar')

<!-- HEADER  -->
@include('layouts.program.header')

<!-- CONTENT SECTION -->
@include('layouts.program.content')

<!----- FOOTER ----->
@extends('layouts.footer')


</body>
</html>
