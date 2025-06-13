<section class="home-section">
  <div class="text">Add New Teacher</div>

  <div class="teacher-container">
    @include('mio.dashboard.status-message')

    <form action="{{ route('mio.AddTeacher', ['uid' => session('uid')]) }}" method="POST" enctype="multipart/form-data">
      @csrf

      <!-- HEADER CONTROLS -->
      <div class="table-header">
        <div class="search-container" style="background: transparent;"></div>
        <div class="button-group">
          <button type="button" class="btn cancel-btn"><a href="{{ route(("mio.students")) }}">Cancel</a></button>
          <button type="submit" class="btn add-btn">
            <span class="icon">+</span> New Teacher
          </button>
        </div>
      </div>

      <div class="form-container">
        <!-- Teacher Information Section -->
        <div class="section-header">Teacher Information</div>
        <div class="section-content">
         <label>Teacher Category<span style="color: red; font-weight:700;">*</span></label>
          <div class="form-row" style="margin-left: 1rem;">
            <div class="form-group">
              <label><input type="radio" name="category" value="new" required checked> New</label>
            </div>
            <div class="form-group teacher-category">
              <label><input type="radio" name="category" value="full-time"> Full-Time</label>
            </div>
            <div class="form-group teacher-category">
              <label><input type="radio" name="category" value="part-time"> Part-Time</label>
            </div>
            <div class="form-group teacher-category">
              <label><input type="radio" name="category" value="intern"> Intern</label>
            </div>
          </div>
          <div class="form-group wide">
              <label>Teacher ID <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="teacherid" id="teacherID" required />
            </div>

            <div class="form-group">
                <label for="department_id">Department</label>
                <select name="department_id" id="department_id" required>
                    <option value="">Select Department</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept['departmentid'] }}">{{ $dept['department_name'] }}</option>
                    @endforeach
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
            <label>Birthday <span style="color: red; font-weight:700">*</span></label>
            <input
                type="date"
                id="birthday"
                name="birthday"
                required
                min="1900-01-01"
                max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}"
                />

          </div>

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

          <div class="form-row">
            <div class="form-group">
              <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="contact_number" placeholder="Contact Number" required />
            </div>
            <div class="form-group wide">
              <label>Emergency Contact Number <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="emergency_contact" placeholder="Emergency Contact Number" required />
            </div>
            <div class="form-group">
              <label>Email <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="email" placeholder="Email Address" required />
            </div>
          </div>
        </div>
        </div>

        <!-- Educational Attainment Section -->
        <div class="section-header">Educational Attainment</div>
        <div class="section-content">
        <div class="form-row">
            <div class="form-group wide">
            <label>Highest Educational Attainment <span style="color: red; font-weight:700">*</span></label>
            <select name="educational_attainment" required>
                <option value="" disabled selected>Select Attainment</option>
                <option value="Bachelor's Degree">Bachelor's Degree</option>
                <option value="Master's Degree">Master's Degree</option>
                <option value="Doctorate Degree">Doctorate Degree</option>
                <option value="Post-graduate Diploma">Post-graduate Diploma</option>
                <option value="Vocational Course">Vocational Course</option>
            </select>
            </div>
            <div class="form-group">
            <label>Course / Major <span style="color: red; font-weight:700">*</span></label>
            <input type="text" name="course" placeholder="e.g., Major in English / Information Technology" required />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group wide">
                <label>School / University Attended <span style="color: red; font-weight:700">*</span></label>
                <select id="universitySelect" name="university" required>
                    <option value="">Select School</option>
                    <!-- Populated by JavaScript -->
                    <option value="Other">Other</option>
                </select>
                </div>

                <div class="form-group wide" id="customUniversityGroup" style="display: none;">
                <label>Enter School / University Name <span style="color: red; font-weight:700">*</span></label>
                <input type="text" id="customUniversity" name="custom_university" placeholder="Enter school name here" />
                </div>

            <div class="form-group">
                <label>Year Graduated <span style="color: red; font-weight:700">*</span></label>
                <select name="year_graduated" required>
                    <option value="" disabled selected>Select Year</option>
                    <!-- Example: dynamically generate years via JS -->
                    <!-- JS will populate this -->
                </select>
                </div>
            <div class="form-group">
                <label>LET Passer?</label>
                <select name="let_passer">
                    <option value="" disabled selected>Select</option>
                    <option value="Yes" @if (isset($editdata['let_passer']) && $editdata['let_passer'] == 'Yes') selected @endif>Yes</option>
                    <option value="No" @if (isset($editdata['let_passer']) && $editdata['let_passer'] == 'No') selected @endif>No</option>
                </select>
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

<!-- SCHOOLS -->
<script>
  // Fetch and populate PH universities
  fetch('http://universities.hipolabs.com/search?country=Philippines')
    .then(response => response.json())
    .then(data => {
      const select = document.getElementById('universitySelect');

      // Add each university to the select dropdown
      data.forEach(univ => {
        const option = document.createElement('option');
        option.value = univ.name;
        option.textContent = univ.name;
        select.insertBefore(option, select.querySelector('option[value="Other"]'));
      });
    })
    .catch(error => console.error('Error fetching universities:', error));

  // Show/hide custom university input
  document.getElementById('universitySelect').addEventListener('change', function () {
    const customGroup = document.getElementById('customUniversityGroup');
    if (this.value === 'Other') {
      customGroup.style.display = 'block';
      document.getElementById('customUniversity').required = true;
    } else {
      customGroup.style.display = 'none';
      document.getElementById('customUniversity').required = false;
    }
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
let lastStudentID = "TE2025171"; // Example, but won't matter since random

// Randomize last 3 digits (000–999)
const randomLastThreeDigits = Math.floor(Math.random() * 1000);

// Format the last 3 digits to always be 3 digits long (e.g., 001, 087, 999)
const lastThreeDigits = String(randomLastThreeDigits).padStart(3, '0');

// Generate the student ID with the current year, week, day, and random last 3 digits
const studentID = `TE${currentYear}${String(currentWeek).padStart(2, '0')}${String(currentDay)}${lastThreeDigits}`;

// Set the value in the input field
document.getElementById('teacherID').value = studentID;
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


<script>
  const select = document.querySelector('select[name="year_graduated"]');
  const currentEducYear = new Date().getFullYear();
  for (let year = currentEducYear; year >= 1950; year--) {
    const option = document.createElement('option');
    option.value = year;
    option.textContent = year;
    select.appendChild(option);
  }
</script>

<!-- FOR TESTING -->

<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('input[name="category"][value="part-time"]').checked = true;

    document.querySelector('input[name="first_name"]').value = 'Maria';
    document.querySelector('input[name="last_name"]').value = 'Santos';
    document.querySelector('select[name="gender"]').value = 'Female';
    document.getElementById('age').value = 28;
    document.getElementById('birthday').value = '1997-04-10';

    document.querySelector('input[name="address"]').value = 'Unit 502, Sunshine Residences, Quezon City';
    document.querySelector('input[name="zip_code"]').value = '1100';
    document.querySelector('input[name="contact_number"]').value = '09181234567';
    document.querySelector('input[name="emergency_contact"]').value = '09998887766';
    document.querySelector('input[name="email"]').value = 'maria.santos@example.com';

    document.querySelector('select[name="educational_attainment"]').value = "Bachelor's Degree";
    document.querySelector('input[name="course"]').value = 'Bachelor of Secondary Education';

    // Wait for university list to populate
    setTimeout(() => {
      document.getElementById('universitySelect').value = 'Ateneo de Manila University';
    }, 1000);

    document.querySelector('select[name="year_graduated"]').value = '2016';
    document.querySelector('select[name="let_passer"]').value = 'Yes';

    document.getElementById('account_username').value = 'mariasantos';
    document.getElementById('account_password').value = 'AnotherSecure123';
    document.querySelector('select[name="account_status"]').value = 'active';
  });
</script>






