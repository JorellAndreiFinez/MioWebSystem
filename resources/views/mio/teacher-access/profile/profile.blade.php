<!-- Modal -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form id="editProfileForm" method="POST" action="{{ route('mio.updateProfile') }}" enctype="multipart/form-data">
            @csrf
            <!-- Centered profile picture -->
        <div class="modal-profile-pic-container" id="profilePicContainer" style="cursor:pointer; position: relative; display: inline-block;">

            <img src="{{ $teacher['photo_url'] ?? 'https://ui-avatars.com/api/?name='.$name }}"
                alt="Profile Picture" 
                class="modal-profile-pic" 
                id="profilePicImage" />

            <!-- Pencil icon overlay -->
            <div class="edit-icon" style="display:none; position: absolute; bottom: 0; right: 0; background: #0008; border-radius: 50%; padding: 6px;">
                ✎
            </div>

            <!-- Hidden file input -->
            <input type="file" name="profile_picture" id="profilePictureInput" accept="image/*"  style="display:none;">
        </div>

            <div class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="fname" value="{{ $teacher['fname'] ?? '' }}" readonly>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lname" value="{{ $teacher['lname'] ?? '' }}" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="email" value="{{ $teacher['email'] ?? '' }}" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Biography</label>
                <textarea name="bio" rows="3">{{ $teacher['bio'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="form-row">
            <div class="form-group">
                <label>Social Link</label>
                <input type="url" name="social_link" value="{{ $teacher['social_link'] ?? '' }}" placeholder="https://facebook.com/yourprofile">
            </div>
        </div>


                <button type="submit" class="primary-btn btn">Save Changes</button>


            </div>
        </form>
    </div>
</div>


<section class="home-section">
    <div class="text">Profile</div>
    <div class="grid-container">
        <!-- Begin Main-->
        <main class="main">
            <div class="profile-container">

                <!-- Main Content -->
                <div class="main-content">
                    @if (session('success'))
                    <div class="alert alert-success" style="color: green; margin-bottom: 1em;">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger" style="color: red; margin-bottom: 1em;">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="profile-card">

                    <div class="profile-picture">
                        <img src="{{ $teacher['photo_url'] ?? 'https://ui-avatars.com/api/?name='.$name }}" alt="Profile Picture" />


                        <button class="edit-button">✎</button>
                    </div>

                    <div class="profile-info">

                       @php
                            $teacher_name = ($teacher['fname'] ?? '') . ' ' . ($teacher['lname'] ?? '');
                        @endphp

                        <h2>{{ $teacher_name ?: 'No Name' }}</h2>
                        <div class="section">
                            <h3>Biography</h3>
                            <p>{{ $teacher['bio'] ?? 'No biography available.' }}</p>
                        </div>
                        <div class="section">
                            <h3>Contact</h3>
                            <p><p>{{ $teacher['email'] ?? 'No email' }}</p></p>
                        </div>
                        <div class="section">
                            <h3>Social Links</h3>
                           <p><p>{{ $teacher['social_link'] ?? 'No Linked Social' }}</p></p>
                        </div>
                    </div>
                </div>

                </div>

            </div>

            <div class="form-container">
            <!-- Student Information Section -->
            <div class="section-header">Teacher Information</div>
            <div class="section-content">
            <label>Teacher Category<span style="color: red; font-weight:700">*</span></label>

                <div class="form-row" style="margin-left: 1rem;">
                    <div class="form-group">
                        <label><input type="radio" name="category" value="new" disabled
                            @if (isset($teacher['category']) && $teacher['category'] == 'new') checked @endif> New</label>
                    </div>
                    <div class="form-group teacher-category">
                        <label><input type="radio" name="category" value="full-time" disabled
                            @if (isset($teacher['category']) && $teacher['category'] == 'full-time') checked @endif> Full-Time</label>
                    </div>
                    <div class="form-group teacher-category">
                        <label><input type="radio" name="category" value="part-time" disabled
                            @if (isset($teacher['category']) && $teacher['category'] == 'part-time') checked @endif> Part-Time</label>
                    </div>
                    <div class="form-group teacher-category">
                        <label><input type="radio" name="category" value="intern" disabled
                            @if (isset($teacher['category']) && $teacher['category'] == 'intern') checked @endif> Intern</label>
                    </div>
                </div>

                <div class="form-group wide">
                    <label>Teacher ID <span style="color: red; font-weight:700">*</span></label>
                    <input type="text" name="teacherid" id="teacherid" value="{{ $teacher['teacherid'] }}" readonly />
                </div>
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select name="department_id" id="department_id" disabled>
                        <option value="">Select Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept['departmentid'] }}"
                                {{ $teacher['department_id'] ?? '' == $dept['departmentid'] ? 'selected' : '' }}>
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
                <input type="text" name="first_name" value="{{ $teacher['fname'] }}" placeholder="First Name" readonly />
                </div>
                <div class="form-group">
                <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="last_name" value="{{ $teacher['lname'] }}" placeholder="Last Name" readonly />
                </div>
                <div class="form-group">
                <label>Gender <span style="color: red; font-weight:700">*</span></label>
                <select name="gender" disabled>
                    <option value="" disabled {{ !isset($teacher['gender']) ? 'selected' : '' }}>Select Gender</option>
                    <option value="Male" {{ isset($teacher['gender']) && $teacher['gender'] == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ isset($teacher['gender']) && $teacher['gender'] == 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ isset($teacher['gender']) && $teacher['gender'] == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

                <div class="form-group">
                <label>Age <span style="color: red; font-weight:700">*</span></label>
                <input type="number" name="age" id="age" value="{{ $teacher['age'] }}" placeholder="Age" required min="1" max="100" readonly />
                </div>
                <div class="form-group">
                <label>Birthday <span style="color: red; font-weight:700">*</span></label>
                <input type="date"
                id="birthday"
                name="birthday"
                min="1900-01-01"
                max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}"
                value="{{ $teacher['bday'] }}" readonly />
                </div>
            </div>

            <div class="form-row">

                <input type="hidden" id="selectedRegion" value="{{ $teacher['region'] }}" readonly>
                <input type="hidden" id="selectedProvince" value="{{ $teacher['province'] }}" readonly>
                <input type="hidden" id="selectedCity" value="{{ $teacher['city'] }}" readonly>
                <input type="hidden" id="selectedBarangay" value="{{ $teacher['barangay'] }}" readonly>


                <div class="form-group wide">
                    <label for="region">Region <span style="color: red; font-weight:700">*</span></label>
                    <input type="text" id="region_name" name="region_name" readonly />
                </div>

                <div class="form-group wide">
                    <label>Province <span style="color: red; font-weight:700">*</span></label>
                    <input type="text" id="province_name" name="province_name" readonly />
                </div>


                <div class="form-group wide">
                    <label>City/ Municipality <span style="color: red; font-weight:700">*</span></label>
                    <input type="text" id="city_name" name="city_name" readonly />
                </div>

                <div class="form-group wide">
                    <label>Barangay </label>
                    <input type="text" id="barangay_name" name="barangay_name" readonly />
                </div>

            </div>

            <div class="form-row">

                <div class="form-group wide">
                    <label>Street Name, Building, House No. <span style="color: red; font-weight:700">*</span></label>
                    <input type="text" name="address" value="{{$teacher['address']}}" readonly />
                </div>

                <div class="form-group">
                    <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
                    <input type="number" name="zip_code" value="{{ $teacher['zip_code'] }}" minlength="4" maxlength="4" readonly />
                </div>
                </div>


            <div class="form-row">
                <div class="form-group">
                <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="contact_number" value="{{ $teacher['contact_number'] }}" readonly />
                </div>
                <div class="form-group wide">
                <label>Emergency Contact Number <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="emergency_contact" value="{{ $teacher['emergency_contact'] }}" readonly />
                </div>
                <div class="form-group">
                <label>Email <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="email" value="{{ $teacher['email'] }}" readonly />
                </div>
            </div>
            </div>

            <!-- Educational Attainment Section -->
            <div class="section-header">Educational Attainment</div>
            <div class="section-content">
            <div class="form-row">
                <div class="form-group wide">
                <label>Highest Educational Attainment <span style="color: red; font-weight:700">*</span></label>
                <select name="educational_attainment" disabled>
                    <option value="" disabled {{ !isset($teacher['educational_attainment']) ? 'selected' : '' }}>Select Attainment</option>
                    <option value="Bachelor's Degree" {{ $teacher['educational_attainment'] == "Bachelor's Degree" ? 'selected' : '' }}>Bachelor's Degree</option>
                    <option value="Master's Degree" {{ $teacher['educational_attainment'] == "Master's Degree" ? 'selected' : '' }}>Master's Degree</option>
                    <option value="Doctorate Degree" {{ $teacher['educational_attainment'] == "Doctorate Degree" ? 'selected' : '' }}>Doctorate Degree</option>
                    <option value="Post-graduate Diploma" {{ $teacher['educational_attainment'] == "Post-graduate Diploma" ? 'selected' : '' }}>Post-graduate Diploma</option>
                    <option value="Vocational Course" {{ $teacher['educational_attainment'] == "Vocational Course" ? 'selected' : '' }}>Vocational Course</option>
                </select>

                </div>
                <div class="form-group">
                <label>Course / Major <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="course" value="{{ $teacher['course'] }}" placeholder="e.g., Major in English / Information Technology" readonly />

                </div>
            </div>

           <div class="form-row">
            <div class="form-group wide">
                <label>School / University Attended <span style="color: red; font-weight:700">*</span></label>
                <select id="universitySelect" name="university" disabled>
                    <option value="">Select School</option>
                    <!-- Populated by JavaScript -->
                    <option value="Other">Other</option>
                </select>
                </div>

                <div class="form-group wide" id="customUniversityGroup" style="display: none;">
                <label>Enter School / University Name <span style="color: red; font-weight:700">*</span></label>
                <input type="text" id="customUniversity" name="custom_university" placeholder="Enter school name here" readonly/>
                </div>

            <div class="form-group">
                <label>Year Graduated <span style="color: red; font-weight:700">*</span></label>
                <select name="year_graduated" disabled>
                    <option value="" disabled selected>Select Year</option>
                    <!-- Example: dynamically generate years via JS -->
                    <!-- JS will populate this -->
                </select>
                </div>
            <div class="form-group">
                <label>LET Passer?</label>
               <select name="let_passer" disabled>
                <option value="" disabled {{ !isset($teacher['let_passer']) ? 'selected' : '' }}>Select</option>
                <option value="Yes" @if ($teacher['let_passer'] == 'Yes') selected @endif>Yes</option>
                <option value="No" @if ($teacher['let_passer'] == 'No') selected @endif>No</option>
                </select>

        </div>
        </div>


            </div>

        </div>
        </main>
        <!-- End Main -->
    </div>
</section>

<!-- PH ADDRESS -->
<script>
document.addEventListener("DOMContentLoaded", async () => {
  const regionCode = document.getElementById("selectedRegion").value;
  const provinceCode = document.getElementById("selectedProvince").value;
  const cityCode = document.getElementById("selectedCity").value;
  const barangayCode = document.getElementById("selectedBarangay").value;

  // Show region name from static map or API fallback
  const regionNameInput = document.getElementById("region_name");
  const provinceNameInput = document.getElementById("province_name");
  const cityNameInput = document.getElementById("city_name");
  const barangayNameInput = document.getElementById("barangay_name");

  // Region name from static map first
  regionNameInput.value = regionNameMap[regionCode] || "Unknown Region";

  // Province Name
  if (regionCode && provinceCode) {
    const provinces = await fetchData(`${apiBase}/regions/${regionCode}/provinces/`);
    const province = provinces.find(p => p.code === provinceCode);
    provinceNameInput.value = province ? province.name : "Unknown Province";
  } else {
    provinceNameInput.value = "";
  }

  // City/Municipality Name
  if (provinceCode && cityCode) {
    const cities = await fetchData(`${apiBase}/provinces/${provinceCode}/cities-municipalities/`);
    const city = cities.find(c => c.code === cityCode);
    cityNameInput.value = city ? city.name : "Unknown City/Municipality";
  } else {
    cityNameInput.value = "";
  }

  // Barangay Name
  if (cityCode && barangayCode) {
    const barangays = await fetchData(`${apiBase}/cities-municipalities/${cityCode}/barangays/`);
    const barangay = barangays.find(b => b.code === barangayCode);
    barangayNameInput.value = barangay ? barangay.name : "Unknown Barangay";
  } else {
    barangayNameInput.value = "";
  }
});


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
  }

  // 3. Load and select City
  if (selectedProvince) {
    const cities = await fetchData(`${apiBase}/provinces/${selectedProvince}/cities-municipalities/`);
    populateSelect(citySelect, cities, "Select City/Municipality");
    citySelect.value = selectedCity;
  }

  // 4. Load and select Barangay
  if (selectedCity) {
    const barangays = await fetchData(`${apiBase}/cities-municipalities/${selectedCity}/barangays/`);
    populateSelect(barangaySelect, barangays, "Select Barangay");
    barangaySelect.value = selectedBarangay;
  }

  // Dynamic Change Handlers

  // On Region Change
  regionSelect.addEventListener("change", async () => {
    const regionCode = regionSelect.value;
    const provinces = await fetchData(`${apiBase}/regions/${regionCode}/provinces/`);
    populateSelect(provinceSelect, provinces, "Select Province");

    // Reset dependent selects
    citySelect.innerHTML = `<option value="" disabled selected>Select City/Municipality</option>`;
    barangaySelect.innerHTML = `<option value="" disabled selected>Select Barangay</option>`;
  });

  // On Province Change
  provinceSelect.addEventListener("change", async () => {
    const provinceCode = provinceSelect.value;
    const cities = await fetchData(`${apiBase}/provinces/${provinceCode}/cities-municipalities/`);
    populateSelect(citySelect, cities, "Select City/Municipality");

    barangaySelect.innerHTML = `<option value="" disabled selected>Select Barangay</option>`;
  });

  // On City Change
  citySelect.addEventListener("change", async () => {
    const cityCode = citySelect.value;
    const barangays = await fetchData(`${apiBase}/cities-municipalities/${cityCode}/barangays/`);
    populateSelect(barangaySelect, barangays, "Select Barangay");
  });
});
</script>

<!-- SCRIPTS -->
<script>
document.querySelector('.edit-button').addEventListener('click', function() {
    document.getElementById('editModal').style.display = 'block';
});

document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('editModal').style.display = 'none';
});

window.addEventListener('click', function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
});

const profilePicContainer = document.getElementById('profilePicContainer');
const fileInput = document.getElementById('profilePictureInput');

profilePicContainer.addEventListener('click', () => {
    fileInput.click();
});

fileInput.addEventListener('change', (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Optional: Preview the selected image instantly
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('profilePicImage').src = e.target.result;
    };
    reader.readAsDataURL(file);
});

</script>

<script>
  const yearSelect = document.querySelector('select[name="year_graduated"]');
  const currentYear = new Date().getFullYear();

 const selectedYear = @json($teacher['year_graduated'] ?? '');
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
      const selectedUniversity = @json($teacher['university'] ?? '');
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



<style>
    .modal {
        position: fixed;
        z-index: 1000;
        padding-top: 60px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
        background-color: #fff;
        margin: auto;
        padding: 20px;
        width: 90%;
        max-width: 500px;
        border-radius: 10px;
    }
    .close {
        float: right;
        font-size: 28px;
        cursor: pointer;
    }
    .modal-profile-pic-container {
        text-align: center;
        margin-bottom: 15px;
    }

.modal-profile-pic {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ccc;
}

.modal-profile-pic-container:hover .edit-icon {
    display: block;
}



.edit-icon {
    color: white;
    font-size: 18px;
    user-select: none;
}


</style>


