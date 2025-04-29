<section class="home-section">
  <div class="text">Add New Student</div>

  <div class="teacher-container">
    @include('mio.dashboard.status-message')
    <form action="{{ route('mio.AddStudent') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <!-- HEADER CONTROLS -->
      <div class="table-header">
        <div class="search-container" style="background: transparent;"></div>
        <div class="button-group">
          <button type="button" class="btn cancel-btn"><a href="{{ url()->previous() }}">Cancel</a></button>
          <button type="submit" class="btn add-btn">
            <span class="icon">+</span> New Student
          </button>
        </div>
      </div>

      <div class="form-container">
        <!-- Student Information Section -->
        <div class="section-header">Student Information</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-group">
              <label><input type="radio" name="category" value="new" required checked> New</label>
            </div>
            <div class="form-group teacher-category">
              <label><input type="radio" name="category" value="transfer"> Transfer</label>
            </div>
            <div class="form-group teacher-category">
              <label><input type="radio" name="category" value="returning"> Returning</label>
            </div>
            <div class="form-group teacher-category">
              <label><input type="radio" name="category" value="international"> International</label>
            </div>
          </div>
          <div class="form-group wide">
              <label>Student ID <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="studentid" id="studentID" required />
            </div>
        </div>

        <!-- Personal Information Section -->
        <div class="section-header">Personal Information</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-group">
              <label>First Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="first_name" value="Jorell Andrei" required />
            </div>
            <div class="form-group">
              <label>Last Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="last_name" value="Finez" required />
            </div>
            <div class="form-group">
            <label>Gender <span style="color: red; font-weight:700">*</span></label>
            <select name="gender" required>
                <option value="" disabled selected>Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            </div>
            <div class="form-group">
              <label>Age <span style="color: red; font-weight:700">*</span></label>
              <input type="number" name="age" value="17" required />
            </div>
            <div class="form-group">
              <label>Birthday <span style="color: red; font-weight:700">*</span></label>
              <input type="date" name="birthday" value="2008-04-01" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group wide">
              <label>Street Name, Building, House No. <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="address" value="13 Blk Lot 8, Camella Homes, Valenzuela City" required />
            </div>
            <div class="form-group wide">
              <label>Barangay <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="barangay" value="Brgy. Lapuk" required />
            </div>
            <div class="form-group wide">
              <label for="region">Region *</label>
              <select id="region" name="region" required>
                <option value="" disabled selected>Select a Region</option>
                <option value="NCR">National Capital Region (NCR)</option>
                <option value="CAR">Cordillera Administrative Region (CAR)</option>
                <!-- More options here -->
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
              <input type="text" name="contact_number" value="09053622382" required />
            </div>
            <div class="form-group wide">
              <label>Emergency Contact Number <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="emergency_contact" value="09053622382" required />
            </div>
            <div class="form-group">
              <label>Email <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="email" value="jorellandrei23@gmail.com" required />
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
            <input type="text" name="username" id="account_username" required />
            </div>
            <div class="form-group">
            <label>Password <span style="color: red; font-weight:700">*</span></label>
            <input type="text" name="account_password" id="account_password" required />
            </div>

            <div class="form-group">
            <label>Account Status <span style="color: red; font-weight:700">*</span></label>
            <select name="account_status" required>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
            </div>
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
// Get the current date
const currentDate = new Date();

// Get the current year
const currentYear = currentDate.getFullYear();

// Get the current week number (ISO-8601 standard)
const getWeekNumber = (date) => {
  const firstDayOfYear = new Date(date.getFullYear(), 0, 1);
  const days = Math.floor((date - firstDayOfYear) / (24 * 60 * 60 * 1000));
  return Math.ceil((days + 1) / 7);
};
const currentWeek = getWeekNumber(currentDate);

// Get the current day of the week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
const currentDay = currentDate.getDay();

// Assume this is fetched from the database
let lastStudentID = "ST2025171"; // Example, but won't matter since random

// Randomize last 3 digits (000–999)
const randomLastThreeDigits = Math.floor(Math.random() * 1000);

// Format the last 3 digits to always be 3 digits long (e.g., 001, 087, 999)
const lastThreeDigits = String(randomLastThreeDigits).padStart(3, '0');

// Generate the student ID with the current year, week, day, and random last 3 digits
const studentID = `ST${currentYear}${String(currentWeek).padStart(2, '0')}${String(currentDay)}${lastThreeDigits}`;

// Set the value in the input field
document.getElementById('studentID').value = studentID;
</script>

<script>
// Function to update account username and password fields
function updateAccountInfo() {
    const personalEmail = document.querySelector('input[name="email"]').value;
    const personalBirthday = document.querySelector('input[name="birthday"]').value;

    document.getElementById('account_username').value = personalEmail;  // use email as username
    document.getElementById('account_password').value = personalBirthday; // birthday as password
}

// Update fields when the page loads
window.addEventListener('load', updateAccountInfo);

// Also update fields whenever email or birthday inputs are changed
document.querySelector('input[name="email"]').addEventListener('input', updateAccountInfo);
document.querySelector('input[name="birthday"]').addEventListener('input', updateAccountInfo);
</script>

