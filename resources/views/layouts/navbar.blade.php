

<!--    Navigation Bar  -->
<div class="nav-wrapper">


  <nav class="navbar navbar-default mainmenu" data-offset-top="1" data-spy="affix">
  <div class="uppernav cd">
    <div class="item cn">Company Name</div>
    <div class="item ph">123-456-7890</div>
  </div>
    <div class="container">

      <div class="navbar-header">
        <button class="navbar-toggle" data-target="#myNavbar" data-toggle="collapse" type="button"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button><a class="navbar-brand" href="#"><span>LOGO HERE</span></a>
      </div>
      <div class="collapse navbar-collapse" id="myNavbar">
        <ul class="nav navbar-nav">

        </ul>
        <ul class="nav navbar-nav navbar-right">
        <li class=""> <!-- put active to class  -->
            <a href="{{ route('landing') }}">Home</a>
          </li>
          <li>
            <a href="{{ route('enroll') }}">Enroll</a>
          </li>
          <li>
            <a href="{{ route('about') }}">About</a>
          </li>
          <li>
            <a href="#">Admission</a>
          </li>
          <li>
            <a href="#">Programs</a>
          </li>
          <li>
            <a href="#">Contact</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</div>
