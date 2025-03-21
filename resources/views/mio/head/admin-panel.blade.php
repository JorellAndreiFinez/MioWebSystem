<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('storage/assets/images/dummy1.jpg') }}">

    @vite(['resources/css/Mio/admin/panel.css', 'resources/js/Mio/admin/panel.js', 'resources/css/Mio/mio-app.css'])

    @include('mio.external-links')
</head>

<body>

@include('mio.admin-access.panel.admin-sidebar');
@if ($page === 'dashboard')
        @include('mio.admin-access.panel.panel-body')
    @elseif ($page === 'teachers')
        @include('mio.admin-access.teachers')
    @elseif ($page === 'students')
        @include('mio.admin-access.students')
    @elseif ($page === 'accounts')
        @include('mio.admin-access.accounts')
    @elseif ($page === 'subjects')
        @include('mio.admin-access.subjects')
    @elseif ($page === 'schedules')
        @include('mio.admin-access.schedules')
    @elseif ($page === 'school')
        @include('mio.admin-access.school')
    @elseif ($page === 'emergency')
        @include('mio.admin-access.emergency')
    @endif

</body>



</html>
