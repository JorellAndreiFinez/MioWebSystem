<!-- Navigation Bar -->
<div class="nav-wrapper">

  <nav class="navbar mainmenu" data-offset-top="1" data-spy="affix" style="background-color: var(--nav-bg-color); border: none;">
    
    <!-- Top Contact Bar -->
    <!-- Top Contact Bar with Ionicons -->
    <div class="uppernav">
      <div class="uppernav-right">
        <div class="contact-left">
          <i class="ion-ios-telephone"></i>
          <span>(02) 8703 1819</span>
        </div>
        <div class="contact-right">
          <a href="mailto:rpbautista.semfi@gmail.com"><i class="ion-email"></i></a>
          <a href="https://www.facebook.com/PhilippineInstitutefortheDeaf" target="_blank"><i class="ion-social-facebook"></i></a>
          <a href="https://www.instagram.com/pidmanila17/" target="_blank"><i class="ion-social-instagram"></i></a>
          <a href="https://x.com/semfipid" target="_blank"><i class="ion-social-twitter"></i></a>
          <a href="https://maps.app.goo.gl/pRCiv3pxFWWhRZwX7" target="_blank"><i class="ion-location"></i></a>
        </div>
      </div>
    </div>


    <!-- Main Navigation -->
    <div class="container">
      <div class="navbar-header">
        <button class="navbar-toggle" data-target="#myNavbar" data-toggle="collapse" type="button">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#" style="color: var(--text-white-color);">
          <span>Philippine Institute for the Deaf</span>
        </a>
      </div>

      <div class="collapse navbar-collapse" id="myNavbar">
        <ul class="nav navbar-nav"></ul>
        <ul class="nav navbar-nav navbar-right" style="color: var(--text-white-color);">
          <li><a href="{{ route('landing') }}">Home</a></li>
          <li><a href="{{ route('enroll') }}">Admission</a></li>
          <li><a href="{{ route('about') }}">About</a></li>
          <li><a href="{{ route('program') }}">Programs</a></li>
          <li><a href="{{ route('campus') }}">Campus</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">PID <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="{{ route('news') }}">News</a></li>
              <li><a href="{{ route('events') }}">Events</a></li>
              <li role="separator" class="divider"></li>
              <li><a href="{{ route('mio.login') }}">MIO</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>

  </nav>
</div>
