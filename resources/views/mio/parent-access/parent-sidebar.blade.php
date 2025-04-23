
<div class="mobile-toggle" id="btn">
    <i class='bx bx-menu'></i>
</div>

<div class="sidebar">
    <div class="logo-details">
        <div class="logo_name">MIO</div>
        <i class='bx bx-menu' id="btn2"></i>
    </div>

    @php
        $menuSections = [
            'Menu' => [
                ['route' => 'mio.parent-panel', 'icon' => 'bx bx-grid-alt', 'label' => 'Dashboard'],
                ['route' => 'mio.parent-calendar', 'icon' => 'bx bx-calendar', 'label' => 'Calendar'],
                ['route' => 'mio.parent-inbox', 'icon' => 'bx bx-message', 'label' => 'Inbox'],
            ],
            'Other' => [
                ['route' => 'mio.parent-profile', 'icon' => 'bx bx-user', 'label' => 'Profile'],
                ['url' => '#', 'icon' => 'bx bx-info-circle', 'label' => 'Help & Report', 'custom_active' => request()->is('help')],
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
                        <i class="{{ $item['icon'] }}"></i>
                        <span class="links_name">{{ $item['label'] }}</span>
                    </a>
                    <!-- <span class="tooltip">{{ $item['label'] }}</span> -->
                </li>
            @endforeach
            <br>
        @endforeach

        <!-- Profile + Logout -->
        <li class="profile">
            <div class="profile-details">
                <div class="name_job">
                    <div class="name">{{ Auth::user()->name ?? 'John Doe' }}</div>
                    <div class="job">{{ Auth::user()->role ?? 'Role' }}</div>
                </div>
            </div>
            <a href="{{ route('mio.login') }}">
                <i class='bx bx-log-out' id="log_out"></i>
            </a>
        </li>
    </ul>
</div>




<script>
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.querySelector("#btn");
    const toggleBtn2 = document.querySelector("#btn2");


    function toggleSidebar() {
        sidebar.classList.toggle("open");

        // Toggle menu icon
        const icon = toggleBtn.querySelector("i");
        icon.classList.toggle("bx-menu");
        icon.classList.toggle("bx-menu-alt-right");
    }

     // Toggle sidebar on button click
     toggleBtn.addEventListener("click", (event) => {
        event.stopPropagation(); // Prevent triggering outside click
        toggleSidebar();
        toggleBtn.style.display = "none";
    });
    // Toggle sidebar on button click
    toggleBtn2.addEventListener("click", (event) => {
        event.stopPropagation(); // Prevent triggering outside click
        toggleSidebar();
        toggleBtn.style.display = "none";

    });

    // Close sidebar if clicked outside (on smaller screens)
    document.addEventListener("click", (event) => {
        if (
            window.innerWidth <= 768 &&
            sidebar.classList.contains("open") &&
            !sidebar.contains(event.target) &&
            !toggleBtn.contains(event.target)
        ) {
            sidebar.classList.remove("open");

            // Reset icon back to hamburger
            const icon = toggleBtn.querySelector("i");
            icon.classList.remove("bx-menu-alt-right");
            icon.classList.add("bx-menu");
        }
    });

    // Optional: Automatically close sidebar on window resize if screen becomes small
    window.addEventListener("resize", () => {
        if (window.innerWidth > 768 && sidebar.classList.contains("open")) {
            sidebar.classList.remove("open");
            const icon = toggleBtn.querySelector("i");
            icon.classList.remove("bx-menu-alt-right");
            icon.classList.add("bx-menu");
        }
    });
</script>

