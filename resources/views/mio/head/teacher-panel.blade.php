{{-- resources/views/mio/student-access/panel/student.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('storage/assets/images/1.2.2 home-pid.png/') }}">

    {{-- Conditional CSS based on the page --}}
    @if ($page === 'teacher-inbox')
        @vite(['resources/css/Mio/dashboard/inbox.css', 'resources/css/Mio/mio-app.css'])
    @elseif($page === 'teacher-dashboard')
        @vite(['resources/css/Mio/dashboard/dashboard.css', 'resources/js/Mio/dashboard/dashboard.js', 'resources/css/Mio/mio-app.css'])
    @elseif ($page === 'module')
        @vite(['resources/css/Mio/dashboard/subject-components.css', 'resources/js/Mio/dashboard/subject-components.js', 'resources/css/Mio/mio-app.css'])
    @elseif ($page === 'calendar')
        @vite(['resources/css/Mio/dashboard/calendar.css', 'resources/js/Mio/dashboard/calendar.js', 'resources/css/Mio/mio-app.css'])
    @elseif ($page === 'profile')
        @vite(['resources/css/Mio/dashboard/profile.css', 'resources/js/Mio/dashboard/profile.js', 'resources/css/Mio/mio-app.css'])
    @elseif ($page === 'teacher-subject')
        @vite(['resources/js/Mio/dashboard/subject.js', 'resources/css/Mio/mio-app.css'])
    @elseif (in_array($page, ['announcement', 'announcement-body', 'assignment', 'assignment-body', 'scores', 'module', 'module-body']))
        @vite(['resources/css/Mio/dashboard/subject-components.css', 'resources/js/Mio/dashboard/subject-components.js', 'resources/css/Mio/mio-app.css'])
    @elseif($page === 'people')
        @vite(['resources/css/Mio/admin/panel.css', 'resources/js/Mio/admin/panel.js', 'resources/css/Mio/mio-app.css'])

    @endif

    @include('mio.external-links')
</head>

<body>
    @include('mio.teacher-access.teacher-sidebar')

    {{-- Dynamic content switching --}}
    @if ($page === 'teacher-dashboard')
        @include('mio.teacher-access.main.main')

    @elseif ($page === 'calendar')
        @include('mio.student-access.calendar.calendar')

    @elseif ($page === 'teacher-inbox')
        @include('mio.teacher-access.inbox.inbox')

    @elseif ($page === 'profile')
        @include('mio.teacher-access.profile.profile')

    @elseif ($page === 'teacher-subject')
        @include('mio.teacher-access.subject.subject')

    @elseif ($page === 'announcement')
        @include('mio.teacher-access.subject.announcement.announcement')
    @elseif ($page === 'announcement-body')
        @include('mio.teacher-access.subject.announcement.announcement-body')

    @elseif ($page === 'assignment')
        @include('mio.teacher-access.subject.assignment.assignment')
    @elseif ($page === 'assignment-body')
        @include('mio.teacher-access.subject.assignment.assignment-body')

    @elseif ($page === 'scores')
        @include('mio.teacher-access.subject.score.score')

    @elseif ($page === 'people')
        @include('mio.teacher-access.subject.people.people')

    @elseif ($page === 'module')
        @include('mio.teacher-access.subject.module.module')
    @elseif ($page === 'module-body')
        @include('mio.teacher-access.subject.module.module-body')
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
