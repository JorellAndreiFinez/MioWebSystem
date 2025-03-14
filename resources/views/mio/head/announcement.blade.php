<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('resources/assets/images/dummy1.jpg') }}">

    @vite(['resources/css/Mio/dashboard/subject-announcement.css', 'resources/js/Mio/dashboard/subject-announcement.js', 'resources/css/Mio/mio-app.css'])

    @include('mio.external-links')
</head>

<body>

@include('mio.sidebar');
@include('mio.dashboard.subject.announcement.announcement-body');

</body>



</html>
