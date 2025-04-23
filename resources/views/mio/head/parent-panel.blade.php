{{-- resources/views/mio/student-access/panel/student.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('storage/assets/images/1.2.2 home-pid.png/') }}">

    {{-- Conditional CSS based on the page --}}
    @if   ($page === 'parent-dashboard')
        @vite(['resources/css/Mio/dashboard/dashboard.css', 'resources/js/Mio/dashboard/dashboard.js', 'resources/css/Mio/mio-app.css'])
    @elseif ($page === 'parent-inbox')
        @vite(['resources/css/Mio/dashboard/inbox.css', 'resources/css/Mio/mio-app.css'])
    @elseif ($page === 'parent-calendar')
        @vite(['resources/css/Mio/dashboard/calendar.css', 'resources/js/Mio/dashboard/calendar.js', 'resources/css/Mio/mio-app.css'])
    @elseif ($page === 'parent-profile')
        @vite(['resources/css/Mio/dashboard/profile.css', 'resources/js/Mio/dashboard/profile.js', 'resources/css/Mio/mio-app.css'])

    @elseif ($page === 'module')
        @vite(['resources/css/Mio/dashboard/subject-components.css', 'resources/js/Mio/dashboard/subject-components.js', 'resources/css/Mio/mio-app.css'])


    @elseif ($page === 'subject')
        @vite(['resources/js/Mio/dashboard/subject.js', 'resources/css/Mio/mio-app.css'])
    @elseif (in_array($page, ['announcement', 'announcement-body', 'assignment', 'assignment-body', 'scores', 'module', 'module-body']))
        @vite(['resources/css/Mio/dashboard/subject-components.css', 'resources/js/Mio/dashboard/subject-components.js', 'resources/css/Mio/mio-app.css'])

    @endif

    @include('mio.external-links')
</head>

<body>
    @include('mio.parent-access.parent-sidebar')

    {{-- Dynamic content switching --}}
    @if ($page === 'parent-dashboard')
        @include('mio.parent-access.main.main')
    @elseif ($page === 'parent-calendar')
        @include('mio.parent-access.calendar.calendar')
    @elseif ($page === 'parent-inbox')
        @include('mio.parent-access.inbox.inbox')
    @elseif ($page === 'parent-profile')
        @include('mio.parent-access.profile.profile')
    @endif

    {{-- Sidebar script (keep the collapse logic) --}}
    <script>
        let sidebar = document.querySelector(".sidebar");
        let closeBtn = document.querySelector("#btn");
        let searchBtn = document.querySelector(".bx-search");

        closeBtn.addEventListener("click", () => {
            sidebar.classList.toggle("open");
            menuBtnChange();
        });

        if (searchBtn) {
            searchBtn.addEventListener("click", () => {
                sidebar.classList.toggle("open");
                menuBtnChange();
            });
        }

        function menuBtnChange() {
            if (sidebar.classList.contains("open")) {
                closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else {
                closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");
            }
        }
    </script>
</body>

</html>
