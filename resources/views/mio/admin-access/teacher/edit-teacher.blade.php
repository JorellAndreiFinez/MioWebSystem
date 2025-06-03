<!-- Admin Password Modal -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <span class="modal-title">Admin Verification</span>
        </div>
        <div class="modal-body">
            <p id="confirmMessage">Enter your password to change the teacher's account password:</p>
            <input type="password" id="adminPasswordInput" placeholder="Admin Password" class="form-control" />
            <p id="errorMessage" style="color: red; display: none; font-size: 0.9rem;">Incorrect password.</p>
        </div>
        <div class="modal-footer">
            <button class="btn cancel-btn" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn confirm-btn" onclick="verifyAdminPassword()">Confirm</button>
        </div>
    </div>
</div>

<section class="home-section">
<div class="text">Edit Teacher</div>
<div class="teacher-container">
@include('mio.dashboard.status-message')

 <form action="{{ url('mio/admin/UpdateTeacher/'.$editdata['teacherid']) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <!-- HEADER CONTROLS -->
    <div class="table-header">
            <div class="search-container" style="background: transparent;">
            </div>

            <div class="button-group">
            <button type="button" class="btn cancel-btn"><a href="{{ route(("mio.teachers")) }}">Cancel</a>
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
            <label>Teacher Category<span style="color: red; font-weight:700">*</span></label>

                <div class="form-row" style="margin-left: 1rem;">
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
                    <label>Teacher ID <span style="color: red; font-weight:700">*</span></label>
                    <input type="text" name="teacherid" id="teacherid" value="{{ $editdata['teacherid'] }}" required />
                </div>
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select name="department_id" id="department_id" required>
                        <option value="">Select Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept['departmentid'] }}"
                                {{ $editdata['department_id'] ?? '' == $dept['departmentid'] ? 'selected' : '' }}>
                                {{ $dept['department_name'] }}
                            </option>
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
                <input type="text" name="first_name" value="{{ $editdata['fname'] }}" placeholder="First Name" required />
                </div>
                <div class="form-group">
                <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="last_name" value="{{ $editdata['lname'] }}" placeholder="Last Name" required />
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
                <input type="number" name="age" id="age" value="{{ $editdata['age'] }}" placeholder="Age" required min="1" max="100" required />
                </div>
                <div class="form-group">
                <label>Birthday <span style="color: red; font-weight:700">*</span></label>
                <input type="date"
                id="birthday"
                name="birthday"
                min="1900-01-01"
                max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}"
                value="{{ $editdata['bday'] }}" required />
                </div>
            </div>

            <div class="form-row">

                <input type="hidden" id="selectedRegion" value="{{ $editdata['region'] }}">
                <input type="hidden" id="selectedProvince" value="{{ $editdata['province'] }}">
                <input type="hidden" id="selectedCity" value="{{ $editdata['city'] }}">
                <input type="hidden" id="selectedBarangay" value="{{ $editdata['barangay'] }}">


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
                <label>Barangay </label>
                <select id="barangay" name="barangay" required disabled>
                    <option value="" disabled selected>Select Barangay</option>
                </select>
                </div>

            </div>

            <div class="form-row">

                <div class="form-group wide">
                    <label>Street Name, Building, House No. <span style="color: red; font-weight:700">*</span></label>
                    <input type="text" name="address" value="{{$editdata['address']}}" required />
                </div>

                <div class="form-group">
                    <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
                    <input type="number" name="zip_code" value="{{ $editdata['zip_code'] }}" minlength="4" maxlength="4" required />
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

            <!-- Educational Attainment Section -->
            <div class="section-header">Educational Attainment</div>
            <div class="section-content">
            <div class="form-row">
                <div class="form-group wide">
                <label>Highest Educational Attainment <span style="color: red; font-weight:700">*</span></label>
                <select name="educational_attainment" required>
                    <option value="" disabled {{ !isset($editdata['educational_attainment']) ? 'selected' : '' }}>Select Attainment</option>
                    <option value="Bachelor's Degree" {{ $editdata['educational_attainment'] == "Bachelor's Degree" ? 'selected' : '' }}>Bachelor's Degree</option>
                    <option value="Master's Degree" {{ $editdata['educational_attainment'] == "Master's Degree" ? 'selected' : '' }}>Master's Degree</option>
                    <option value="Doctorate Degree" {{ $editdata['educational_attainment'] == "Doctorate Degree" ? 'selected' : '' }}>Doctorate Degree</option>
                    <option value="Post-graduate Diploma" {{ $editdata['educational_attainment'] == "Post-graduate Diploma" ? 'selected' : '' }}>Post-graduate Diploma</option>
                    <option value="Vocational Course" {{ $editdata['educational_attainment'] == "Vocational Course" ? 'selected' : '' }}>Vocational Course</option>
                </select>

                </div>
                <div class="form-group">
                <label>Course / Major <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="course" value="{{ $editdata['course'] }}" placeholder="e.g., Major in English / Information Technology" required />

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
                <option value="" disabled {{ !isset($editdata['let_passer']) ? 'selected' : '' }}>Select</option>
                <option value="Yes" @if ($editdata['let_passer'] == 'Yes') selected @endif>Yes</option>
                <option value="No" @if ($editdata['let_passer'] == 'No') selected @endif>No</option>
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
                        <input type="text" name="username" id="account_username" value="{{ $editdata['username'] ?? '' }}" required readonly/>
                    </div>

                    <div class="form-group">
                    <label for="account_password">Password</label>
                    <div id="passwordInputWrapper" style="display: none; position: relative;">
                        <input
                            type="password"
                            name="account_password"
                            id="account_password"
                            placeholder="Enter new password"
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
                    <input
                        type="text"
                        id="triggerPasswordModal"
                        placeholder="Click to change password"
                        readonly
                        style="cursor: pointer;"
                        onclick="openPasswordModal()"
                    />
                </div>

                <div class="form-group">
                        <label>Account Status <span style="color: red; font-weight:700">*</span></label>
                        <select name="account_status" required>
                            <option value="active" @if (isset($editdata['account_status']) && $editdata['account_status'] == 'active') selected @endif>Active</option>
                            <option value="inactive" @if (isset($editdata['account_status']) && $editdata['account_status'] == 'inactive') selected @endif>Inactive</option>
                        </select>
                    </div>
            </div>
            </div>


            <!-- Schedule Section -->
            <div class="section-header">Schedule Information (For One-on-One Therapy)</div>
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

    </form>
</div>

</section>


<!-- --------   SCRIPTS  -------- -->


<!-- CHANGE PASSWORD VALIDATION -->
<script>
    function openPasswordModal() {
        document.getElementById('confirmModal').style.display = 'flex';
        document.getElementById('adminPasswordInput').value = '';
        document.getElementById('errorMessage').style.display = 'none';
    }

    function closeModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    function verifyAdminPassword() {
        const enteredPassword = document.getElementById('adminPasswordInput').value;
        const email = '{{ session("firebase_user.email") }}'; // Use admin's current session email

        fetch("{{ route('mio.verify-admin-password') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: email, password: enteredPassword })
        })
        .then(response => {
            if (!response.ok) throw new Error("Invalid credentials");
            return response.json();
        })
        .then(data => {
            document.getElementById('passwordInputWrapper').style.display = 'block';
            document.getElementById('triggerPasswordModal').style.display = 'none';
            closeModal();
        })
        .catch(error => {
            document.getElementById('errorMessage').style.display = 'block';
        });
    }

    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('account_password');
        const eyeIcon = document.getElementById('eye-icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
</script>

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

<!-- YEAR GRADUTED -->
<script>
  const yearSelect = document.querySelector('select[name="year_graduated"]');
  const currentYear = new Date().getFullYear();

 const selectedYear = @json($editdata['year_graduated'] ?? '');
    for (let year = currentYear; year >= 1950; year--) {
    const option = document.createElement('option');
    option.value = year;
    option.textContent = year;
    if (year.toString() === selectedYear) {
        option.selected = true;
    }
    yearSelect.appendChild(option);
    }

</script>

<!-- SCHOOLS -->
<script>
  // Fetch and populate PH universities
  fetch('http://universities.hipolabs.com/search?country=Philippines')
    .then(response => response.json())
    .then(data => {
      const select = document.getElementById('universitySelect');
      const selectedUniversity = @json($editdata['university'] ?? '');
      const customUniversityGroup = document.getElementById('customUniversityGroup');
      const customUniversityInput = document.getElementById('customUniversity');
      let foundMatch = false;

      data.forEach(univ => {
        const option = document.createElement('option');
        option.value = univ.name;
        option.textContent = univ.name;
        if (univ.name === selectedUniversity) {
          option.selected = true;
          foundMatch = true;
        }
        select.insertBefore(option, select.querySelector('option[value="Other"]'));
      });

      // If not found in list, show Other + prefill custom input
      if (!foundMatch && selectedUniversity) {
        select.value = "Other";
        customUniversityGroup.style.display = "block";
        customUniversityInput.value = selectedUniversity;
        customUniversityInput.required = true;
      }
    })
    .catch(error => console.error('Error fetching universities:', error));

  // Show/hide custom university input
  document.getElementById('universitySelect').addEventListener('change', function () {
    const customGroup = document.getElementById('customUniversityGroup');
    const customInput = document.getElementById('customUniversity');
    if (this.value === 'Other') {
      customGroup.style.display = 'block';
      customInput.required = true;
    } else {
      customGroup.style.display = 'none';
      customInput.required = false;
      customInput.value = '';
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


// Utility function to fetch data
async function fetchData(url) {
  const response = await fetch(url);
  return await response.json();
}

// Utility to populate any select element
function populateSelect(selectEl, data, placeholder, customMap = null, useNameAsValue = false) {
  selectEl.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
  data.forEach(item => {
    const option = document.createElement("option");
    const label = (selectEl.id === "region" && regionNameMap[item.code])
      ? regionNameMap[item.code]
      : item.name;

    option.value = useNameAsValue ? label : item.code;
    option.text = label;
    selectEl.appendChild(option);
  });
  selectEl.disabled = false;
}


// Main region → province → city → barangay chain
document.addEventListener("DOMContentLoaded", async () => {
  const regionSelect = document.getElementById("region");
  const provinceSelect = document.getElementById("province");
  const citySelect = document.getElementById("city");
  const barangaySelect = document.getElementById("barangay");

  const selectedRegion = document.getElementById("selectedRegion").value;
  const selectedProvince = document.getElementById("selectedProvince").value;
  const selectedCity = document.getElementById("selectedCity").value;
  const selectedBarangay = document.getElementById("selectedBarangay").value;

  // 1. Load and select Region
  const regions = await fetchData(`${apiBase}/regions/`);
  populateSelect(regionSelect, regions, "Select Region");
  regionSelect.value = selectedRegion;

  // 2. Load and select Province
  if (selectedRegion) {
    const provinces = await fetchData(`${apiBase}/regions/${selectedRegion}/provinces/`);
    populateSelect(provinceSelect, provinces, "Select Province");
    provinceSelect.value = selectedProvince;
    provinceSelect.disabled = false;
  }

  // 3. Load and select City
  if (selectedProvince) {
    const cities = await fetchData(`${apiBase}/provinces/${selectedProvince}/cities-municipalities/`);
    populateSelect(citySelect, cities, "Select City/Municipality");
    citySelect.value = selectedCity;
    citySelect.disabled = false;
  }

  // 4. Load and select Barangay
  if (selectedCity) {
    const barangays = await fetchData(`${apiBase}/cities-municipalities/${selectedCity}/barangays/`);
    populateSelect(barangaySelect, barangays, "Select Barangay");
    barangaySelect.value = selectedBarangay;
    barangaySelect.disabled = false;
  }

  // Dynamic Change Handlers

  // On Region Change
  regionSelect.addEventListener("change", async () => {
    const regionCode = regionSelect.value;
    const provinces = await fetchData(`${apiBase}/regions/${regionCode}/provinces/`);
    populateSelect(provinceSelect, provinces, "Select Province");
    provinceSelect.disabled = false;

    // Reset dependent selects
    citySelect.innerHTML = `<option value="" disabled selected>Select City/Municipality</option>`;
    citySelect.disabled = true;
    barangaySelect.innerHTML = `<option value="" disabled selected>Select Barangay</option>`;
    barangaySelect.disabled = true;
  });

  // On Province Change
  provinceSelect.addEventListener("change", async () => {
    const provinceCode = provinceSelect.value;
    const cities = await fetchData(`${apiBase}/provinces/${provinceCode}/cities-municipalities/`);
    populateSelect(citySelect, cities, "Select City/Municipality");
    citySelect.disabled = false;

    barangaySelect.innerHTML = `<option value="" disabled selected>Select Barangay</option>`;
    barangaySelect.disabled = true;
  });

  // On City Change
  citySelect.addEventListener("change", async () => {
    const cityCode = citySelect.value;
    const barangays = await fetchData(`${apiBase}/cities-municipalities/${cityCode}/barangays/`);
    populateSelect(barangaySelect, barangays, "Select Barangay");
    barangaySelect.disabled = false;
  });
});
</script>

<!-- SCHEDULE -->
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
