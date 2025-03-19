<div class="sidebar">
    <div class="logo-details">
        <div class="logo_name">MIO - Admin</div>
        <i class='bx bx-menu' id="btn"></i>
    </div>
    <ul class="nav-list">
        <h3 class="title-label">Menu</h3>

        <li>
            <a href="#" class="{{ request()->routeIs('mio.admin-panel') ? 'active' : '' }}">
                <i class='bx bx-grid-alt'></i>
                <span class="links_name">Dashboard</span>
            </a>
            <span class="tooltip">Dashboard</span>
        </li>

        <li>
            <a href="#" class="{{ request()->routeIs('mio.calendar') ? 'active' : '' }}">
                <i class='bx bxs-graduation'></i>
                <span class="links_name">Teachers</span>
            </a>
            <span class="tooltip">Teachers</span>
        </li>

        <li>
            <a href="#" class="{{ request()->is('inbox') ? 'active' : '' }}">
                <i class='bx bx-user-voice'></i>
                <span class="links_name">Students</span>
            </a>
            <span class="tooltip">Students</span>
        </li>

        <li>
            <a href="#" class="{{ request()->is('inbox') ? 'active' : '' }}">
                <i class='bx bxs-user-detail'></i>
                <span class="links_name">Accounts</span>
            </a>
            <span class="tooltip">Accounts</span>
        </li>

        <li>
            <a href="#" class="{{ request()->is('inbox') ? 'active' : '' }}">
                <i class='bx bx-book-open'></i>
                <span class="links_name">Subjects</span>
            </a>
            <span class="tooltip">Subjects</span>
        </li>

        <li>
            <a href="#" class="{{ request()->is('inbox') ? 'active' : '' }}">
                <i class='bx bx-calendar'></i>
                <span class="links_name">Schedule</span>
            </a>
            <span class="tooltip">Schedule</span>
        </li>

        <li>
            <a href="#" class="{{ request()->is('inbox') ? 'active' : '' }}">
                <i class='bx bx-building'></i>
                <span class="links_name">School</span>
            </a>
            <span class="tooltip">School</span>
        </li>



        <br>
        <h3 class="title-label">Other</h3>

        <li>
            <a href="#" class="{{ request()->is('profile') ? 'active' : '' }}">
                <i class='bx bxs-error'></i>
                <span class="links_name">Emergency Alert</span>
            </a>
            <span class="tooltip">Profile</span>
        </li>

        <li>
            <a href="#" class="{{ request()->is('help') ? 'active' : '' }}">
                <i class='bx bxs-chart'></i>
                <span class="links_name">Data Analytics</span>
            </a>
            <span class="tooltip">Help & Report</span>
        </li>

        <li>
            <a href="#" class="{{ request()->is('settings') ? 'active' : '' }}">
                <i class='bx bx-cog'></i>
                <span class="links_name">Setting</span>
            </a>
            <span class="tooltip">Setting</span>
        </li>

        <li class="profile">
            <div class="profile-details">
                <div class="name_job">
                    <div class="name">John Doe</div>
                    <div class="job">Role</div>
                </div>
            </div>
            <a href="{{ route('mio.login') }}">
                <i class='bx bx-log-out' id="log_out"></i>
            </a>
        </li>
    </ul>
</div>



  <script>
  let sidebar = document.querySelector(".sidebar");
  let closeBtn = document.querySelector("#btn");
  let searchBtn = document.querySelector(".bx-search");

  closeBtn.addEventListener("click", ()=>{
    sidebar.classList.toggle("open");
    menuBtnChange();//calling the function(optional)
  });

  searchBtn.addEventListener("click", ()=>{ // Sidebar open when you click on the search iocn
    sidebar.classList.toggle("open");
    menuBtnChange(); //calling the function(optional)
  });

  // following are the code to change sidebar button(optional)
  function menuBtnChange() {
   if(sidebar.classList.contains("open")){
     closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");//replacing the iocns class
   }else {
     closeBtn.classList.replace("bx-menu-alt-right","bx-menu");//replacing the iocns class
   }
  }
  </script>
