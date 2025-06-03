
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

          <div class="form-row">
            <div class="form-group">
              <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="contact_number" placeholder="Contact Number" required />
            </div>
            <div class="form-group">
              <label>Email <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="email" placeholder="Email Address" required />
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
            <input type="password" name="account_password" id="account_password" required />
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
    const emailInput = document.querySelector('input[name="email"]');


    // Load Regions
    fetch("https://psgc.gitlab.io/api/regions/")
        .then(res => res.json())
        .then(regions => {
        regions.forEach(region => {
            const option = new Option(region.name, region.code);
            regionSelect.add(option);
        });
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
    studentIDInput.addEventListener('input', function () {
        const studentID = this.value;


        if (studentID && studentID.length > 0) {
        checkboxContainer.style.display = 'block';

        fetch(`/mio/admin/get-student/${studentID}`)
            .then(response => response.json())
            .then(data => {

            if (data) {
                studentNameDisplay.textContent = `${data.first_name} ${data.last_name}`;
                gradeLevelDisplay.textContent = `Grade ${data.grade_level || 'N/A'}`;
                window.studentData = data;


                if (sameToChildCheckbox.checked) {
                autofillAddress(data);
                }
            }
            })
            .catch(error => {
            studentNameDisplay.textContent = '';
            gradeLevelDisplay.textContent = '';
            emailInput.value = '';
            alert("Student data could not be fetched.");
            });
        } else {
        studentNameDisplay.textContent = '';
        gradeLevelDisplay.textContent = '';
        checkboxContainer.style.display = 'none';
          emailInput.value = '';
        }
    });

    // Checkbox: Same to Child
    sameToChildCheckbox.addEventListener('change', function () {
        if (this.checked && window.studentData) {
        autofillAddress(window.studentData);
        }
        else {
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
  const emailInput = document.querySelector('input[name="email"]');
  const personalEmail = document.querySelector('input[name="email"]');



  if (studentID && studentID.length > 0) {
    checkboxContainer.style.display = 'block';

    fetch(`/mio/admin/get-student/${studentID}`)
      .then(response => response.json())
      .then(data => {
        if (data) {
          studentNameDisplay.textContent = `${data.first_name} ${data.last_name}`;
          gradeLevelDisplay.textContent = `Grade ${data.grade_level || 'N/A'}`;
            emailInput.value = `${data.email}`;
            document.getElementById('account_username').value =  emailInput.value;
            document.getElementById('account_password').value = `${data.password}`;


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

<!-- FOR TESTING - AUTO-FILL PARENT FORM -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Select category as 'father'
    document.querySelector('input[name="category"][value="father"]').checked = true;

    // Personal Information
    document.querySelector('input[name="first_name"]').value = 'Jose';
    document.querySelector('input[name="last_name"]').value = 'Dela Cruz';
    document.querySelector('select[name="gender"]').value = 'Male';
    document.querySelector('input[name="age"]').value = 45;
    document.querySelector('input[name="birthday"]').value = '1980-06-15';
    document.querySelector('input[name="contact_number"]').value = '09787230194';


    document.querySelector('select[name="account_status"]').value = 'active';
  });
</script>





