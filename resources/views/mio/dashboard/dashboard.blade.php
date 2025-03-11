<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('mio.head.dashboard')

<body>

@include('mio.sidebar');
@include('mio.dashboard.main.main');

</body>



</html>
