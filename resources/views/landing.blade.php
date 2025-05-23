<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('layouts.head')

<style>
    .carousel-item:nth-child(1) {
    background-image: url('{{ asset('storage/assets/images/1.1.1 home-slider.png') }}');
}

.carousel-item:nth-child(2) {
    background-image: url('{{ asset('storage/assets/images/1.1.2 home-slider.png') }}');
}

.carousel-item:nth-child(3) {
    background-image: url('{{ asset('storage/assets/images/1.1.3 home-slider.png') }}');
}

.carousel-item:nth-child(4) {
    background-image: url('{{ asset('storage/assets/images/1.1.4 home-slider.png') }}');
}
</style>

<body>

<!-- NAVBAR -->
@include('layouts.navbar');

@include('layouts.landing.header')
@include('layouts.landing.content')



<!----- FOOTER ----->
@extends('layouts.footer')


</body>
</html>
