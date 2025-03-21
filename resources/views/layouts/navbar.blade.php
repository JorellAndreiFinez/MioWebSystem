

<!--    Navigation Bar  -->
<div class="nav-wrapper" >

  <nav class="navbar mainmenu" data-offset-top="1" data-spy="affix" style="background-color: var(--nav-bg-color); border: none;">
  <div class="uppernav cd">
    <div class="item cn"></div>
    <div class="item ph">123-456-7890</div>
  </div>
    <div class="container">
      <div class="navbar-header">
        <button class="navbar-toggle" data-target="#myNavbar" data-toggle="collapse" type="button" >

        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>

        </button><a class="navbar-brand" href="#" style="color: var(--text-white-color);"><span>Philippine Institute for the Deaf</span></a>
      </div>
      <div class="collapse navbar-collapse" id="myNavbar">
        <ul class="nav navbar-nav" >

        </ul>
        <ul class="nav navbar-nav navbar-right" style="color: var(--text-white-color);">
        <li class=""> <!-- put active to class  -->
            <a href="{{ route('landing') }}">Home</a>
          </li>
          <li >
            <a href="{{ route('enroll') }}">Admission</a>
          </li>
          <li>
            <a href="{{ route('about') }}">About</a>

          <li>
            <a href="{{ route(name: 'program') }}">Programs</a>
          </li>
          <li>
            <a href="{{ route('campus')}}">Campus</a>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            PID <span class="caret"></span>
        </a>
          <ul class="dropdown-menu">
                <li><a href="{{ route('news')}}">News</a></li>
                <li><a href="{{ route('events')}}">Events</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="{{ route('mio.login') }}">MIO</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</div>
