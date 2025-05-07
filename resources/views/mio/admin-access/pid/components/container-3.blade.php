<div class="third-container">
  <div class="third-div">
    <!-- Left Side - Image and Title -->
    <div class="guide-left">
      <img src="{{ asset('storage/assets/images/1.1.2 home-slider.png') }}" alt="Classroom" class="guide-image" />
      <div class="guide-title">
        <span class="process-label">PROCESS</span>
        <h2>Simple Guide to<br>Join Our School</h2>
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
          <h3>Wait for First Day</h3>
          <p>Get ready to start your learning journey!</p>
        </div>
      </div>

      <a href="{{ route('enroll') }}" class="guide-btn">VIEW DETAILS</a>
    </div>
  </div>
</div>
