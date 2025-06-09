<section class="home-section">
  <div class="text">Edit Parent</div>
  <div class="teacher-container">
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif


    <form action="{{ url('mio/admin/UpdateParent/'.$editdata['parentid']) }}"  method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
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
            <label>Parent Role <span style="color: red; font-weight:700">*</span></label>

        <div class="form-row">
            <div class="form-group">
              <label><input type="radio" name="category" value="father" {{ $editdata['category'] == 'father' ? 'checked' : '' }} required> Father</label>
            </div>
            <div class="form-group">
              <label><input type="radio" name="category" value="mother" {{ $editdata['category'] == 'mother' ? 'checked' : '' }}> Mother</label>
            </div>
            <div class="form-group">
              <label><input type="radio" name="category" value="guardian" {{ $editdata['category'] == 'guardian' ? 'checked' : '' }}> Guardian</label>
            </div>
          </div>

        <hr>

          <div class="form-group wide">
            <label>Parent ID <span style="color: red; font-weight:700">*</span></label>
            <input type="text" name="parentid" id="parentID" value="{{ $editdata['parentid'] }}" readonly required />
          </div>

           <!-- put student id to get some of the student info and reflect in the inputs in per section header with checkbox that say "Same to child", if check put the info of student to the inputs here in parent -->

            <div class="form-row">
                <!-- Add student ID to populate student information -->
            <div class="form-group wide">
                <label>Student ID <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="studentid" id="studentID" value="{{ $editdata['studentid'] }}" required/>
            </div>

            <div class="form-group">
                <label>Student Name </label>

                <div class="student-preview-info" style="border: 1px dashed gray; height: 55px; padding: 5px;">
                    <span id="studentNameDisplay" style=" font-weight: bold; color: gray"></span>
                    <br>
                <span id="gradeLevelDisplay" style="font-weight: light; color: gray"></span>
                </div>

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
                <input type="text" name="first_name" placeholder="First Name" value="{{ $editdata['fname'] }}" required />
                </div>
                <div class="form-group">
                <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="last_name" placeholder="Last Name" value="{{ $editdata['lname'] }}" required />
                </div>
            </div>
            <hr>

            <div class="form-row">
                <div class="form-group">
                <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="contact_number" value="{{ $editdata['contact_number'] }}" required />
                </div>
                <div class="form-group">
                <label>Email <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="email" value="{{ $editdata['email'] }}" required />
                </div>
            </div>

            <hr>

            <!-- Dynamic Address Fields -->
            <div class="form-row">

            <input type="hidden" id="selectedRegion" value="{{ $editdata['region'] }}">
            <input type="hidden" id="selectedProvince" value="{{ $editdata['province'] }}">
            <input type="hidden" id="selectedCity" value="{{ $editdata['city'] }}">
            <input type="hidden" id="selectedBarangay" value="{{ $editdata['barangay'] }}">

            <div class="form-group wide">
                <label for="region">Region <span style="color: red; font-weight:700">*</span></label>
                <select id="region" name="region" data-selected="{{ $editdata['region'] }}" required>
                <option value="">Select Region</option>
                </select>
            </div>

            <div class="form-group wide">
                <label for="province">Province <span style="color: red; font-weight:700">*</span></label>
                <select id="province" name="province" data-selected="{{ $editdata['province'] }}" required>
                <option value="">Select Province</option>
                </select>
            </div>

            <div class="form-group wide">
                <label for="city">City/Municipality <span style="color: red; font-weight:700">*</span></label>
                <select id="city" name="city" data-selected="{{ $editdata['city'] }}" required>
                <option value="">Select City/Municipality</option>
                </select>
            </div>

            <div class="form-group wide">
                <label for="barangay">Barangay <span style="color: red; font-weight:700">*</span></label>
                <select id="barangay" name="barangay" data-selected="{{ $editdata['barangay'] }}" required>
                <option value="">Select Barangay</option>
                </select>
            </div>
            </div>

            <div class="form-row">

            <div class="form-group wide">
                <label>Building/House No., Street <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="address" placeholder="Home Address" value="{{ $editdata['address'] }}" required />
                </div>

                <div class="form-group wide">
                <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
                <input type="number" name="zip_code" placeholder="Zip Code" minlength="4" value="{{ $editdata['zip_code'] }}" maxlength="4" required />
                </div>
          </div>

            <!-- Checkbox to Copy Information from Student -->
            <div class="form-group checkbox-container" id="sameToChildContainer" style="display: none;">
                <label><input type="checkbox" id="sameToChild" /> Same as Child's Address</label>
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
        </div>

      </div>
    </form>
  </div>
</section>


<!-- ----- SCRIPTS ----- -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const regionSelect = document.getElementById("region");
    const provinceSelect = document.getElementById("province");
    const citySelect = document.getElementById("city");
    const barangaySelect = document.getElementById("barangay");
    const addressInput = document.querySelector('input[name="address"]');
    const zipInput = document.querySelector('input[name="zip_code"]');
    const studentIDInput = document.getElementById('studentID');
    const sameToChildCheckbox = document.getElementById('sameToChild');
    const checkboxContainer = document.getElementById('sameToChildContainer'); // Make sure this exists
    const studentNameDisplay = document.getElementById('studentNameDisplay'); // Make sure this exists
    const gradeLevelDisplay = document.getElementById('gradeLevelDisplay');   // Make sure this exists

   // Map region codes to their proper display names
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

// Load Regions
fetch("https://psgc.gitlab.io/api/regions/")
    .then(res => res.json())
    .then(async regions => {
        regions.forEach(region => {
            const regionCode = region.code;
            const regionLabel = regionNameMap[regionCode] || region.name;
            const option = new Option(regionLabel, regionCode);
            regionSelect.add(option);
        });

        const selectedRegion = document.getElementById("selectedRegion").value;
        const selectedProvince = document.getElementById("selectedProvince").value;
        const selectedCity = document.getElementById("selectedCity").value;
        const selectedBarangay = document.getElementById("selectedBarangay").value;

        if (selectedRegion) {
            regionSelect.value = selectedRegion;
            regionSelect.dispatchEvent(new Event("change"));

            // Wait and load province
            await waitForOptions(provinceSelect, selectedProvince);
            provinceSelect.value = selectedProvince;
            provinceSelect.dispatchEvent(new Event("change"));

            // Wait and load city
            await waitForOptions(citySelect, selectedCity);
            citySelect.value = selectedCity;
            citySelect.dispatchEvent(new Event("change"));

            // Wait and load barangay
            await waitForOptions(barangaySelect, selectedBarangay);
            barangaySelect.value = selectedBarangay;
        }
    });



    // On Region Change
    regionSelect.addEventListener("change", function () {
        provinceSelect.innerHTML = `<option disabled selected>Loading...</option>`;
        citySelect.innerHTML = `<option disabled selected>Select City/Municipality</option>`;
        barangaySelect.innerHTML = `<option disabled selected>Select Barangay</option>`;
        provinceSelect.disabled = true;
        citySelect.disabled = true;
        barangaySelect.disabled = true;

        fetch(`https://psgc.gitlab.io/api/regions/${this.value}/provinces/`)
        .then(res => res.json())
        .then(provinces => {
            provinceSelect.innerHTML = `<option disabled selected>Select Province</option>`;
            provinces.forEach(province => {
            const option = new Option(province.name, province.code);
            provinceSelect.add(option);
            });
            provinceSelect.disabled = false;
        });
    });

    // On Province Change
    provinceSelect.addEventListener("change", function () {
        citySelect.innerHTML = `<option disabled selected>Loading...</option>`;
        barangaySelect.innerHTML = `<option disabled selected>Select Barangay</option>`;
        citySelect.disabled = true;
        barangaySelect.disabled = true;

        fetch(`https://psgc.gitlab.io/api/provinces/${this.value}/cities-municipalities/`)
        .then(res => res.json())
        .then(cities => {
            citySelect.innerHTML = `<option disabled selected>Select City/Municipality</option>`;
            cities.forEach(city => {
            const option = new Option(city.name, city.code);
            citySelect.add(option);
            });
            citySelect.disabled = false;
        });
    });

    // On City Change
    citySelect.addEventListener("change", function () {
        barangaySelect.innerHTML = `<option disabled selected>Loading...</option>`;
        barangaySelect.disabled = true;

        fetch(`https://psgc.gitlab.io/api/cities-municipalities/${this.value}/barangays/`)
        .then(res => res.json())
        .then(barangays => {
            barangaySelect.innerHTML = `<option disabled selected>Select Barangay</option>`;
            barangays.forEach(barangay => {
            const option = new Option(barangay.name, barangay.code);
            barangaySelect.add(option);
            });
            barangaySelect.disabled = false;
        });
    });

    // Student ID input event
    if (studentIDInput.value.trim() !== '') {
        const studentID = studentIDInput.value.trim();
        checkboxContainer.style.display = 'block';

        fetch(`/mio/admin/get-student/${studentID}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    studentNameDisplay.textContent = `${data.first_name} ${data.last_name}`;
                    gradeLevelDisplay.textContent = `Grade ${data.grade_level || 'N/A'}`;
                    window.studentData = data;

                    // Automatically check checkbox and autofill
                    sameToChildCheckbox.checked = true;
                    autofillAddress(data);
                }
            })
            .catch(error => {
                studentNameDisplay.textContent = '';
                gradeLevelDisplay.textContent = '';
                alert("Student data could not be fetched.");
            });
    }


    // Checkbox: Same to Child
    sameToChildCheckbox.addEventListener('change', function () {
        if (this.checked && window.studentData) {
        autofillAddress(window.studentData);
        } else {
        clearAddressFields();
    }
    });

    function clearAddressFields() {
        regionSelect.value = '';
        provinceSelect.innerHTML = '<option value="">Select Province</option>';
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        addressInput.value = '';
        zipInput.value = '';
    }


    async function autofillAddress(data) {
        if (!regionSelect || !provinceSelect || !citySelect || !barangaySelect) {
        console.warn("One or more address fields are missing from the DOM.");
        return;
        }

        if (addressInput) addressInput.value = data.address || "";
        if (zipInput) zipInput.value = data.zip_code || "";

        try {
        regionSelect.value = data.region;
        regionSelect.dispatchEvent(new Event("change"));

        await waitForOptions(provinceSelect, data.province);
        provinceSelect.value = data.province;
        provinceSelect.dispatchEvent(new Event("change"));

        await waitForOptions(citySelect, data.city);
        citySelect.value = data.city;
        citySelect.dispatchEvent(new Event("change"));

        await waitForOptions(barangaySelect, data.barangay);
        barangaySelect.value = data.barangay;
        } catch (err) {
        console.warn("Address autofill failed:", err);
        }
    }

    function waitForOptions(selectElement, targetValue, timeout = 3000) {
        return new Promise((resolve, reject) => {
        const start = Date.now();
        const check = () => {
            const optionExists = Array.from(selectElement.options).some(opt => opt.value == targetValue);
            if (optionExists) return resolve();
            if (Date.now() - start > timeout) return reject("Timeout waiting for options to load: " + selectElement.id);
            setTimeout(check, 100);
        };
        check();
        });
    }
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


<!-- ACCOUNT INFO AND EMAIL -->
<script>
// Function to update account username and password fields
function updateAccountInfo() {
    const personalEmail = document.querySelector('input[name="email"]').value;

    document.getElementById('account_username').value = personalEmail;  // use email as username
}

// Update fields when the page loads
window.addEventListener('load', updateAccountInfo);

// Also update fields whenever email or birthday inputs are changed
document.querySelector('input[name="email"]').addEventListener('input', updateAccountInfo);
</script>

<!-- TOGGLE PASSWORD -->
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


