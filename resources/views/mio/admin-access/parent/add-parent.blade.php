
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
          <button type="button" class="btn cancel-btn"><a href="{{ route('mio.parents') }}">Cancel</a></button>
          <button type="submit" class="btn add-btn">
            <span class="icon">+</span> New Parent
          </button>
        </div>
      </div>

      <div class="form-container">
        <!-- Teacher Information Section -->
        <div class="section-header">Parent Information</div>
        <div class="section-content">
            <label>Parent Role <span style="color: red; font-weight:700">*</span></label>

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

          <hr>

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
                <label>Student Name </label>

                <div class="student-preview-info" style="border: 1px dashed gray; height: 55px; padding: 5px;">
                    <span id="studentNameDisplay" style=" font-weight: bold; color: gray"></span>
                    <br>
                <span id="gradeLevelDisplay" style="font-weight: light; color: gray"></span>
                </div>

                </div>

                <hr>

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
          </div>

          <hr>

          <div class="form-row">
            <div class="form-group">
              <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
              <input type="text" id="contact_number" name="contact_number" placeholder="Contact Number" required />
            </div>
            <div class="form-group">
              <label>Email <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="email" placeholder="Email Address" required />
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

          <!-- Checkbox to Copy Information from Student -->
        <div class="form-group checkbox-container" style="display: none;">
        <label><input type="checkbox" id="sameToChild" /> Same as Child's Address</label>
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
            <input type="password" name="account_password" id="account_password" />
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

<!-- SCRIPTS -->

<!-- PH ADDRESS with region name mapping and async handling -->
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
        if (!res.ok) throw new Error("Failed to fetch " + url);
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

        const addressInput = document.querySelector('input[name="address"]');
        const zipInput = document.querySelector('input[name="zip_code"]');
        const studentIDInput = document.getElementById('studentID');
        const sameToChildCheckbox = document.getElementById('sameToChild');
        const checkboxContainer = document.getElementById('sameToChildContainer');
        const studentNameDisplay = document.getElementById('studentNameDisplay');
        const gradeLevelDisplay = document.getElementById('gradeLevelDisplay');
        const account_password = document.getElementById('account_password');
        const emailInput = document.querySelector('input[name="email"]');

        // Load Regions with friendly names
        try {
            const regions = await fetchData(`${apiBase}/regions/`);
            populateSelect(regionSelect, regions, "Select Region", regionNameMap);
        } catch (e) {
            console.error("Error loading regions:", e);
        }

        regionSelect.addEventListener("change", async () => {
            provinceSelect.innerHTML = '';
            citySelect.innerHTML = '';
            barangaySelect.innerHTML = '';
            provinceSelect.disabled = citySelect.disabled = barangaySelect.disabled = true;

            const selectedRegion = regionSelect.value;

            try {
            const provinces = await fetchData(`${apiBase}/regions/${selectedRegion}/provinces/`);

            if (provinces.length === 0) {
                // Region has no provinces: disable province dropdown but keep visible
                provinceSelect.innerHTML = `<option value="" disabled selected>Not Applicable</option>`;
                provinceSelect.disabled = true;

                // Load cities/municipalities under the region directly
                const cities = await fetchData(`${apiBase}/regions/${selectedRegion}/cities-municipalities/`);
                populateSelect(citySelect, cities, "Select City/Municipality");
            } else {
                populateSelect(provinceSelect, provinces, "Select Province");
            }
            } catch (e) {
            console.error("Error loading provinces or cities:", e);
            provinceSelect.innerHTML = `<option value="" disabled selected>Error loading data</option>`;
            }
        });

        provinceSelect.addEventListener("change", async () => {
            citySelect.innerHTML = '';
            barangaySelect.innerHTML = '';
            citySelect.disabled = barangaySelect.disabled = true;

            try {
            const cities = await fetchData(`${apiBase}/provinces/${provinceSelect.value}/cities-municipalities/`);
            populateSelect(citySelect, cities, "Select City/Municipality");
            } catch (e) {
            console.error("Error loading cities:", e);
            citySelect.innerHTML = `<option value="" disabled selected>Error loading data</option>`;
            }
        });

        citySelect.addEventListener("change", async () => {
            barangaySelect.innerHTML = '';
            barangaySelect.disabled = true;

            try {
            const barangays = await fetchData(`${apiBase}/cities-municipalities/${citySelect.value}/barangays/`);
            populateSelect(barangaySelect, barangays, "Select Barangay");
            } catch (e) {
            console.error("Error loading barangays:", e);
            barangaySelect.innerHTML = `<option value="" disabled selected>Error loading data</option>`;
            }
        });

        // Student ID input event to fetch student data and autofill
        studentIDInput.addEventListener('input', async () => {
            const studentID = studentIDInput.value.trim();

            if (studentID.length > 0) {
            checkboxContainer.style.display = 'block';

            try {
                const response = await fetch(`/mio/admin/get-student/${studentID}`);
                if (!response.ok) throw new Error('Student not found');
                const data = await response.json();

                if (data.error) throw new Error(data.error);

                studentNameDisplay.textContent = `${data.first_name || ''} ${data.last_name || ''}`.trim();
                gradeLevelDisplay.textContent = `Grade ${data.grade_level || 'N/A'}`;
                emailInput.value = data.email || '';
                account_password.value = data.password || '';
                window.studentData = data;

                if (sameToChildCheckbox.checked) {
                await autofillAddress(data);
                }
            } catch (error) {
                studentNameDisplay.textContent = '';
                gradeLevelDisplay.textContent = '';
                emailInput.value = '';
                alert("Student data could not be fetched: " + error.message);
            }
            } else {
            studentNameDisplay.textContent = '';
            gradeLevelDisplay.textContent = '';
            checkboxContainer.style.display = 'none';
            emailInput.value = '';
            }
        });

        // Checkbox: Same to Child
        sameToChildCheckbox.addEventListener('change', async () => {
            if (sameToChildCheckbox.checked && window.studentData) {
            await autofillAddress(window.studentData);
            } else {
            clearAddressFields();
            }
        });

        function clearAddressFields() {
            regionSelect.value = '';
            provinceSelect.innerHTML = '<option value="" disabled selected>Select Province</option>';
            citySelect.innerHTML = '<option value="" disabled selected>Select City/Municipality</option>';
            barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
            provinceSelect.disabled = true;
            citySelect.disabled = true;
            barangaySelect.disabled = true;
            if (addressInput) addressInput.value = '';
            if (zipInput) zipInput.value = '';
        }

        async function autofillAddress(data) {
            if (!regionSelect || !provinceSelect || !citySelect || !barangaySelect) {
            console.warn("One or more address fields missing");
            return;
            }

            if (addressInput) addressInput.value = data.address || "";
            if (zipInput) zipInput.value = data.zip_code || "";

            try {
            regionSelect.value = data.region || '';
            regionSelect.dispatchEvent(new Event("change"));

            await waitForOptions(provinceSelect, data.province);
            provinceSelect.value = data.province || '';
            provinceSelect.dispatchEvent(new Event("change"));

            await waitForOptions(citySelect, data.city);
            citySelect.value = data.city || '';
            citySelect.dispatchEvent(new Event("change"));

            await waitForOptions(barangaySelect, data.barangay);
            barangaySelect.value = data.barangay || '';
            } catch (err) {
            console.warn("Autofill address failed:", err);
            }
        }

        function waitForOptions(selectElement, targetValue, timeout = 3000) {
            return new Promise((resolve, reject) => {
            const start = Date.now();
            const check = () => {
                const optionExists = Array.from(selectElement.options).some(opt => opt.value == targetValue);
                if (optionExists) return resolve();
                if (Date.now() - start > timeout) return reject(`Timeout waiting for options in ${selectElement.id}`);
                setTimeout(check, 100);
            };
            check();
            });
        }
        });
    </script>


<!-- PARENT ID -->
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

<!-- FETCH STUDENT DATA -->
<script>
document.getElementById('studentID').addEventListener('input', function () {
  const studentID = this.value;
  const studentNameDisplay = document.getElementById('studentNameDisplay');
  const gradeLevelDisplay = document.getElementById('gradeLevelDisplay');
  const contact_number = document.getElementById('contact_number');

  const checkboxContainer = document.querySelector('.form-group.checkbox-container');
  const emailInput = document.querySelector('input[name="email"]');
  const personalEmail = document.querySelector('input[name="email"]');



  if (studentID && studentID.length > 0) {
    checkboxContainer.style.display = 'block';

    fetch(`/mio/admin/get-student/${studentID}`)
      .then(response => response.json())
      .then(data => {
         if (data.error) {
            // Clear fields and maybe alert the user
            studentNameDisplay.textContent = '';
            gradeLevelDisplay.textContent = '';
            emailInput.value = '';
            document.getElementById('account_username').value = '';
            document.getElementById('account_password').value = '';
            alert(data.error);
            return; // stop further processing
        }
         // if no error, populate fields safely
        studentNameDisplay.textContent = `${data.first_name || ''} ${data.last_name || ''}`.trim();
        gradeLevelDisplay.textContent = `Grade ${data.grade_level || 'N/A'}`;
        contact_number.value = `${data.emergency_contact}`;
        emailInput.value = data.email || '';
        document.getElementById('account_username').value = data.email || '';
        // Remove password line as advised before

        window.studentData = data;

        if (document.getElementById('sameToChild').checked) {
            autofillAddress(data);
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



<!-- FOR TESTING - AUTO-FILL PARENT FORM -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Select category as 'father'
    document.querySelector('input[name="category"][value="father"]').checked = true;

    // Personal Information
    document.querySelector('input[name="first_name"]').value = 'Jose';
    document.querySelector('input[name="last_name"]').value = 'Dela Cruz';


    document.querySelector('select[name="account_status"]').value = 'active';
  });
</script>





