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
        <button type="button" class="btn cancel-btn" onclick="window.history.back()">Cancel</button>
          <button type="submit" class="btn add-btn">
            <span class="icon">+</span> New Student
          </button>
        </div>
      </div>

      <div class="form-container">
        <!-- Student Information Section -->
        <div class="section-header">Student Information</div>
        <div class="section-content">
        <label>Student Category<span style="color: red; font-weight:700; ">*</span></label>

          <div class="form-row" style="margin-left: 1rem;">
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
          <hr>
          <div class="form-group wide">
              <label>Student ID <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="studentid" id="studentID" required />
            </div>
            <div class="form-group">
                <label for="section_id">Section</label>
                <select name="section_id" id="section_id" required>
                    <option value="">Select Section</option>
                    @foreach ($sections as $sect)
                        <option value="{{ $sect['sectionid'] }}">{{ $sect['section_name'] }}</option>
                    @endforeach
                        <option value="none">None</option>
                </select>
            </div>
        </div>

        <!-- Personal Information Section -->
        <div class="section-header">Personal Information</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-group">
              <label>First Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="first_name" placeholder="First Name" required />
            </div>
            <div class="form-group">
              <label>Last Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="last_name" placeholder="Last Name"  required />
            </div>
            </div>

            <hr>

            <div class="form-row">

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
            <input type="number" id="age" name="age" placeholder="Enter Age" required min="1" max="100"/>
            </div>

            <div class="form-group">
            <label>Birthday <span style="color: red; font-weight:700;">*</span></label>
            <input
                type="date"
                id="birthday"
                name="birthday"
                required
                min="1900-01-01"
                max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                />
          </div>
          <div class="form-group">
              <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="contact_number" placeholder="Contact Number" required />
            </div>
            </div>

            <hr>

          <div class="form-row">

             <div class="form-group wide">
              <label for="region">Region <span style="color: red; font-weight:700">*</span></label>
              <select id="region" name="region" required>
                    <option value="" disabled selected>Select Region</option>
                </select>
            </div>

            <div class="form-group wide">
              <label>Province <span style="color: red; font-weight:700">*</span></label>
              <select id="province" name="province" required disabled>
              <option value="" disabled selected>Select Province</option>
            </select>
            </div>

            <div class="form-group wide">
              <label>City/ Municipality <span style="color: red; font-weight:700">*</span></label>
               <select id="city" name="city" required disabled>
                <option value="" disabled selected>Select City/Municipality</option>
              </select>
            </div>

            <div class="form-group wide">
              <label>Barangay <span style="color: red; font-weight:700">*</span> </label>
               <select id="barangay" name="barangay"  required disabled>
                <option value="" disabled selected>Select Barangay</option>
            </select>
            </div>

          </div>

          <div class="form-row">

          <div class="form-group wide">
              <label>Building/House No., Street <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="address" placeholder="Home Address" required />
            </div>

            <div class="form-group">
              <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
              <input type="number" name="zip_code" placeholder="Zip Code" minlength="4" maxlength="4" required />
            </div>
          </div>

          <hr>

          <div class="form-row">

            <div class="form-group">
              <label>Email <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="email" placeholder="Email Address" required />
            </div>
          </div>
        </div>

        <div class="section-header">Parent/Guardian Information</div>
                        <div class="section-content">

                         <div class="form-row">
                            <div class="form-group wide">
                                <label>First Name <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="parent_firstname" placeholder="First Name" required value/>
                            </div>

                            <div class="form-group wide">
                                <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="parent_lastname" placeholder="Last Name" required />
                            </div>
                        </div>

                        <hr>
                              <div class="form-row">
                                <div class="form-group">
                                    <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="emergency_contact" placeholder="Emergency Number"  required />
                                </div>
                                <div class="form-group">
                                <label>Parent Role <span style="color: red; font-weight:700">*</span></label>
                                <select name="parent_role" required>
                                    <option value="" disabled selected>Select role</option>
                                    <option value="father">Father</option>
                                    <option value="mother">Mother</option>
                                    <option value="guardian">Guardian</option>
                                </select>
                            </div>
                            </div>

                        </div>

        <div class="section-header">Academic Information</div>
                        <div class="section-content">
                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Previous School Attended <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="previous_school" placeholder="Previous School" required />
                                </div>
                                <div class="form-group">
                                    <label>Previous Grade Level <span style="color: red; font-weight:700">*</span></label>
                                    <input type="number" name="previous_grade_level" placeholder="Previous Grade Level" required />
                                </div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label>What are you enrolling in PID? <span style="color: red; font-weight:700">*</span></label>
                                <select name="enrollment_grade" id="enrollment_grade" required onchange="handleGradeChange()">
                                    <option value="" disabled selected>Select one</option>
                                    <option value="kinder">Kinder</option>
                                    <option value="elementary">Elementary</option>
                                    <option value="junior-highschool">Junior High School</option>
                                    <option value="senior-highschool">Senior High School</option>
                                    <option value="one-on-one-therapy">One-on-One Therapy</option>
                                </select>
                            </div>

                            <!-- Grade Level Selector (Initially Hidden) -->
                            <div class="form-group" id="grade_level_group" style="display: none;">
                                <label>Select Grade Level <span style="color: red; font-weight:700">*</span></label>
                                <select name="grade_level" id="grade_level">
                                    <option value="" disabled selected>Select grade level</option>
                                </select>
                            </div>

                            <!-- Senior High School Strand Selector (Initially Hidden) -->
                            <div class="form-group" id="strand_group" style="display: none;">
                                <label>Select Strand <span style="color: red; font-weight:700">*</span></label>
                                <select name="strand">
                                    <option value="" disabled selected>Select strand</option>
                                    <option value="agri-fishery">Agri-Fishery Arts</option>
                                    <option value="home-economics">Home Economics</option>
                                    <option value="industrial-arts">Industrial Arts</option>
                                    <option value="ict">Information, Communications, & Technology</option>
                                    <option value="entrepreneurship">Entrepreneurship & Financial Management</option>
                                    <option value="culinary">Culinary Skills Development</option>
                                    <option value="fashion-beauty">Fashion Beauty Skills</option>
                                </select>
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

        <div class="section-header">Schedule (For One-on-one Therapy)</div>
        <div class="section-content" id="schedule-section">
          <div class="form-row" id="schedule-container">
            <div class="form-group">
              <label>Schedule ID</label>
              <input type="text" name="schedule[]" placeholder="Schedule ID" />
            </div>
          </div>
          <button type="button" onclick="addScheduleField()" class="add-btn">Add More</button>
        </div>

        </div>

      </div>
    </form>
  </div>
</section>

<!------------ SCRIPTS ------------>
<!-- BDAY -->
 <script>
  const ageInput = document.getElementById('age');
  const birthdayInput = document.getElementById('birthday');

  // Recalculate age when birthday is changed
  birthdayInput.addEventListener('change', function () {
    const birthDate = new Date(this.value);
    const today = new Date();

    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();

    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
      age--;
    }

    if (age > 0) {
      ageInput.value = age;
    } else {
      ageInput.value = '';
      alert('Invalid birthdate. Age must be at least 1.');
    }
  });

  // Block negative or zero manually entered age
  ageInput.addEventListener('input', function () {
    if (parseInt(this.value) <= 0 || isNaN(this.value)) {
      alert('Age must be a positive number.');
      this.value = '';
    }
  });

  // Optional: Prevent paste of invalid values
  ageInput.addEventListener('paste', function (e) {
    const pasted = (e.clipboardData || window.clipboardData).getData('text');
    if (parseInt(pasted) <= 0 || isNaN(pasted)) {
      e.preventDefault();
      alert('Only positive numbers are allowed.');
    }
  });
</script>

<!-- PH ADDRESS -->
<script>
const apiBase = "https://psgc.gitlab.io/api";

const regionNameMap = {
  "010000000": "Region I - Ilocos Region",
  "020000000": "Region II - Cagayan Valley",
  "030000000": "Region III - Central Luzon",
  "040000000": "Region IV-A - CALABARZON",
  "170000000": "MIMAROPA Region",
  "050000000": "Region V - Bicol Region",
  "060000000": "Region VI - Western Visayas",
  "070000000": "Region VII - Central Visayas",
  "080000000": "Region VIII - Eastern Visayas",
  "090000000": "Region IX - Zamboanga Peninsula",
  "100000000": "Region X - Northern Mindanao",
  "110000000": "Region XI - Davao Region",
  "120000000": "Region XII - SOCCSKSARGEN",
  "160000000": "CARAGA",
  "140000000": "CAR - Cordillera Administrative Region",
  "150000000": "BARMM - Bangsamoro Autonomous Region",
  "130000000": "NCR - National Capital Region"
};

async function fetchData(url) {
  const res = await fetch(url);
  return res.json();
}

function populateSelect(selectEl, data, placeholder, customMap = null) {
  selectEl.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
  data.forEach(item => {
    const option = document.createElement("option");
    option.value = item.code;
    option.text = customMap ? customMap[item.code] || item.name : item.name;
    selectEl.appendChild(option);
  });
  selectEl.disabled = false;
}

document.addEventListener('DOMContentLoaded', async () => {
  const regionSelect = document.getElementById("region");
  const provinceSelect = document.getElementById("province");
  const citySelect = document.getElementById("city");
  const barangaySelect = document.getElementById("barangay");

  const regions = await fetchData(`${apiBase}/regions/`);
  populateSelect(regionSelect, regions, "Select Region", regionNameMap);

  regionSelect.addEventListener("change", async () => {
    provinceSelect.innerHTML = '';
    citySelect.innerHTML = '';
    barangaySelect.innerHTML = '';
    provinceSelect.disabled = citySelect.disabled = barangaySelect.disabled = true;

    const selectedRegion = regionSelect.value;

    const provinces = await fetchData(`${apiBase}/regions/${selectedRegion}/provinces/`);

    if (provinces.length === 0) {
        // Region has no provinces: disable province dropdown but keep it visible
        provinceSelect.innerHTML = `<option value="" disabled selected>Not Applicable</option>`;
        provinceSelect.disabled = true;

        // Load cities/municipalities under the region
        const cities = await fetchData(`${apiBase}/regions/${selectedRegion}/cities-municipalities/`);
        populateSelect(citySelect, cities, "Select City/Municipality");
    } else {
        // Region has provinces
        populateSelect(provinceSelect, provinces, "Select Province");
        provinceSelect.disabled = false;
    }
    });


  provinceSelect.addEventListener("change", async () => {
    citySelect.innerHTML = '';
    barangaySelect.innerHTML = '';
    citySelect.disabled = barangaySelect.disabled = true;

    const provinceCode = provinceSelect.value;
    const cities = await fetchData(`${apiBase}/provinces/${provinceCode}/cities-municipalities/`);
    populateSelect(citySelect, cities, "Select City/Municipality");
  });

  citySelect.addEventListener("change", async () => {
    barangaySelect.innerHTML = '';
    barangaySelect.disabled = true;

    const barangays = await fetchData(`${apiBase}/cities-municipalities/${citySelect.value}/barangays/`);
    populateSelect(barangaySelect, barangays, "Select Barangay");
  });
});
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

<!-- ACADEMICS DYNAMIC INPUT -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Trigger update on page load
        handleGradeChange();
    });

    function handleGradeChange() {
        const enrollmentGrade = document.getElementById('enrollment_grade').value;
        const gradeLevelGroup = document.getElementById('grade_level_group');
        const gradeLevelSelect = document.getElementById('grade_level');
        const strandGroup = document.getElementById('strand_group');

        // Saved value from backend (Blade will render these)
        const savedGradeLevel = "{{ $form['grade_level'] ?? '' }}";

        // Reset
        gradeLevelSelect.innerHTML = '<option value="" disabled>Select grade level</option>';
        gradeLevelGroup.style.display = 'none';
        strandGroup.style.display = 'none';

        if (enrollmentGrade === 'elementary') {
            gradeLevelGroup.style.display = 'block';
            for (let i = 1; i <= 6; i++) {
                gradeLevelSelect.innerHTML += `<option value="${i}" ${savedGradeLevel == i ? 'selected' : ''}>Grade ${i}</option>`;
            }
        } else if (enrollmentGrade === 'junior-highschool') {
            gradeLevelGroup.style.display = 'block';
            for (let i = 7; i <= 10; i++) {
                gradeLevelSelect.innerHTML += `<option value="${i}" ${savedGradeLevel == i ? 'selected' : ''}>Grade ${i}</option>`;
            }
        } else if (enrollmentGrade === 'senior-highschool') {
            gradeLevelGroup.style.display = 'block';
            strandGroup.style.display = 'block';
            for (let i = 11; i <= 12; i++) {
                gradeLevelSelect.innerHTML += `<option value="${i}" ${savedGradeLevel == i ? 'selected' : ''}>Grade ${i}</option>`;
            }
        }
    }
</script>

<!-- FOR TESTING -->

<script>
  function fillDummyData() {
    // Student Category (radio)
    document.querySelector('input[name="category"][value="new"]').checked = true;

    // Student ID

    // Section select
    const sectionSelect = document.getElementById('section_id');
    if(sectionSelect){
      // Select first non-empty option or 'none'
      sectionSelect.value = sectionSelect.options[1] ? sectionSelect.options[1].value : 'none';
    }

    // Personal Information
    document.querySelector('input[name="first_name"]').value = "John";
    document.querySelector('input[name="last_name"]').value = "Doe";

    // Gender select
    const genderSelect = document.querySelector('select[name="gender"]');
    if(genderSelect) genderSelect.value = "Male";

    // Age
    document.getElementById('age').value = 16;

    // Birthday
    document.getElementById('birthday').value = "2007-05-15";

    // Contact Number
    document.querySelector('input[name="contact_number"]').value = "09171234567";

    // Region/Province/City/Barangay selects
    // For demo, just enable and select first options (assuming options exist)
    function setSelectValue(id) {
      let sel = document.getElementById(id);
      if(sel && sel.options.length > 1){
        sel.disabled = false;
        sel.value = sel.options[1].value; // pick second option (first after placeholder)
      }
    }
    setSelectValue('region');
    setSelectValue('province');
    setSelectValue('city');
    setSelectValue('barangay');

    // Address and Zip
    document.querySelector('input[name="address"]').value = "1234 Elm Street";
    document.querySelector('input[name="zip_code"]').value = "1234";

    // Email
    document.querySelector('input[name="email"]').value = "john.doe@example.com";

    // Parent/Guardian
    document.querySelector('input[name="parent_firstname"]').value = "Jane";
    document.querySelector('input[name="parent_lastname"]').value = "Doe";
    document.querySelector('input[name="emergency_contact"]').value = "09987654321";

    // Parent Role select
    const parentRoleSelect = document.querySelector('select[name="parent_role"]');
    if(parentRoleSelect) parentRoleSelect.value = "mother";

    // Academic Information
    document.querySelector('input[name="previous_school"]').value = "Springfield Elementary";
    document.querySelector('input[name="previous_grade_level"]').value = 10;

    // Enrollment Grade select
    const enrollGradeSelect = document.getElementById('enrollment_grade');
    if(enrollGradeSelect) enrollGradeSelect.value = "junior-highschool";

    // Show grade_level selector and populate it for junior highschool (example)
    const gradeLevelGroup = document.getElementById('grade_level_group');
    const gradeLevelSelect = document.getElementById('grade_level');
    if(gradeLevelGroup && gradeLevelSelect){
      gradeLevelGroup.style.display = "block";
      gradeLevelSelect.innerHTML = `
        <option value="" disabled>Select grade level</option>
        <option value="7">Grade 7</option>
        <option value="8" selected>Grade 8</option>
        <option value="9">Grade 9</option>
        <option value="10">Grade 10</option>
      `;
      gradeLevelSelect.value = "8";
    }

    // Hide strand group since it's not senior high school
    const strandGroup = document.getElementById('strand_group');
    if(strandGroup) strandGroup.style.display = "none";

    // Account Information
    document.getElementById('account_username').value = "johndoe16";
    document.getElementById('account_password').value = "password123";
    const accStatusSelect = document.querySelector('select[name="account_status"]');
    if(accStatusSelect) accStatusSelect.value = "active";

    // Schedule Section (leave empty or add one dummy)
    const scheduleInput = document.querySelector('input[name="schedule[]"]');
    if(scheduleInput) scheduleInput.value = "SCHD001";
  }

  // Run on page load or attach to a button click
  window.addEventListener('DOMContentLoaded', () => {
    fillDummyData();
  });
</script>
