
<div class="mobile-toggle">
    <i class='bx bx-menu'></i>
</div>

<div class="sidebar">
    <!-- Sidebar Toggle Button -->
<div class="logo-details">
  <div class="logo_name">MIO</div>
  <i class='bx bx-menu' id="btn"></i>
</div>


    @php
        $menuSections = [
            'Menu' => [
                ['route' => 'mio.student-panel', 'icon' => 'bx bx-grid-alt', 'label' => 'Dashboard'],
                ['route' => 'mio.calendar', 'icon' => 'bx bx-calendar', 'label' => 'Calendar'],
                ['route' => 'mio.inbox', 'icon' => 'bx bx-message', 'label' => 'Inbox'],
            ],
            'Other' => [
                ['route' => 'mio.student.profile', 'icon' => 'bx bx-user', 'label' => 'Profile'],
                ['url' => '#', 'icon' => 'bx bx-cog', 'label' => 'Setting', 'custom_active' => request()->is('settings')],
            ],
        ];
    @endphp

    <ul class="nav-list">
        @foreach ($menuSections as $section => $items)
            <h3 class="title-label">{{ $section }}</h3>
            @foreach ($items as $item)
                @php
                    $isActive = isset($item['route'])
                        ? request()->routeIs($item['route'])
                        : (!empty($item['custom_active']) && $item['custom_active']);

                    $href = isset($item['route']) ? route($item['route']) : $item['url'];
                @endphp

                <li>
            <a href="{{ $href }}" class="{{ $isActive ? 'active' : '' }}">
                <div class="icon-wrapper">
                    <i class="{{ $item['icon'] }}"></i>
                    @if($item['label'] === 'Inbox' && $hasUnreadMessages ?? false)
                        <span class="nav-red-dot"></span>
                    @endif
                </div>
                <span class="links_name">{{ $item['label'] }}</span>
            </a>
        </li>

            @endforeach
            <br>
        @endforeach

        <!-- Profile + Logout -->
        <li class="profile">
            <div class="profile-details">
                <div class="name_job">
                    <div class="name">{{ session('firebase_user.name') ?? 'User' }}</div>
                    <div class="job">{{ session('firebase_user.role') ?? 'Role' }}</div>
                </div>
            </div>
           <a href="{{ route('logout') }}">
                <i class='bx bx-log-out' id="log_out"></i>
            </a>
        </li>
    </ul>
</div>




<script>
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.querySelector("#btn");

    // Toggle sidebar open/close
    toggleBtn.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent toggle click from triggering the document click
        sidebar.classList.toggle("open");
    });

    // Close sidebar when clicking outside (on small screens)
    document.addEventListener("click", (e) => {
        if (
            window.innerWidth <= 768 &&
            sidebar.classList.contains("open") &&
            !sidebar.contains(e.target) &&
            !toggleBtn.contains(e.target)
        ) {
            sidebar.classList.remove("open");
        }
    });

    // Close sidebar if resized to desktop view
    window.addEventListener("resize", () => {
        if (window.innerWidth > 768 && sidebar.classList.contains("open")) {
            sidebar.classList.remove("open");
        }
    });

    const mobileToggle = document.querySelector(".mobile-toggle");

    // Toggle sidebar from mobile toggle
    mobileToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        sidebar.classList.toggle("open");
    });

</script>


<style>
    @media (max-width: 768px) {
    .sidebar {
        left: -230px; /* Start hidden */
        width: 230px;
        transition: left 0.3s ease;
    }

    .nav-red-dot {
        position: absolute;
        top: 8px;
        right: 12px;
        width: 8px;
        height: 8px;
        background-color: red;
        border-radius: 50%;
    }


    .sidebar.open {
        left: 0; /* Slide in when open */
    }

    .home-section {
        left: 0;
        width: 100%;
        transition: all 0.3s ease;
    }

    .sidebar.open ~ .home-section {
        position: fixed;
        top: 0;
        left: 230px;
        width: calc(100% - 230px);
        height: 100vh;
        background: #2264DC; /* Optional: overlay background */
    }
}
.mobile-toggle {
    display: none;
    font-size: 28px;
    padding: 10px;
    cursor: pointer;
}

@media (max-width: 768px) {
    .mobile-toggle {
        display: block;
    }
}


</style>

