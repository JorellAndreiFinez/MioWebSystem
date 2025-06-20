<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Philippine Institute for the Deaf</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('storage/assets/images/dummy1.jpg') }}">

    @vite(['resources/css/cms/enroll.css', 'resources/js/cms/enroll.js', 'resources/css/cms/main-app.css'])

    @include('layouts.external-links')
</head>

<style>
    .header {
        background: url('{{ asset('storage/assets/images/2 admission-banner.png') }}') center/cover no-repeat;
    }
</style>
