@php
    $formData = $form ?? [];

    // Helper function to check if URL is a PDF
    function isPdf($url) {
        return str_contains($url, '.pdf');
    }
@endphp

<!-- Modal -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form id="editProfileForm" method="POST" action="{{ route('mio.updateStudentProfile') }}" enctype="multipart/form-data">
            @csrf
            <!-- Centered profile picture -->
        <div class="modal-profile-pic-container" id="profilePicContainer" style="cursor:pointer; position: relative; display: inline-block;">

            <img src="{{ $student['photo_url'] ?? 'https://ui-avatars.com/api/?name='.$name }}"
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
                    <input type="text" name="fname" value="{{ $student['fname'] ?? '' }}" readonly>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lname" value="{{ $student['lname'] ?? '' }}" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="email" value="{{ $student['email'] ?? '' }}" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Biography</label>
                <textarea name="bio" rows="3">{{ $student['bio'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="form-row">
            <div class="form-group">
                <label>Social Link</label>
                <input type="url" name="social_link" value="{{ $student['social_link'] ?? '' }}" placeholder="https://facebook.com/yourprofile">
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
                        <img src="{{ $student['photo_url'] ?? 'https://ui-avatars.com/api/?name='.$name }}" alt="Profile Picture" />


                        <button class="edit-button">✎</button>
                    </div>

                    <div class="profile-info">

                       @php
                            $student_name = ($student['fname'] ?? '') . ' ' . ($student['lname'] ?? '');
                        @endphp

                        <h2>{{ $student_name ?: 'No Name' }}</h2>
                        <div class="section">
                            <h3>Biography</h3>
                            <p>{{ $student['bio'] ?? 'No biography available.' }}</p>
                        </div>
                        <div class="section">
                            <h3>Contact</h3>
                            <p><p>{{ $student['email'] ?? 'No email' }}</p></p>
                        </div>
                        <div class="section">
                            <h3>Social Links</h3>
                           <p><p>{{ $student['social_link'] ?? 'No Linked Social' }}</p></p>
                        </div>
                    </div>
                </div>

                </div>

            </div>

            <div class="form-container">
            <!-- Student Information Section -->
                <div class="section-header">Student Information</div>
                <div class="section-content">
                <label>Student Category<span style="color: red; font-weight:700; ">*</span></label>

                <div class="form-row" style="margin-left: 1rem;">
                    <div class="form-group">
                        <label>
                            <input type="radio" name="category" value="new" required
                                @if (isset($student['category']) && $student['category'] === 'new') checked @endif> New
                        </label>
                    </div>
                    <div class="form-group student-category">
                        <label>
                            <input type="radio" name="category" value="transfer"
                                @if (isset($student['category']) && $student['category'] === 'transfer') checked @endif> Transfer
                        </label>
                    </div>
                    <div class="form-group student-category">
                        <label>
                            <input type="radio" name="category" value="returning"
                                @if (isset($student['category']) && $student['category'] === 'returning') checked @endif> Returning
                        </label>
                    </div>
                    <div class="form-group student-category">
                        <label>
                            <input type="radio" name="category" value="international"
                                @if (isset($student['category']) && $student['category'] === 'international') checked @endif> International
                        </label>
                    </div>
                </div>
                <hr>
                <div class="form-group wide">
                    <label>Student ID <span style="color: red; font-weight:700">*</span></label>
                    <input type="text" name="studentid" id="studentID" value="{{ $student['studentid']  }}" readonly />
                    </div>
                    <div class="form-group">
                        <label for="section_id">Section</label>
                        <input type="text" name="section_id" id="section_id" class="form-control"
                            value="{{ $studentSection['section_name'] ?? 'None' }}" readonly />
                    </div>

                </div>



            <!-- Personal Information Section -->
            <div class="section-header">Personal Information</div>
            <div class="section-content">
            <div class="form-row">
                <div class="form-group">
                <label>First Name <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="first_name" value="{{ $student['fname'] }}" placeholder="First Name" readonly />
                </div>
                <div class="form-group">
                <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="last_name" value="{{ $student['lname'] }}" placeholder="Last Name" readonly />
                </div>
                <div class="form-group">
                <label>Gender <span style="color: red; font-weight:700">*</span></label>
                <select name="gender" disabled>
                    <option value="" disabled {{ !isset($student['gender']) ? 'selected' : '' }}>Select Gender</option>
                    <option value="Male" {{ isset($student['gender']) && $student['gender'] == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ isset($student['gender']) && $student['gender'] == 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ isset($student['gender']) && $student['gender'] == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

                <div class="form-group">
                <label>Age <span style="color: red; font-weight:700">*</span></label>
                <input type="number" name="age" id="age" value="{{ $student['age'] }}" placeholder="Age" required min="1" max="100" readonly />
                </div>
                <div class="form-group">
                <label>Birthday <span style="color: red; font-weight:700">*</span></label>
                <input type="date"
                id="birthday"
                name="birthday"
                min="1900-01-01"
                max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}"
                value="{{ $student['bday'] }}" readonly />
                </div>
            </div>

            <div class="form-row">

                <input type="hidden" id="selectedRegion" value="{{ $student['region'] }}" readonly>
                <input type="hidden" id="selectedProvince" value="{{ $student['province'] }}" readonly>
                <input type="hidden" id="selectedCity" value="{{ $student['city'] }}" readonly>
                <input type="hidden" id="selectedBarangay" value="{{ $student['barangay'] }}" readonly>


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
                    <input type="text" name="address" value="{{$student['address']}}" readonly />
                </div>

                <div class="form-group">
                    <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
                    <input type="number" name="zip_code" value="{{ $student['zip_code'] }}" minlength="4" maxlength="4" readonly />
                </div>
                </div>


            <div class="form-row">
                <div class="form-group">
                <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="contact_number" value="{{ $student['contact_number'] }}" readonly />
                </div>
                <div class="form-group wide">
                <label>Emergency Contact Number <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="emergency_contact" value="{{ $student['emergency_contact'] }}" readonly />
                </div>
                <div class="form-group">
                <label>Email <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="email" value="{{ $student['email'] }}" readonly />
                </div>
            </div>
            </div>

            <div class="section-header">Parent/Guardian Information</div>
                <div class="section-content">

                    <div class="form-row">
                        <div class="form-group wide">
                            <label>First Name <span style="color: red; font-weight:700">*</span></label>
                            <input type="text" name="parent_firstname" placeholder="First Name" readonly
                                value="{{ $form['parent_firstname'] ?? '' }}" />
                        </div>

                        <div class="form-group wide">
                            <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                            <input type="text" name="parent_lastname" placeholder="Last Name" readonly
                                value="{{ $form['parent_firstname'] ?? '' }}" />
                        </div>
                    </div>

                    <hr>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
                            <input type="text" name="emergency_contact" placeholder="Emergency Number" readonly
                                value="{{ $form['emergency_contact'] ?? '' }}" />
                        </div>
                        <div class="form-group">
                            <label>Parent Role <span style="color: red; font-weight:700">*</span></label>
                            <select name="parent_role" disabled>
                                <option value="" disabled {{ empty($form['parent_role']) ? 'selected' : '' }}>Select role</option>
                                <option value="father" {{ ($form['parent_role'] ?? '') == 'father' ? 'selected' : '' }}>Father</option>
                                <option value="mother" {{ ($form['parent_role'] ?? '') == 'mother' ? 'selected' : '' }}>Mother</option>
                                <option value="guardian" {{ ($form['parent_role'] ?? '') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="section-header">Academic Information</div>
                <div class="section-content">
                    <div class="form-row">
                        <div class="form-group wide">
                            <label>Previous School Attended <span style="color: red; font-weight:700">*</span></label>
                            <input type="text" name="previous_school" placeholder="Previous School" readonly
                                value="{{ $form['previous_school'] ?? '' }}" />
                        </div>
                        <div class="form-group">
                            <label>Previous Grade Level <span style="color: red; font-weight:700">*</span></label>
                            <input type="number" name="previous_grade_level" placeholder="Previous Grade Level" readonly
                                value="{{ $form['previous_grade_level'] ?? '' }}" />
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label>What are you enrolling in PID? <span style="color: red; font-weight:700">*</span></label>
                        <select name="enrollment_grade" id="enrollment_grade" disabled onchange="handleGradeChange()">
                            <option value="" disabled {{ empty($form['enrollment_grade']) ? 'selected' : '' }}>Select one</option>
                            <option value="kinder" {{ $form['enrollment_grade'] === 'kinder' ? 'selected' : '' }}>Kinder</option>
                            <option value="elementary" {{ $form['enrollment_grade'] === 'elementary' ? 'selected' : '' }}>Elementary</option>
                            <option value="junior-highschool" {{ $form['enrollment_grade'] === 'junior-highschool' ? 'selected' : '' }}>Junior High School</option>
                            <option value="senior-highschool" {{ $form['enrollment_grade'] === 'senior-highschool' ? 'selected' : '' }}>Senior High School</option>
                            <option value="one-on-one-therapy" {{ $form['enrollment_grade'] === 'one-on-one-therapy' ? 'selected' : '' }}>One-on-One Therapy</option>
                        </select>
                    </div>

                    <div class="form-group" id="grade_level_group" style="display: none;">
                        <label>Select Grade Level <span style="color: red; font-weight:700">*</span></label>
                        <select name="grade_level" id="grade_level">
                            <option value="" disabled {{ empty($form['grade_level']) ? 'selected' : '' }}>Select grade level</option>
                            @foreach (['7','8','9','10','11','12'] as $level)
                                <option value="{{ $level }}" {{ $form['grade_level'] == $level ? 'selected' : '' }}>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="strand_group" style="display: none;">
                        <label>Select Strand <span style="color: red; font-weight:700">*</span></label>
                        <select name="strand">
                            <option value="" disabled {{ empty($form['strand']) ? 'selected' : '' }}>Select strand</option>
                            @php
                                $strands = [
                                    'agri-fishery' => 'Agri-Fishery Arts',
                                    'home-economics' => 'Home Economics',
                                    'industrial-arts' => 'Industrial Arts',
                                    'ict' => 'Information, Communications, & Technology',
                                    'entrepreneurship' => 'Entrepreneurship & Financial Management',
                                    'culinary' => 'Culinary Skills Development',
                                    'fashion-beauty' => 'Fashion Beauty Skills'
                                ];
                            @endphp
                            @foreach ($strands as $key => $label)
                                <option value="{{ $key }}" {{ $form['strand'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


             <!-- Health Information Section -->
                    <div class="section-header">Health Information</div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Medical History <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="medical_history" placeholder="E.g. Asthma, Allergies, etc." value="{{ $form['medical_history'] ?? '' }}" readonly />
                            </div>
                            <div class="form-group">
                                <label>Type of Hearing Loss (if applicable)</label>
                                <input type="text" name="hearing_loss" placeholder="E.g. Sensorineural, Conductive, Mixed" value="{{ $form['hearing_loss'] ?? '' }}" readonly/>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Do you identify as? <span style="color: red; font-weight:700">*</span></label>
                                <select name="hearing_identity" disabled>
                                    <option value="" disabled {{ empty($form['hearing_identity']) ? 'selected' : '' }}>Select one</option>
                                    <option value="deaf" {{ $form['hearing_identity'] === 'deaf' ? 'selected' : '' }}>Deaf</option>
                                    <option value="hard-of-hearing" {{ $form['hearing_identity'] === 'hard-of-hearing' ? 'selected' : '' }}>Hard of Hearing</option>
                                    <option value="speech-delay" {{ $form['hearing_identity'] === 'speech-delay' ? 'selected' : '' }}>Speech Delay</option>
                                    <option value="none" {{ $form['hearing_identity'] === 'none' ? 'selected' : '' }}>Neither</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Assistive Devices Used</label>
                                <input type="text" name="assistive_devices" placeholder="E.g. Hearing Aid, Cochlear Implant, None" value="{{ $form['assistive_devices'] ?? '' }}" readonly/>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Other Notes or Health Concerns</label>
                                <input type="text" name="health_notes" placeholder="Specify any other relevant information" value="{{ $form['health_notes'] ?? '' }}" readonly/>
                            </div>
                        </div>
                    </div>


                    <!-- Payment Section -->
                    <div class="section-header">Proof of Payment Upload</div>
                    <div class="section-content">
                        {{-- Payment Proof --}}
                        @if (!empty($form['payment_proof_path']))
                        <div class="form-group">
                            <label>Payment Proof</label>
                            <div class="review-toggle" data-target="payment-proof-preview">
                                <button type="button" class="review-btn btn btn-primary btn-sm" >Review</button>
                                <button type="button" class="hide-btn btn btn-secondary btn-sm d-none" >Hide</button>
                            </div>
                            <div id="payment-proof-preview" class="file-preview-area d-none mt-2">

                                @if (isPdf($form['payment_proof_path']))
                                    {{-- PDF file: show icon and link --}}
                                    <a href="{{ $form['payment_proof_path'] }}" target="_blank" style="display:flex; align-items:center; gap:0.5rem;">
                                        <img src="{{ asset('pdf-icon.png') }}" alt="PDF Icon" style="width: 40px; height: auto;">
                                        <span>Open PDF Document</span>
                                    </a>
                                    {{-- Optionally embed PDF --}}
                                    {{--
                                    <embed src="{{ $form['payment_proof_path'] }}" type="application/pdf" width="100%" height="300px" />
                                    --}}
                                @else
                                    {{-- Image file: show preview --}}
                                    <img src="{{ $form['payment_proof_path'] }}" class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                                @endif

                            </div>
                        </div>
                    @endif
                    </div>

                    <!-- File Upload Section -->
                    <div class="section-header">File Upload</div>
                    <div class="section-content">

                        {{-- Good Moral --}}
                        @if (!empty($form['good_moral_path']))
                            <div class="form-group">
                                <label>Good Moral</label>
                                <div class="review-toggle" data-target="good-moral-preview">
                                    <button type="button" class="review-btn btn btn-primary btn-sm">Review</button>
                                    <button type="button" class="hide-btn btn btn-secondary btn-sm d-none">Hide</button>
                                </div>
                                <div id="good-moral-preview" class="file-preview-area d-none mt-2" style="max-height: 600px;">
                                    <iframe src="{{ $form['good_moral_path'] }}" style="width:100%; height:600px;" frameborder="0"></iframe>
                                </div>
                            </div>
                        @endif

                        {{-- Health Certificate --}}
                        @if (!empty($form['health_certificate_path']))
                            <div class="form-group">
                                <label>Health Certificate</label>
                                <div class="review-toggle" data-target="health-preview">
                                    <button type="button" class="review-btn btn btn-primary btn-sm">Review</button>
                                    <button type="button" class="hide-btn btn btn-secondary btn-sm d-none">Hide</button>
                                </div>
                                <div id="health-preview" class="file-preview-area d-none mt-2" style="max-height: 600px;">
                                    <iframe src="{{ $form['health_certificate_path'] }}" style="width:100%; height:600px;" frameborder="0"></iframe>
                                </div>
                            </div>
                        @endif

                        {{-- PSA Birth Certificate --}}
                        @if (!empty($form['psa_birth_certificate_path']))
                            <div class="form-group">
                                <label>PSA Birth Certificate</label>
                                <div class="review-toggle" data-target="psa-preview">
                                    <button type="button" class="review-btn btn btn-primary btn-sm">Review</button>
                                    <button type="button" class="hide-btn btn btn-secondary btn-sm d-none">Hide</button>
                                </div>
                                <div id="psa-preview" class="file-preview-area d-none mt-2" style="max-height: 600px;">
                                    <iframe src="{{ $form['psa_birth_certificate_path'] }}" style="width:100%; height:600px;" frameborder="0"></iframe>
                                </div>
                            </div>
                        @endif

                        {{-- Form 137 --}}
                        @if (!empty($form['form_137_path']))
                            <div class="form-group">
                                <label>PSA Birth Certificate</label>
                                <div class="review-toggle" data-target="137-preview">
                                    <button type="button" class="review-btn btn btn-primary btn-sm">Review</button>
                                    <button type="button" class="hide-btn btn btn-secondary btn-sm d-none">Hide</button>
                                </div>
                                <div id="137-preview" class="file-preview-area d-none mt-2" style="max-height: 600px;">
                                    <iframe src="{{ $form['form_137_path'] }}" style="width:100%; height:600px;" frameborder="0"></iframe>
                                </div>
                            </div>
                        @endif

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

 const selectedYear = @json($student['year_graduated'] ?? '');
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
      const selectedUniversity = @json($student['university'] ?? '');
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

<!-- REVIEW FILE -->
<script>
document.querySelectorAll('.review-toggle').forEach(toggle => {
    const targetId = toggle.dataset.target;
    const previewArea = document.getElementById(targetId);
    const reviewBtn = toggle.querySelector('.review-btn');
    const hideBtn = toggle.querySelector('.hide-btn');

    reviewBtn.addEventListener('click', () => {
        previewArea.classList.remove('d-none');
        reviewBtn.classList.add('d-none');
        hideBtn.classList.remove('d-none');
    });

    hideBtn.addEventListener('click', () => {
        previewArea.classList.add('d-none');
        reviewBtn.classList.remove('d-none');
        hideBtn.classList.add('d-none');
    });
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


