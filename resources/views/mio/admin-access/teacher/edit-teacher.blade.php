<section class="home-section">
<div class="text">Edit Teacher</div>
<div class="teacher-container">
@include('mio.dashboard.status-message')

 <form action="{{ url('mio/admin1/UpdateTeacher/'.$editdata['teacherid']) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <!-- HEADER CONTROLS -->
    <div class="table-header">
            <div class="search-container" style="background: transparent;">
            </div>

            <div class="button-group">
            <button type="button" class="btn cancel-btn"><a href="{{ url()->previous() }}">Cancel</a>
            </button>
            <button class="btn add-btn">
                <span class="icon">✔</span> Save Changes
            </button>
            </div>

        </div>
        <div class="form-container">
        <!-- Student Information Section -->
        <div class="section-header">Teacher Information</div>
        <div class="section-content">
            <div class="form-row">
                <div class="form-group">
                    <label><input type="radio" name="category" value="new"
                        @if (isset($editdata['category']) && $editdata['category'] == 'new') checked @endif> New</label>
                </div>
                <div class="form-group teacher-category">
                    <label><input type="radio" name="category" value="full-time"
                        @if (isset($editdata['category']) && $editdata['category'] == 'full-time') checked @endif> Full-Time</label>
                </div>
                <div class="form-group teacher-category">
                    <label><input type="radio" name="category" value="part-time"
                        @if (isset($editdata['category']) && $editdata['category'] == 'part-time') checked @endif> Part-Time</label>
                </div>
                <div class="form-group teacher-category">
                    <label><input type="radio" name="category" value="intern"
                        @if (isset($editdata['category']) && $editdata['category'] == 'intern') checked @endif> Intern</label>
                </div>
            </div>
            <div class="form-group wide">
                <label>Student ID <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="teacherid" id="teacherid" value="{{ $editdata['teacherid'] }}" required />
            </div>
        </div>


        <!-- Personal Information Section -->
        <div class="section-header">Personal Information</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-group">
              <label>First Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="first_name" value="{{ $editdata['fname'] }}" required />
            </div>
            <div class="form-group">
              <label>Last Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="last_name" value="{{ $editdata['lname'] }}" required />
            </div>
            <div class="form-group">
            <label>Gender <span style="color: red; font-weight:700">*</span></label>
            <select name="gender" required>
                <option value="" disabled {{ !isset($editdata['gender']) ? 'selected' : '' }}>Select Gender</option>
                <option value="Male" {{ isset($editdata['gender']) && $editdata['gender'] == 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ isset($editdata['gender']) && $editdata['gender'] == 'Female' ? 'selected' : '' }}>Female</option>
                <option value="Other" {{ isset($editdata['gender']) && $editdata['gender'] == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

            <div class="form-group">
              <label>Age <span style="color: red; font-weight:700">*</span></label>
              <input type="number" name="age" value="{{ $editdata['age'] }}" required />
            </div>
            <div class="form-group">
              <label>Birthday <span style="color: red; font-weight:700">*</span></label>
              <input type="date" name="birthday" value="{{ $editdata['bday'] }}" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group wide">
              <label>Street Name, Building, House No. <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="address" value="13 Blk Lot 8, Camella Homes, Valenzuela City" required />
            </div>
            <div class="form-group wide">
              <label>Barangay <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="barangay" value="{{ $editdata['barangay'] }}" required />
            </div>
            <div class="form-group wide">
            <label for="region">Region *</label>
            <select id="region" name="region" required>
                <option value="" disabled selected>Select a Region</option>
                <option value="NCR" @if (isset($editdata['region']) && $editdata['region'] == 'NCR') selected @endif>
                    National Capital Region (NCR)
                </option>
                <option value="CAR" @if (isset($editdata['region']) && $editdata['region'] == 'CAR') selected @endif>
                    Cordillera Administrative Region (CAR)
                </option>
                <!-- Add more options as needed -->
            </select>
        </div>

          </div>

          <div class="form-row">
            <div class="form-group wide">
              <label>Province <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="province" value="Metro Manila" required />
            </div>
            <div class="form-group wide">
              <label>City <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="city" value="Valenzuela City" required />
            </div>
            <div class="form-group wide">
              <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
              <input type="number" name="zip_code" value="3333" minlength="4" maxlength="4" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="contact_number" value="{{ $editdata['contact_number'] }}" required />
            </div>
            <div class="form-group wide">
              <label>Emergency Contact Number <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="emergency_contact" value="{{ $editdata['emergency_contact'] }}" required />
            </div>
            <div class="form-group">
              <label>Email <span style="color: red; font-weight:700">*</span></label>
            <input type="text" name="email" value="{{ $editdata['email'] }}" required />
            </div>
          </div>
        </div>

        <!-- Academic Information Section -->
        <div class="section-header">Academic Information</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-group wide">
              <label>Previous School Attended <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="previous_school" value="Blah blah High school" required />
            </div>
            <div class="form-group">
              <label>Grade Level <span style="color: red; font-weight:700">*</span></label>
              <input type="number" name="grade_level" value="10" required />
            </div>
          </div>
        </div>

        <!-- Account Information Section -->
        <div class="section-header">Account Information</div>
        <div class="section-content">
            <div class="form-row">
                <div class="form-group">
                    <label>Username <span style="color: red; font-weight:700">*</span></label>
                    <input type="text" name="username" id="account_username" value="{{ $editdata['username'] ?? '' }}" required readonly/>
                </div>
                <div class="form-group">
                <label for="account_password">
                    Password
                </label>
                <div style="position: relative;">
                    <input
                        type="password"
                        name="account_password"
                        id="account_password"
                        placeholder="Enter new password if changing"
                        class="form-control"
                    />
                    <button
                        type="button"
                        onclick="togglePasswordVisibility()"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none;"
                    >
                        <i class="fa fa-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>
            <div class="form-group">
                    <label>Account Status <span style="color: red; font-weight:700">*</span></label>
                    <select name="account_status" required>
                        <option value="active" @if (isset($editdata['account_status']) && $editdata['account_status'] == 'active') selected @endif>Active</option>
                        <option value="inactive" @if (isset($editdata['account_status']) && $editdata['account_status'] == 'inactive') selected @endif>Inactive</option>
                    </select>
                </div>
        </div>


        <!-- Schedule Section -->
        <div class="section-header">Schedule</div>
        <div class="section-content" id="schedule-section">
          <div class="form-row" id="schedule-container">
            <div class="form-group">
              <label>Schedule ID<span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="schedule[]" placeholder="Schedule ID" required />
            </div>
          </div>
          <button type="button" onclick="addScheduleField()" class="add-btn">Add More</button>
        </div>

      </div>
 </form>
</div>

</section>

<script>
    let scheduleCount = 0;

function addScheduleField() {
  const section = document.getElementById("schedule-section");
  const inputsPerRow = 4;

  // Find all current rows
  let currentRows = section.getElementsByClassName("form-row");

  // Check the last row
  let lastRow = currentRows[currentRows.length - 1];

  // If no row exists or last row has 4 children, create a new row
  if (!lastRow || lastRow.children.length >= inputsPerRow) {
    lastRow = document.createElement("div");
    lastRow.className = "form-row";
    section.insertBefore(lastRow, section.querySelector(".add-btn")); // insert before Add button
  }

  // Create the form-group
  const formGroup = document.createElement("div");
  formGroup.className = "form-group";
  formGroup.style.flex = "1"; // Responsive width

  // Create label and input
  const label = document.createElement("label");
  label.innerHTML = `Schedule ID <span style="color: red; font-weight:700">*</span>`;

  const input = document.createElement("input");
  input.type = "text";
  input.name = "schedule[]";
  input.placeholder = "Schedule ID";

  // Append label and input to formGroup
  formGroup.appendChild(label);
  formGroup.appendChild(input);

  // Append formGroup to the lastRow
  lastRow.appendChild(formGroup);
}

</script>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('account_password');
    const icon = document.getElementById('eye-icon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<script>
// Function to update account username and password fields
function updateAccountInfo() {
    const personalEmail = document.querySelector('input[name="email"]').value;
    const personalBirthday = document.querySelector('input[name="birthday"]').value;

    document.getElementById('account_username').value = personalEmail;  // use email as username
}

// Update fields when the page loads
window.addEventListener('load', updateAccountInfo);

// Also update fields whenever email or birthday inputs are changed
document.querySelector('input[name="email"]').addEventListener('input', updateAccountInfo);
</script>
