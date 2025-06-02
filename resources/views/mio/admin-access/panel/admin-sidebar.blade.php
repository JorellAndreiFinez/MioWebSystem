
<div class="sidebar">
    <div class="logo-details">
        <div class="logo_name">MIO - Admin</div>
        <i class='bx bx-menu' id="btn"></i>
    </div>
    <ul class="nav-list">
        @php
        $menuSections = [
            'Menu' => [
                ['route' => 'mio.admin-panel', 'icon' => 'bx bx-grid-alt', 'label' => 'Dashboard'],
                ['route' => 'mio.teachers', 'icon' => 'bx bxs-graduation', 'label' => 'Teachers'],
                ['route' => 'mio.students', 'icon' => 'bx bx-user-voice', 'label' => 'Students'],
                ['route' => 'mio.accounts', 'icon' => 'bx bxs-user-detail', 'label' => 'Accounts'],
                ['route' => 'mio.subjects', 'icon' => 'bx bx-book-open', 'label' => 'Subjects'],
                ['route' => 'mio.schedules', 'icon' => 'bx bx-calendar', 'label' => 'Schedule'],
                ['route' => 'mio.school', 'icon' => 'bx bx-building', 'label' => 'School'],
                ['route' => 'mio.enrollment', 'icon' => 'bx bx-file', 'label' => 'Enrollment'],
            ],
            'Other' => [
                ['route' => 'mio.ViewCMS', 'icon' => 'bx bx-chalkboard', 'label' => 'PID Website'],
                ['route' => 'mio.emergency', 'icon' => 'bx bxs-error', 'label' => 'Emergency Alert'],
                ['route' => 'mio.ViewDataAnalytics', 'icon' => 'bx bxs-chart', 'label' => 'Data Analytics', 'custom_active' => request()->is('help')],
                ['url' => '#', 'icon' => 'bx bx-cog', 'label' => 'Setting', 'custom_active' => request()->is('settings')],
            ]
        ];
        @endphp

        @foreach($menuSections as $section => $items)
            <h3 class="title-label">{{ $section }}</h3>
            @foreach($items as $item)
                @php
                    $isActive = isset($item['route'])
                        ? request()->routeIs($item['route'])
                        : (!empty($item['custom_active']) && $item['custom_active']);
                    // Pass uid as a parameter for the dashboard or teachers route
                    $href = isset($item['route'])
                        ? (str_contains($item['route'], 'mio.admin-panel') || str_contains($item['route'], 'mio.teachers') || str_contains($item['route'], 'mio.students') || str_contains($item['route'], 'mio.accounts') || str_contains($item['route'], 'mio.subjects') || str_contains($item['route'], 'mio.schedules') || str_contains($item['route'], 'mio.school') || str_contains($item['route'], 'mio.emergency') || str_contains($item['route'], 'mio.settings') || str_contains($item['route'], 'mio.AddTeacher')
                            ? route($item['route'], ['uid' => session('uid')]) // Pass the uid for these routes
                            : route($item['route']))
                        : $item['url'];
                @endphp
                <li>
                    <a href="{{ $href }}" class="{{ $isActive ? 'active' : '' }}">
                        <i class="{{ $item['icon'] }}"></i>
                        <span class="links_name">{{ $item['label'] }}</span>
                    </a>
                    <span class="tooltip">{{ $item['label'] }}</span>
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
  const closeBtn = document.querySelector("#btn");
  const searchBtn = document.querySelector(".bx-search");

  // Toggle sidebar
  function toggleSidebar() {
    sidebar.classList.toggle("open");
    menuBtnChange();
  }

  // Update sidebar button icon
  function menuBtnChange() {
    if (sidebar.classList.contains("open")) {
      closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
    } else {
      closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");
    }
  }

  // Show/hide tooltips on hover when sidebar is closed
  document.querySelectorAll(".nav-list li").forEach((item) => {
    item.addEventListener("mouseenter", () => {
      if (!sidebar.classList.contains("open")) {
        const tooltip = item.querySelector(".tooltip");
        if (tooltip) {
          tooltip.style.opacity = "1";
          tooltip.style.pointerEvents = "auto";
        }
      }
    });

    item.addEventListener("mouseleave", () => {
      const tooltip = item.querySelector(".tooltip");
      if (tooltip) {
        tooltip.style.opacity = "0";
        tooltip.style.pointerEvents = "none";
      }
    });
  });

  // Event listeners
  if (closeBtn) closeBtn.addEventListener("click", toggleSidebar);
  if (searchBtn) searchBtn.addEventListener("click", toggleSidebar);

  // Optional: Close sidebar on outside click
  document.addEventListener("click", (event) => {
    if (!sidebar.contains(event.target) && !closeBtn.contains(event.target)) {
      sidebar.classList.remove("open");
      menuBtnChange();
    }
  });

</script>


