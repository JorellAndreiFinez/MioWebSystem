<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('mio.dashboard.head')

<body class="darkmode">

@include('mio.dashboard.landing.landing');


<script>
    //! Light/Dark Mode
const moonIcon = document.querySelector(".moon");
const sunIcon = document.querySelector(".sun");
const nightImage = document.querySelector(".night-img");
const morningImage = document.querySelector(".morning-img");
const toggle = document.querySelector(".toggle");

function switchTheme() {
    document.body.classList.toggle("darkmode");
    if (document.body.classList.contains("darkmode")) {
        sunIcon.classList.remove("hidden");
        moonIcon.classList.add("hidden");
        morningImage.classList.add("hidden");
        nightImage.classList.remove("hidden");
    } else {
        sunIcon.classList.add("hidden");
        moonIcon.classList.remove("hidden");
        morningImage.classList.remove("hidden");
        nightImage.classList.add("hidden");
    }
}
</script>
</body>
</html>
