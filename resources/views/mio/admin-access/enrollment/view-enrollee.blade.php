<section class="home-section">
  <div class="text">View Enrollee</div>
  <div class="teacher-container">
    @include('mio.dashboard.status-message')

    <form action="{{ url('mio/teacher/updateEnrollmentStatus/'.$enrollee['ID']) }}" method="post">
      @csrf
      @method('PUT')

      <!-- HEADER CONTROLS -->
      <div class="table-header">
        <div class="button-group">
          <button type="button" class="btn cancel-btn">
            <a href="{{ url()->previous() }}">Cancel</a>
          </button>
          <button class="btn add-btn">
            <span class="icon">✔</span> Save Changes
          </button>
        </div>
      </div>

      <div class="form-container">
        <!-- Enrollee Info Display -->
        <div class="section-header">Student Enrollment Form</div>
        <div class="section-content">

          <div class="form-group">
            <label>Full Name:</label>
            <input type="text" value="{{ $enrollee['enrollment_form']['first_name'] }} {{ $enrollee['enrollment_form']['last_name'] }}" readonly />
          </div>

          <div class="form-group">
            <label>Gender:</label>
            <input type="text" value="{{ $enrollee['enrollment_form']['gender'] }}" readonly />
          </div>

          <div class="form-group">
            <label>Age:</label>
            <input type="text" value="{{ $enrollee['enrollment_form']['age'] }}" readonly />
          </div>

          <div class="form-group">
            <label>Birthday:</label>
            <input type="text" value="{{ $enrollee['enrollment_form']['birthday'] }}" readonly />
          </div>

          <div class="form-group">
            <label>Disability:</label>
            <input type="text" value="{{ $enrollee['enrollment_form']['disability'] }}" readonly />
          </div>

          <div class="form-group">
            <label>Assistive Devices:</label>
            <input type="text" value="{{ $enrollee['enrollment_form']['assistive_devices'] }}" readonly />
          </div>

          <div class="form-group">
            <label>Address:</label>
            <textarea readonly>{{ $enrollee['enrollment_form']['street'] }}, {{ $enrollee['enrollment_form']['barangay'] }}, {{ $enrollee['enrollment_form']['city'] }}, {{ $enrollee['enrollment_form']['province'] }}, {{ $enrollee['enrollment_form']['zip_code'] }}</textarea>
          </div>

          <div class="form-group">
            <label>Medical History:</label>
            <textarea readonly>{{ $enrollee['enrollment_form']['medical_history'] }}</textarea>
          </div>

          <div class="form-group">
            <label>Health Notes:</label>
            <textarea readonly>{{ $enrollee['enrollment_form']['health_notes'] }}</textarea>
          </div>

          <!-- Add any document preview or links here -->
        </div>

        <!-- Teacher Action Section -->
        <div class="section-header">Teacher Action</div>
        <div class="section-content">
          <div class="form-group wide">
            <label>Teacher Feedback</label>
            <textarea name="teacher_feedback" placeholder="Write your feedback here..." required>{{ $enrollee['Assessment'] ?? '' }}</textarea>
          </div>

          <div class="form-group">
            <label>Enrollment Status</label>
            <select name="enroll_status" required>
              <option value="Pending" {{ $enrollee['enroll_status'] == 'Pending' ? 'selected' : '' }}>Pending</option>
              <option value="Registered" {{ $enrollee['enroll_status'] == 'Registered' ? 'selected' : '' }}>Registered</option>
              <option value="Approved" {{ $enrollee['enroll_status'] == 'Approved' ? 'selected' : '' }}>Approved</option>
              <option value="Rejected" {{ $enrollee['enroll_status'] == 'Rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>
