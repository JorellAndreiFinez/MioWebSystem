<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Panel</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('storage/assets/images/1.2.2 home-pid.png/') }}">

    @vite(['resources/css/EnrollPanel/enrollment-panel.css', 'resources/css/Mio/mio-app.css'])

    @include('mio.external-links')
</head>

<body>

    @if ($page === 'enroll-login')
        @include('enrollment-panel.enroll-login')
    @elseif ($page === 'enroll-dashboard')
        @include('enrollment-panel.enroll-dashboard')
    @endif


</body>


</html>
