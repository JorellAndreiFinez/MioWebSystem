{{-- resources/views/mio/student-access/panel/student.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('storage/assets/images/1.2.2 home-pid.png/') }}">

    {{-- Conditional CSS based on the page --}}
    @if ($page === 'inbox')
        @vite(['resources/css/Mio/dashboard/inbox.css', 'resources/css/Mio/mio-app.css'])
    @elseif($page === 'dashboard')
        @vite(['resources/css/Mio/dashboard/dashboard.css', 'resources/js/Mio/dashboard/dashboard.js', 'resources/css/Mio/mio-app.css'])
    @elseif ($page === 'module')
        @vite(['resources/css/Mio/dashboard/subject-components.css', 'resources/js/Mio/dashboard/subject-components.js', 'resources/css/Mio/mio-app.css'])
    @elseif ($page === 'calendar')
        @vite(['resources/css/Mio/dashboard/calendar.css', 'resources/js/Mio/dashboard/calendar.js', 'resources/css/Mio/mio-app.css'])
    @elseif ($page === 'profile')
        @vite(['resources/css/Mio/dashboard/profile.css', 'resources/js/Mio/dashboard/profile.js', 'resources/css/Mio/mio-app.css'])
    @elseif ($page === 'subject')
        @vite(['resources/js/Mio/dashboard/subject.js', 'resources/css/Mio/mio-app.css'])
    @elseif (in_array($page, ['announcement', 'announcement-body', 'assignment', 'assignment-body', 'scores', 'module', 'module-body', 'quiz', 'quiz-body']))
        @vite(['resources/css/Mio/dashboard/subject-components.css', 'resources/js/Mio/dashboard/subject-components.js', 'resources/css/Mio/mio-app.css'])
    @elseif($page === 'people')
        @vite(['resources/css/Mio/admin/panel.css', 'resources/js/Mio/admin/panel.js', 'resources/css/Mio/mio-app.css'])

    @endif

    @include('mio.external-links')
</head>

<body>
    @include('mio.sidebar')

    {{-- Dynamic content switching --}}
    @if ($page === 'dashboard')
        @include('mio.student-access.main.main')

    @elseif ($page === 'calendar')
        @include('mio.student-access.calendar.calendar')

    @elseif ($page === 'inbox')
        @include('mio.student-access.inbox.inbox')

    @elseif ($page === 'profile')
        @include('mio.student-access.profile.profile')

    @elseif ($page === 'subject')
        @include('mio.student-access.subject.subject')

    @elseif ($page === 'quiz')
        @include('mio.student-access.subject.quiz.quiz')
    @elseif ($page === 'quiz-body')
        @include('mio.student-access.subject.quiz.quiz-body')

    @elseif ($page === 'announcement')
        @include('mio.student-access.subject.announcement.announcement')
    @elseif ($page === 'announcement-body')
        @include('mio.student-access.subject.announcement.announcement-body')

    @elseif ($page === 'assignment')
        @include('mio.student-access.subject.assignment.assignment')
    @elseif ($page === 'assignment-body')
        @include('mio.student-access.subject.assignment.assignment-body')

    @elseif ($page === 'scores')
        @include('mio.student-access.subject.score.score')

    @elseif ($page === 'people')
        @include('mio.student-access.subject.people.people')

    @elseif ($page === 'module')
        @include('mio.student-access.subject.module.module', ['modules' => $modules])
    @elseif ($page === 'module-body')
        @include('mio.student-access.subject.module.module-body', ['module' => $module])
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
