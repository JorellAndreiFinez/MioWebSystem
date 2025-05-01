<section class="home-section">
  <div class="text">Add New Parent</div>

  <div class="teacher-container">
    @include('mio.dashboard.status-message')

    <form action="{{ route('mio.AddParent') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <!-- HEADER CONTROLS -->
      <div class="table-header">
        <div class="search-container" style="background: transparent;"></div>
        <div class="button-group">
          <button type="button" class="btn cancel-btn"><a href="{{ url()->previous() }}">Cancel</a></button>
          <button type="submit" class="btn add-btn">
            <span class="icon">+</span> New Parent
          </button>
        </div>
      </div>

      <div class="form-container">
        <!-- Teacher Information Section -->
        <div class="section-header">Parent Information</div>
        <div class="section-content">
        <div class="form-row">
            <!-- Change category to Father, Mother, or Guardian -->
            <div class="form-group">
              <label><input type="radio" name="category" value="father" required> Father</label>
            </div>
            <div class="form-group">
              <label><input type="radio" name="category" value="mother"> Mother</label>
            </div>
            <div class="form-group">
              <label><input type="radio" name="category" value="guardian"> Guardian</label>
            </div>
          </div>


          <div class="form-group wide">
              <label>Parent ID <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="parentid" id="parentID" required />
           </div>

           <!-- put student id to get some of the student info and reflect in the inputs in per section header with checkbox that say "Same to child", if check put the info of student to the inputs here in parent -->

            <div class="form-row">
                <!-- Add student ID to populate student information -->
            <div class="form-group wide">
                <label>Student ID <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="studentid" id="studentID" required/>
            </div>

            <div class="form-group">
                <br>
                <span id="studentNameDisplay" style="margin-top: 10px; margin-left: 10px; font-weight: bold; color: gray"></span>
                <span id="gradeLevelDisplay" style="margin-left: 10px; font-weight: light; color: gray"></span>
                </div>

            </div>


        </div>

        <!-- Personal Information Section -->
        <div class="section-header">Personal Information</div>
        <!-- CHECKBOX HERE -->
        <div class="section-content">
          <div class="form-row">
            <div class="form-group">
              <label>First Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="first_name" placeholder="First Name"required />
            </div>
            <div class="form-group">
              <label>Last Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="last_name" placeholder="Last Name" required />
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
              <input type="number" name="age" placeholder="Age" required />
            </div>
            <div class="form-group">
              <label>Birthday <span style="color: red; font-weight:700">*</span></label>
              <input type="date" name="birthday" value="2008-04-01" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group wide">
              <label>Street Name, Building, House No. <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="address" placeholder="Street, Building, House No." required />
            </div>
            <div class="form-group wide">
              <label>Barangay <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="barangay" placeholder="Barangay" required />
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
              <input type="text" name="province" placeholder="Province" required />
            </div>
            <div class="form-group wide">
              <label>City <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="city" placeholder="City" required />
            </div>
            <div class="form-group wide">
              <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
              <input type="number" name="zip_code" placeholder="Zip Code" minlength="4" maxlength="4" required />
            </div>
          </div>

          <!-- Checkbox to Copy Information from Student -->
        <div class="form-group checkbox-container" style="display: none;">
        <label><input type="checkbox" id="sameToChild" /> Same as Child's Address</label>
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

// Randomize last 3 digits (000–999)
const randomLastThreeDigits = Math.floor(Math.random() * 1000);

// Format the last 3 digits to always be 3 digits long (e.g., 001, 087, 999)
const lastThreeDigits = String(randomLastThreeDigits).padStart(3, '0');

// Generate the student ID with the current year, week, day, and random last 3 digits
const parentID = `PA${currentYear}${String(currentWeek).padStart(2, '0')}${String(currentDay)}${lastThreeDigits}`;

// Set the value in the input field
document.getElementById('parentID').value = parentID;
</script>

<script>
document.getElementById('studentID').addEventListener('input', function () {
  const studentID = this.value;
  const studentNameDisplay = document.getElementById('studentNameDisplay');
  const gradeLevelDisplay = document.getElementById('gradeLevelDisplay');
  const checkboxContainer = document.querySelector('.form-group.checkbox-container');

  if (studentID && studentID.length > 0) {
    checkboxContainer.style.display = 'block';

    fetch(`/mio/admin/get-student/${studentID}`)
      .then(response => response.json())
      .then(data => {
        if (data) {
          studentNameDisplay.textContent = `${data.first_name} ${data.last_name}`;
          gradeLevelDisplay.textContent = `Grade ${data.grade_level || 'N/A'}`;

          // Store student data globally to use when checkbox is clicked
          window.studentData = data;

          // Autofill only if checkbox is already checked
          if (document.getElementById('sameToChild').checked) {
            autofillAddress(data);
          }
        }
      })
      .catch(error => {
        studentNameDisplay.textContent = '';
        gradeLevelDisplay.textContent = '';
        alert("Student data could not be fetched.");
      });
  } else {
    studentNameDisplay.textContent = '';
    gradeLevelDisplay.textContent = '';
    checkboxContainer.style.display = 'none';
  }
});

document.getElementById('sameToChild').addEventListener('change', function () {
  if (this.checked && window.studentData) {
    autofillAddress(window.studentData);
  }
});

function autofillAddress(data) {
  document.querySelector('input[name="address"]').value = data.address || '';
  document.querySelector('input[name="barangay"]').value = data.barangay || '';
  document.querySelector('select[name="region"]').value = data.region || '';
  document.querySelector('input[name="province"]').value = data.province || '';
  document.querySelector('input[name="city"]').value = data.city || '';
  document.querySelector('input[name="zip_code"]').value = data.zip_code || '';
}




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




