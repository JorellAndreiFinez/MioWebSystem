<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIO: PID's Learning Management System</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('storage/assets/images/1.2.2 home-pid.png/') }}">


    @vite(['resources/css/Mio/dashboard/calendar.css', 'resources/js/Mio/dashboard/calendar.js', 'resources/css/Mio/mio-app.css'])

    @include('mio.external-links')


</head>

<body>

@include('mio.sidebar');
@include('mio.dashboard.calendar.calendar-body');

</body>



</html>
