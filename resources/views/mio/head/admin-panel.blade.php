<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">



    <link rel="icon" type="image/x-icon" href="{{ asset('storage/assets/images/1.2.2 home-pid.png/') }}">

    @vite(['resources/css/Mio/admin/panel.css', 'resources/js/Mio/admin/panel.js', 'resources/css/Mio/mio-app.css'])

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <!-- Bootstrap 4 JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @include('mio.external-links')
</head>


<body>

@include('mio.admin-access.panel.admin-sidebar');

    @if ($page === 'dashboard')
        @include('mio.admin-access.panel.panel-body')
    @elseif ($page === 'teachers')
        @include('mio.admin-access.teachers')
    @elseif ($page === 'add-teacher')
        @include('mio.admin-access.teacher.add-teacher')
    @elseif ($page === 'edit-teacher')
        @include('mio.admin-access.teacher.edit-teacher')

    @elseif ($page === 'admin-enrollment')
        @include('mio.admin-access.enrollment')
    @elseif ($page === 'admin-enrollee')
        @include('mio.admin-access.enrollment.view-enrollee')
    @elseif ($page === 'edit-assessment')
        @include('mio.admin-access.enrollment.edit-assessment')
    @elseif ($page === 'edit-assessment2')
        @include('mio.admin-access.enrollment.edit-assessment2')

    @elseif ($page === 'pid')
        @include('mio.admin-access.pid')
    @elseif ($page === 'edit-nav')
        @include('mio.admin-access.pid.nav.edit-nav')

    @elseif ($page === 'students')
        @include('mio.admin-access.students')
    @elseif ($page === 'add-student')
        @include('mio.admin-access.student.add-student')
    @elseif ($page === 'edit-student')
        @include('mio.admin-access.student.edit-student')

    @elseif ($page === 'accounts')
        @include('mio.admin-access.accounts')

    @elseif ($page === 'admin')
        @include('mio.admin-access.admin')
    @elseif ($page === 'add-admin')
        @include('mio.admin-access.admin.add-admin')
    @elseif ($page === 'edit-admin')
        @include('mio.admin-access.admin.edit-admin')

    @elseif ($page === 'parent')
        @include('mio.admin-access.parent')
    @elseif ($page === 'add-parent')
        @include('mio.admin-access.parent.add-parent')
    @elseif ($page === 'edit-parent')
        @include('mio.admin-access.parent.edit-parent')

    @elseif ($page === 'subjects')
        @include('mio.admin-access.subjects')
    @elseif ($page === 'view-subject')
        @include('mio.admin-access.subject.view-subject')
    @elseif ($page === 'add-subjects')
        @include('mio.admin-access.subject.add-subject')
    @elseif ($page === 'edit-subject')
        @include('mio.admin-access.subject.edit-subject')

    @elseif ($page === 'school')
        @include('mio.admin-access.school')
<!--
    @elseif ($page === 'view-calendar')
        @include('mio.admin-access.school.view-calendar')
    @elseif ($page === 'add-calendar')
        @include('mio.admin-access.school.add-calendar')
    @elseif ($page === 'edit-calendar')
        @include('mio.admin-access.school.edit-calendar') -->

    @elseif ($page === 'view-department')
        @include('mio.admin-access.school.view-department')
    @elseif ($page === 'add-department')
        @include('mio.admin-access.school.add-department')
    @elseif ($page === 'edit-department')
        @include('mio.admin-access.school.edit-department')

    @elseif ($page === 'view-schedule')
        @include('mio.admin-access.school.view-schedule')
    @elseif ($page === 'add-schedule')
        @include('mio.admin-access.school.add-schedule')
    @elseif ($page === 'edit-schedule')
        @include('mio.admin-access.school.edit-schedule')

    @elseif ($page === 'view-announcement')
        @include('mio.admin-access.school.view-announcement')
    @elseif ($page === 'add-announcement')
        @include('mio.admin-access.school.add-announcement')
    @elseif ($page === 'edit-announcement')
        @include('mio.admin-access.school.edit-announcement')

    @elseif ($page === 'view-schoolyear')
        @include('mio.admin-access.school.view-schoolyear')
    @elseif ($page === 'add-schoolyear')
        @include('mio.admin-access.school.add-schoolyear')
    @elseif($page === 'edit-schoolyear')
        @include('mio.admin-access.school.edit-schoolyear')


    @elseif ($page === 'view-section')
        @include('mio.admin-access.school.view-section')
    @elseif ($page === 'add-section')
        @include('mio.admin-access.school.add-section', ['teachers' => $teachers])
    @elseif ($page === 'edit-section')
        @include('mio.admin-access.school.edit-section')

    @elseif ($page === 'emergency')
        @include('mio.admin-access.emergency')

     @elseif ($page === 'admin-analytics')
        @include('mio.admin-access.admin-analytics')
    @endif

    <script>
    let sidebar = document.querySelector(".sidebar");
    let closeBtn = document.querySelector("#btn");
    let tooltips = document.querySelectorAll(".sidebar .tooltip");

    closeBtn.addEventListener("click", () => {
        sidebar.classList.toggle("open");
        menuBtnChange();
    });

    // Optional: close/open using search icon if used
    let searchBtn = document.querySelector(".bx-search");
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

    document.querySelectorAll(".nav-list li").forEach((item) => {
    item.addEventListener("mouseenter", () => {
        if (!sidebar.classList.contains("open")) {
        let tooltip = item.querySelector(".tooltip");
        if (tooltip) {
            tooltip.style.opacity = "1";
            tooltip.style.pointerEvents = "auto";
        }
        }
    });

    item.addEventListener("mouseleave", () => {
        let tooltip = item.querySelector(".tooltip");
        if (tooltip) {
        tooltip.style.opacity = "0";
        tooltip.style.pointerEvents = "none";
        }
    });
    });
        // Optional: Close sidebar on clicking outside
        document.addEventListener("click", (event) => {
            if (!sidebar.contains(event.target) && !closeBtn.contains(event.target)) {
            sidebar.classList.remove("open");
            menuBtnChange();
            }
        });
</script>


    <!-- Emergency Modal (hidden initially) -->
<div id="emergencyModal" class="modal" style="display:none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.6); justify-content: center; align-items: center;">
    <div id="modalBox" style="background: yellow; transition: background 0.5s ease; padding: 2rem; border-radius: 10px; text-align: center; width: 300px;">
        <h3 id="modalCountdownText" style="font-size: 24px; margin-bottom: 20px;">Starting in 3...</h3>
        <button id="cancelButton" onclick="cancelEmergency()" style="margin-right: 10px;">Cancel</button>
        <button id="finishButton" style="display: none;" onclick="closeEmergencyModal()">Finish</button>
    </div>
</div>

<script>
    let currentEmergencyId = null;
let selectedEmergencyName = '';
let emergencyInterval = null;

function showEmergencyModalFromExisting(data) {
    currentEmergencyId = data.id;
    selectedEmergencyName = data.name;

    const modal = document.getElementById('emergencyModal');
    const text = document.getElementById('modalCountdownText');
    const button = document.getElementById('finishButton');
    const modalBox = document.getElementById('modalBox');

    modal.style.display = 'flex';
    modalBox.style.background = 'red';
    text.textContent = 'Emergency Vibration is Started';
    button.style.display = 'inline-block';
}

// On every page load, check if emergency is active:
window.onload = function () {
    fetch("{{ route('emergency.active') }}")
    .then(response => response.json())
    .then(data => {
        if (data.active) {
            // Show emergency modal immediately on all pages
            showEmergencyModalFromExisting(data);

            // Redirect to emergency page if not already there
            if (!window.location.pathname.includes('/emergency')) {
                window.location.href = "{{ route('mio.emergency') }}";
            }
        }
    });
};

function closeEmergencyModal() {
    if (!currentEmergencyId) {
        alert('No emergency is active.');
        return;
    }

    fetch("{{ route('emergency.stop-vibration') }}", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        body: JSON.stringify({ id: currentEmergencyId })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Vibration stopped:', data);
        alert("Emergency Vibration Stopped.");
        document.getElementById('emergencyModal').style.display = 'none';
        selectedEmergencyName = '';
        currentEmergencyId = null;

        // After stopping emergency, redirect away from emergency page if needed
        if (window.location.pathname.includes('/emergency')) {
            window.location.href = "{{ route('mio.admin-panel') }}"; // or any safe default
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Failed to stop vibration.");
    });
}

function cancelEmergency() {
    // Optionally allow cancel to hide modal but vibration remains active
    clearInterval(emergencyInterval);
    document.getElementById('emergencyModal').style.display = 'none';
    selectedEmergencyName = '';
}

</script>

</body>
</html>
