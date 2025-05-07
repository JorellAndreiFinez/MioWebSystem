<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Panel</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('storage/assets/images/1.2.2 home-pid.png/') }}">

    @vite(['resources/css/EnrollPanel/enrollment-panel.css', 'resources/css/Mio/mio-app.css'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Add to your head section -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.8/build/css/intlTelInput.css">

    <!-- Add to your body section, before the closing </body> tag -->
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.8/build/js/intlTelInput.min.js"></script>

    <!-- Firebase App (core) SDK -->
    <script src="https://www.gstatic.com/firebasejs/10.8.1/firebase-app.js"></script>

    <!-- Firebase Auth SDK -->
    <script src="https://www.gstatic.com/firebasejs/10.8.1/firebase-auth.js"></script>

    <script type="module" src="/firebase/firebase-config.js"></script>



    @include('mio.external-links')
</head>

<body>

    @if ($page === 'enroll-login')
        @include('enrollment-panel.enroll-login')
    @elseif ($page === 'enroll-dashboard')
        @include('enrollment-panel.enroll-dashboard')
    @elseif ($page === 'send-otp')
        @include('enrollment-panel.send-otp')
    @endif


</body>


</html>
