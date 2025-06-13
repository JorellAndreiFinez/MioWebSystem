
<!-- FIRST PART -->

<div class="info-section">
    <div class="info-content">
        <div class="info-image">
            <img src="{{ asset('storage/assets/images/1.3 home-welcome-to-pid.png') }}" alt="Students">
            <!-- <div class="blue-overlay"></div> -->
        </div>
        <div class="info-text">
            <span class="tagline">WELCOME TO PID</span>
            <h1>Start Your Journey with Us! </h1>
            <p>The Philippine Institute for the Deaf (PID) exists for the purpose of insuring that these unique resources are readily available to those young children who need them. With the combined support of the community, young deaf children in the Philippines continue to have the opportunity to listen, learn, and talk.</p>
            <a href="{{ route('about') }}" class="about-btn">ABOUT US</a>

        </div>
    </div>
</div>


<!-- SECOND PART -->

<div class="second-container">
  <h5>WHY CHOOSE US</h5>
  <h2>Why PID is a Good Choice?</h2>
  <div class="reasons">
    <!-- Reason 1 -->
    <div class="reason">
      <div class="icon">
        <img src="{{ asset('storage/assets/images/1.4.1 home-icon-specialized-education.png') }}" alt="Specialized Education">
      </div>
      <h3>Specialized Education</h3>
      <p>A proven curriculum that develops listening, speaking, and academic skills.</p>
    </div>

    <!-- Reason 2 -->
    <div class="reason">
      <div class="icon">
        <img src="{{ asset('storage/assets/images/1.4.2 home-icon-expert-&-caring-educators.png') }}" alt="Expert & Caring Educators">
      </div>
      <h3>Expert & Caring Educators</h3>
      <p>Trained teachers providing personalized guidance for every child.</p>
    </div>

    <!-- Reason 3 -->
    <div class="reason">
      <div class="icon">
        <img src="{{ asset('storage/assets/images/1.4.3 home-icon-inclusive-community.png') }}" alt="Inclusive Community">
      </div>
      <h3>Inclusive Community</h3>
      <p>A welcoming space where students, families, and educators thrive together.</p>
    </div>
  </div>
</div>

<!-- THIRD PART -->

<div class="third-container">
  <div class="third-div">
    <!-- Left Side - Image and Title -->
    <div class="guide-left">
      <div class="guide-title">
        <span class="process-label">PROCESS</span>
        <h2>Simple Guide to Join Our School</h2>
        <img src="{{ asset('storage/assets/images/1.5 home-simple-guide.png') }}" alt="Classroom" class="guide-image" />
      </div>
    </div>

    <!-- Right Side - Steps -->
    <div class="guide-right">
      <div class="step">
        <div class="step-icon">
        <img src="{{ asset('storage/assets/images/icons/enrollment-1.png') }}" alt="Apply for Enrollment">
        </div>
        <div class="step-content">
          <h3>Apply for Enrollment</h3>
          <p>Submit your application and required documents.</p>
        </div>
      </div>

      <div class="step">
        <div class="step-icon">
          <img src="{{ asset('storage/assets/images/icons/interview-1.png') }}" alt="Student Interview">
        </div>
        <div class="step-content">
          <h3>Student Interview</h3>
          <p>Attend a physical assessment and evaluation.</p>
        </div>
      </div>

      <div class="step">
        <div class="step-icon">
          <img src="{{ asset('storage/assets/images/icons/payment-1.png') }}" alt="Pay Your Balance">
        </div>
        <div class="step-content">
          <h3>Pay Your Balance</h3>
          <p>Complete your payment to secure enrollment.</p>
        </div>
      </div>

      <div class="step">
        <div class="step-icon">
          <img src="{{ asset('storage/assets/images/icons/waiting-1.png') }}" alt="Wait for First Day">
        </div>
        <div class="step-content">
          <h3>Ready for School</h3>
          <p>Get ready to start your learning journey!</p>
        </div>
      </div>

      <a href="{{ route('enroll') }}" class="guide-btn">VIEW DETAILS</a>
    </div>
  </div>
</div>

<!-- FOURTH PART -->

<div class="fourth-container">
  <div class="fourth-div">
    <!-- Left Side - Text Content -->
    <div class="achievements-text">
      <span class="achievements-label">COMMUNITY UPDATES</span>
      <h2>Celebrating Our Achievements</h2>
      <p>
        Discover our latest milestones and successes! From student achievements to special events, we celebrate growth, dedication, and excellence in our community. Stay updated and celebrate with us!
      </p>
      <a href="{{ route('news') }}" class="updates-btn">GO TO NEWS</a>
    </div>

    <!-- Right Side - Image -->
    <div class="achievements-image">
    <img src="{{ asset('storage/assets/images/1.6 home-community-updates.png') }}" alt="Students Talking" />
    </div>
  </div>
</div>

<!-- FIFTH PART -->

<section class="fifth-container">
  <div class="programs-header">
    <span class="programs-label">SERVICES</span>
    <h2>Explore Our Programs</h2>
  </div>

  <div class="programs-container">
    <!-- Program 1 -->
    <div class="program-card">
      <div class="program-image">
        <img src="{{ asset('storage/assets/images/1.7.1 home-card-k-12-basic-education.png') }}" alt="K-12 Basic Education" />
      </div>
      <h3>K-12 Basic<br>Education</h3>
    </div>

    <!-- Program 2 -->
    <div class="program-card">
      <div class="program-image">
        <img src="{{ asset('storage/assets/images/1.7.2 home-card-home-economics.png') }}" alt="Home Economics" />
      </div>
      <h3>Home<br>Economics</h3>
    </div>

    <!-- Program 3 -->
    <div class="program-card">
      <div class="program-image">
        <img src="{{ asset('storage/assets/images/1.7.3 home-card-inustrial-arts.png') }}" alt="Industrial Arts" />
      </div>
      <h3>Industrial<br>Arts</h3>
    </div>

    <!-- Program 4 -->
    <div class="program-card">
      <div class="program-image">
        <img src="{{ asset('storage/assets/images/1.7.4 home-card-culinary-skills-development.png') }}" alt="Culinary Skills Development" />
      </div>
      <h3>Culinary Skills<br>Development</h3>
    </div>
  </div>
</section>

<!-- <script>
      window.onload = function() {
        document.body.style.visibility = "visible";
        document.body.style.opacity = "1";
    };
</script> -->

