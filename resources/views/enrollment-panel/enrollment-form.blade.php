
@php
    $formData = $form ?? [];

    // Helper function to check if URL is a PDF
    function isPdf($url) {
        return str_contains($url, '.pdf');
    }
@endphp

<section class="home-section">
    <div class="text" style="margin-top: 2rem">
        Enrollment Form
    </div>
    <div class="dashboard-grid">
    <!-- FORM -->

    <div class="teacher-container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

    @if ($status === 'NotStarted')

        <form action="{{ route('enrollment.submit') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-container">
                    <!-- Personal Information Section -->
                        <div class="section-header">Personal Information</div>
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
                                    <input type="number" id="age" name="age" placeholder="Age" required />
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
                                    <label> Contact Number <span style="color: red; font-weight:700">*</span></label>
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

                        </div>

                        <div class="section-header">Parent/Guardian Information</div>
                        <div class="section-content">

                         <div class="form-row">
                            <div class="form-group wide">
                                <label>First Name <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="parent_firstname" placeholder="Parent First Name" required />
                            </div>

                            <div class="form-group wide">
                                <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="parent_lastname" placeholder="Parent Last Name" required />
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

                        <!-- Academic Information Section -->
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

                        <!-- Health Information Section -->
                        <div class="section-header">Health Information</div>
                        <div class="section-content">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Medical History <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="medical_history" placeholder="E.g. Asthma, Allergies, etc." required />
                                </div>
                                <div class="form-group">
                                    <label>Type of Hearing Loss (if applicable)</label>
                                    <input type="text" name="hearing_loss" placeholder="E.g. Sensorineural, Conductive, Mixed" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Do you identify as? <span style="color: red; font-weight:700">*</span></label>
                                    <select name="hearing_identity" required>
                                        <option value="" disabled selected>Select one</option>
                                        <option value="deaf" >Deaf</option>
                                        <option value="hard-of-hearing">Hard of Hearing</option>
                                        <option value="speech-delay">Speech Delay</option>
                                        <option value="none">Neither</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Assistive Devices Used</label>
                                    <input type="text" name="assistive_devices" placeholder="E.g. Hearing Aid, Cochlear Implant, None"/>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Other Notes or Health Concerns</label>
                                    <input type="text" name="health_notes" placeholder="Specify any other relevant information" />
                                </div>
                            </div>
                        </div>

                        <!-- Payment Section -->
                        <div class="section-header">Proof of Payment Upload</div>
                            <div class="section-content">
                        <div class="form-row">
                                <div class="form-group wide">
                                    <label>Proof of Payment <span style="color: red; font-weight:700">*</span></label>
                                    <div class="file-upload-box drop-area">
                                        <input type="file" name="payment" class="fileInput" hidden>
                                    </div>
                                    <div class="file-preview"></div>
                                </div>
                            </div>


                            </div>

                        <!-- File Upload Section -->
                        <div class="section-header">File Upload</div>
                        <div class="section-content">
                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Good Moral <span style="color: red; font-weight:700">*</span></label>
                                    <div class="file-upload-box drop-area">
                                        <input type="file" name="good_moral_file" class="fileInput" hidden>
                                    </div>
                                    <div class="file-preview"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Health Certificate <span style="color: red; font-weight:700">*</span></label>
                                    <div class="file-upload-box drop-area">
                                        <input type="file" name="health_certificate_file" class="fileInput" hidden>
                                    </div>
                                    <div class="file-preview"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>PSA Birth Certificate <span style="color: red; font-weight:700">*</span></label>
                                    <div class="file-upload-box drop-area">
                                        <input type="file" name="psa_birth_certificate_file" class="fileInput" hidden>
                                    </div>
                                    <div class="file-preview"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Form 137 <span style="color: red; font-weight:700">*</span></label>
                                    <div class="file-upload-box drop-area">
                                        <input type="file" name="form_137_file" class="fileInput" hidden>
                                    </div>
                                    <div class="file-preview"></div>
                                </div>
                            </div>
                        </div>


                    <!-- Submit Button -->
                    <div class="form-footer">
                        <button type="submit">Submit Application</button>
                    </div>
            </div>
        </form>

    @elseif($status === 'Registered' || $status === 'Assessment' || $status === 'Enrolled' || $status === 'Qualified')

        <div class="form-container">
            <!-- Personal Information Section -->
            <div class="section-header">Personal Information</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="{{ $form['first_name'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="{{ $form['last_name'] ?? '' }}" readonly />
                    </div>
                </div>

                <hr>

                <div class="form-row">

                    <div class="form-group">
                        <label>Gender</label>
                        <input type="text" name="gender" value="{{ $form['gender'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" id="age" name="age" value="{{ $form['age'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Birthday</label>
                        <input type="date" name="birthday" value="{{ $form['birthday'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group">
                        <label> Contact Number <span style="color: red; font-weight:700">*</span></label>
                        <input type="text" name="contact_number" placeholder="Contact Number" required
                            value="{{ old('contact_number', $form['contact_number'] ?? '') }}" readonly />
                    </div>
                </div>

                <hr>

               <!-- ADDRESSES -->
                <input type="hidden" id="regionCode" value="{{ $form['region'] ?? '' }}">
                <input type="hidden" id="provinceCode" value="{{ $form['province'] ?? '' }}">
                <input type="hidden" id="cityCode" value="{{ $form['city'] ?? '' }}">
                <input type="hidden" id="barangayCode" value="{{ $form['barangay'] ?? '' }}">

                <div class="form-row">

                    <div class="form-group wide">
                        <label for="region">Region</label>
                        <input type="text" id="regionName" readonly />
                    </div>

                    <div class="form-group wide">
                        <label>Province</label>
                        <input type="text" id="provinceName" readonly />
                    </div>

                     <div class="form-group wide">
                        <label>City</label>
                        <input type="text" id="cityName" readonly />
                    </div>

                    <div class="form-group wide">
                        <label>Barangay</label>
                        <input type="text" id="barangayName" readonly />
                    </div>



                </div>

                <div class="form-row">
                    <div class="form-group wide">
                        <label>Street Name, Building, House No.</label>
                        <input type="text" name="street" value="{{ $form['address'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group wide">
                        <label>Zip Code</label>
                        <input type="number" name="zip_code" value="{{ $form['zip_code'] ?? '' }}" readonly />
                    </div>
                </div>


                <hr>
                </div>

            <div class="section-header">Parent Information</div>
            <div class="section-content">
                <div class="form-row">

                    <div class="form-group wide">
                        <label>First Name <span style="color: red; font-weight:700">*</span></label>
                        <input type="text" name="parent_firstname" value="{{ old('parent_firstname', $form['parent_firstname'] ?? '') }}" readonly />
                    </div>

                    <div class="form-group wide">
                        <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                        <input type="text" name="parent_lastname" placeholder="Parent Last Name" value="{{ old('parent_lastname', $form['parent_lastname'] ?? '') }}" readonly />
                    </div>

                </div>

                 <div class="form-row">

                    <div class="form-group">
                        <label>Parent/Guardian Contact Number <span style="color: red; font-weight:700">*</span></label>
                        <input type="text" name="emergency_contact" placeholder="Emergency Number" required
                            value="{{ old('emergency_contact', $form['emergency_contact'] ?? '') }}" readonly />
                    </div>

                    <div class="form-group">
                        <label>Parent Role <span style="color: red; font-weight:700">*</span></label>
                        <select name="parent_role" disabled>
                            <option value="" disabled {{ old('parent_role', '') == '' ? 'selected' : '' }}>Select role</option>
                            <option value="father" {{ old('parent_role', $form['parent_role'] ?? '') == 'father' ? 'selected' : '' }}>Father</option>
                            <option value="mother" {{ old('parent_role', $data['enrollment_form']['parent_role'] ?? '') == 'mother' ? 'selected' : '' }}>Mother</option>
                            <option value="guardian" {{ old('parent_role', $data['enrollment_form']['parent_role'] ?? '') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Academic Info -->
            <div class="section-header">Academic Information</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-group wide">
                        <label>Previous School Attended</label>
                        <input type="text" name="previous_school" value="{{ $form['previous_school'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Previous Grade Level</label>
                        <input type="number" name="previous_grade_level" value="{{ $form['previous_grade_level'] ?? '' }}" readonly />
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label>What are you enrolling in PID? <span style="color: red; font-weight:700">*</span></label>
                    <select name="enrollment_grade" id="enrollment_grade" required onchange="handleGradeChange()" disabled>
                        <option value="" disabled {{ empty($form['enrollment_grade']) ? 'selected' : '' }}>Select one</option>
                        <option value="kinder" {{ $form['enrollment_grade'] === 'kinder' ? 'selected' : '' }}>Kinder</option>
                        <option value="elementary" {{ $form['enrollment_grade'] === 'elementary' ? 'selected' : '' }}>Elementary</option>
                        <option value="junior-highschool" {{ $form['enrollment_grade'] === 'junior-highschool' ? 'selected' : '' }}>Junior High School</option>
                        <option value="senior-highschool" {{ $form['enrollment_grade'] === 'senior-highschool' ? 'selected' : '' }}>Senior High School</option>
                        <option value="one-on-one-therapy" {{ $form['enrollment_grade'] === 'one-on-one-therapy' ? 'selected' : '' }}>One-on-One Therapy</option>
                    </select>
                </div>

                <!-- Grade Level Selector (Visible if enrollment_grade is appropriate) -->
                <div class="form-group" id="grade_level_group" style="display: block;">
                    <label>Select Grade Level <span style="color: red; font-weight:700">*</span></label>
                    <input type="text" name="grade_level" id="grade_level" value="{{ $form['grade_level'] ?? '' }}" readonly />
                </div>

                <!-- Senior High School Strand Selector -->
                <div class="form-group" id="strand_group" style="display: {{ $form['enrollment_grade'] === 'senior-highschool' ? 'block' : 'none' }};">
                    <label>Select Strand <span style="color: red; font-weight:700">*</span></label>
                    <select name="strand" disabled>
                        <option value="" disabled {{ empty($form['strand']) ? 'selected' : '' }}>Select strand</option>
                        <option value="agri-fishery" {{ $form['strand'] === 'agri-fishery' ? 'selected' : '' }}>Agri-Fishery Arts</option>
                        <option value="home-economics" {{ $form['strand'] === 'home-economics' ? 'selected' : '' }}>Home Economics</option>
                        <option value="industrial-arts" {{ $form['strand'] === 'industrial-arts' ? 'selected' : '' }}>Industrial Arts</option>
                        <option value="ict" {{ $form['strand'] === 'ict' ? 'selected' : '' }}>Information, Communications, & Technology</option>
                        <option value="entrepreneurship" {{ $form['strand'] === 'entrepreneurship' ? 'selected' : '' }}>Entrepreneurship & Financial Management</option>
                        <option value="culinary" {{ $form['strand'] === 'culinary' ? 'selected' : '' }}>Culinary Skills Development</option>
                        <option value="fashion-beauty" {{ $form['strand'] === 'fashion-beauty' ? 'selected' : '' }}>Fashion Beauty Skills</option>
                    </select>
                </div>
            </div>


            <!-- Health Info -->
            <div class="section-header">Health Information</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-group">
                        <label>Medical History <span style="color: red; font-weight:700">*</span></label>
                        <input type="text" name="medical_history" placeholder="E.g. Asthma, Allergies, etc."
                            value="{{ $form['medical_history'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Type of Hearing Loss (if applicable)</label>
                        <input type="text" name="hearing_loss" placeholder="E.g. Sensorineural, Conductive, Mixed"
                            value="{{ $form['hearing_loss'] ?? '' }}" readonly />
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
                        <input type="text" name="assistive_devices" placeholder="E.g. Hearing Aid, Cochlear Implant, None"
                            value="{{ $form['assistive_devices'] ?? '' }}" readonly />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group wide">
                        <label>Other Notes or Health Concerns</label>
                        <input type="text" name="health_notes" placeholder="Specify any other relevant information"
                            value="{{ $form['health_notes'] ?? '' }}" readonly />
                    </div>
                </div>
            </div>


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

    @elseif ($status === 'Revision')

        <form action="{{ route('enrollment.submit') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-container">

                    <!-- Personal Information Section -->
        <div class="section-header">Personal Information</div>
            <div class="section-content">
                <div class="form-row">
                        <div class="form-group">
                            <label>First Name <span style="color: red; font-weight:700">*</span></label>
                            <input type="text" name="first_name" placeholder="First Name" value="{{ $form['first_name'] ?? '' }}" required />
                        </div>
                        <div class="form-group">
                            <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                            <input type="text" name="last_name" placeholder="Last Name" value="{{ $form['last_name'] ?? '' }}" required />
                        </div>
                </div>

                <hr>

                <div class="form-row">
                        <div class="form-group">
                            <label>Gender <span style="color: red; font-weight:700">*</span></label>
                            <select name="gender" required>
                                <option value="" disabled {{ empty($form['gender']) ? 'selected' : '' }}>Select Gender</option>
                                <option value="Male" {{ ($form['gender'] ?? '') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ ($form['gender'] ?? '') === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ ($form['gender'] ?? '') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Age <span style="color: red; font-weight:700">*</span></label>
                            <input type="number" id="age" name="age" placeholder="Age" value="{{ $form['age'] ?? '' }}" required />
                        </div>
                        <div class="form-group">
                            <label>Birthday <span style="color: red; font-weight:700;">*</span></label>
                            <input
                                type="date"
                                id="birthday"
                                name="birthday"
                                value="{{ $form['birthday'] ?? '' }}"
                                required
                                min="1900-01-01"
                                max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                            />
                        </div>
                          <div class="form-group">
                                    <label> Contact Number <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="contact_number" placeholder="Contact Number" value="{{ $form['contact_number']}}" required />
                                </div>
                </div>

                <hr>
                <div class="form-row">
                <div class="form-group">
                    <label for="region">Region <span style="color: red; font-weight:700">*</span></label>
                    <select name="region" id="region" required></select>
                </div>

                <div class="form-group">
                    <label for="province">Province <span style="color: red; font-weight:700">*</span></label>
                    <select name="province" id="province" required disabled></select>
                </div>
                <div class="form-group">
                    <label for="city">City/Municipality <span style="color: red; font-weight:700">*</span></label>
                    <select name="city" id="city" required disabled></select>
                </div>

                <div class="form-group">
                    <label for="barangay">Barangay <span style="color: red; font-weight:700">*</span></label>
                    <select name="barangay" id="barangay" required disabled></select>
                </div>
            </div>

                            <div class="form-row">
                            <div class="form-group wide">
                                <label>Building/House No., Street <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="address" placeholder="Home Address" value="{{ $form['address'] }}" required />
                                </div>

                                <div class="form-group">
                                <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
                                <input type="number" name="zip_code" placeholder="Zip Code" minlength="4" value="{{ $form['zip_code']}}" maxlength="4" required />
                                </div>
                            </div>

                            <hr>

                    </div>

                    <div class="section-header">Parent Information</div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group wide">
                                <label>First Name <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="parent_firstname" value="{{ $form['emergency_nparent_firstname']}}" placeholder="First Name" required />

                                <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="parent_lastname" value="{{ $form['parent_lastname']}}" placeholder="Last Name" required />
                            </div>

                        </div>

                            <div class="form-row">
                            <div class="form-group">
                                <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
                                <input type="text"   name="emergency_contact" value="{{ $form['emergency_contact']}}" placeholder="Emergency Number"  required />
                            </div>
                            <div class="form-group">
                            <label>Parent Role <span style="color: red; font-weight:700">*</span></label>
                            <select name="parent_role" required>
                                <option value="" disabled {{ empty($form['parent_role']) ? 'selected' : '' }}>Select role</option>
                                <option value="father" {{ (isset($form['parent_role']) && $form['parent_role'] === 'father') ? 'selected' : '' }}>Father</option>
                                <option value="mother" {{ (isset($form['parent_role']) && $form['parent_role'] === 'mother') ? 'selected' : '' }}>Mother</option>
                                <option value="guardian" {{ (isset($form['parent_role']) && $form['parent_role'] === 'guardian') ? 'selected' : '' }}>Guardian</option>
                            </select>
                        </div>

                        </div>
                    </div>

                    <!-- Academic Information Section -->
                    <div class="section-header">Academic Information</div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Previous School Attended <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="previous_school" placeholder="Previous School" value="{{ $form['previous_school'] ?? '' }}" required />
                            </div>
                            <div class="form-group">
                                <label>Previous Grade Level <span style="color: red; font-weight:700">*</span></label>
                                <input type="number" name="previous_grade_level" placeholder="Previous Grade Level" value="{{ $form['previous_grade_level'] ?? '' }}" required />
                            </div>
                        </div>
                        <hr>

                        <div class="form-group">
                            <label>What are you enrolling in PID? <span style="color: red; font-weight:700">*</span></label>
                            <select name="enrollment_grade" id="enrollment_grade" required onchange="handleGradeChange()">
                                <option value="" disabled {{ empty($form['enrollment_grade']) ? 'selected' : '' }}>Select one</option>
                                <option value="kinder" {{ $form['enrollment_grade'] === 'kinder' ? 'selected' : '' }}>Kinder</option>
                                <option value="elementary" {{ $form['enrollment_grade'] === 'elementary' ? 'selected' : '' }}>Elementary</option>
                                <option value="junior-highschool" {{ $form['enrollment_grade'] === 'junior-highschool' ? 'selected' : '' }}>Junior High School</option>
                                <option value="senior-highschool" {{ $form['enrollment_grade'] === 'senior-highschool' ? 'selected' : '' }}>Senior High School</option>
                                <option value="one-on-one-therapy" {{ $form['enrollment_grade'] === 'one-on-one-therapy' ? 'selected' : '' }}>One-on-One Therapy</option>
                            </select>
                        </div>

                        <!-- Grade Level Selector -->
                        <div class="form-group" id="grade_level_group" style="display: none;">
                            <label>Select Grade Level <span style="color: red; font-weight:700">*</span></label>
                            <select name="grade_level" id="grade_level">
                                <option value="" disabled {{ empty($form['grade_level']) ? 'selected' : '' }}>Select grade level</option>
                                <!-- Options will be populated dynamically in JS -->
                            </select>
                        </div>

                        <!-- SHS Strand Selector -->
                        <div class="form-group" id="strand_group" style="display: none;">
                            <label>Select Strand <span style="color: red; font-weight:700">*</span></label>
                            <select name="strand">
                                <option value="" disabled {{ empty($form['strand']) ? 'selected' : '' }}>Select strand</option>
                                <option value="agri-fishery" {{ $form['strand'] === 'agri-fishery' ? 'selected' : '' }}>Agri-Fishery Arts</option>
                                <option value="home-economics" {{ $form['strand'] === 'home-economics' ? 'selected' : '' }}>Home Economics</option>
                                <option value="industrial-arts" {{ $form['strand'] === 'industrial-arts' ? 'selected' : '' }}>Industrial Arts</option>
                                <option value="ict" {{ $form['strand'] === 'ict' ? 'selected' : '' }}>Information, Communications, & Technology</option>
                                <option value="entrepreneurship" {{ $form['strand'] === 'entrepreneurship' ? 'selected' : '' }}>Entrepreneurship & Financial Management</option>
                                <option value="culinary" {{ $form['strand'] === 'culinary' ? 'selected' : '' }}>Culinary Skills Development</option>
                                <option value="fashion-beauty" {{ $form['strand'] === 'fashion-beauty' ? 'selected' : '' }}>Fashion Beauty Skills</option>
                            </select>
                        </div>
                    </div>


                    <!-- Health Information Section -->
                    <div class="section-header">Health Information</div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Medical History <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="medical_history" placeholder="E.g. Asthma, Allergies, etc." value="{{ $form['medical_history'] ?? '' }}" required />
                            </div>
                            <div class="form-group">
                                <label>Type of Hearing Loss (if applicable)</label>
                                <input type="text" name="hearing_loss" placeholder="E.g. Sensorineural, Conductive, Mixed" value="{{ $form['hearing_loss'] ?? '' }}" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Do you identify as? <span style="color: red; font-weight:700">*</span></label>
                                <select name="hearing_identity" required>
                                    <option value="" disabled {{ empty($form['hearing_identity']) ? 'selected' : '' }}>Select one</option>
                                    <option value="deaf" {{ $form['hearing_identity'] === 'deaf' ? 'selected' : '' }}>Deaf</option>
                                    <option value="hard-of-hearing" {{ $form['hearing_identity'] === 'hard-of-hearing' ? 'selected' : '' }}>Hard of Hearing</option>
                                    <option value="speech-delay" {{ $form['hearing_identity'] === 'speech-delay' ? 'selected' : '' }}>Speech Delay</option>
                                    <option value="none" {{ $form['hearing_identity'] === 'none' ? 'selected' : '' }}>Neither</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Assistive Devices Used</label>
                                <input type="text" name="assistive_devices" placeholder="E.g. Hearing Aid, Cochlear Implant, None" value="{{ $form['assistive_devices'] ?? '' }}" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Other Notes or Health Concerns</label>
                                <input type="text" name="health_notes" placeholder="Specify any other relevant information" value="{{ $form['health_notes'] ?? '' }}" />
                            </div>
                        </div>
                    </div>


                    <!-- Payment Section -->
                    <div class="section-header">Proof of Payment Upload</div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Proof of Payment <span style="color: red; font-weight:700">*</span></label>
                                <div class="file-upload-box drop-area">
                                    <input type="file" name="payment" class="fileInput" hidden>
                                </div>
                                <div class="file-preview">
                                    @if (!empty($form['payment_proof_path']))
                                        <a href="{{ $form['payment_proof_path'] }}" target="_blank">View Uploaded File</a>
                                        <input type="hidden" name="existing_payment" value="{{ $form['payment_proof_path'] }}">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Upload Section -->
                    <div class="section-header">File Upload</div>
                    <div class="section-content">

                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Good Moral <span style="color: red; font-weight:700">*</span></label>
                                <div class="file-upload-box drop-area">
                                    <input type="file" name="good_moral_file" class="fileInput" hidden>
                                </div>
                                <div class="file-preview">
                                    @if (!empty($form['good_moral_path']))
                                        <a href="{{ $form['good_moral_path'] }}" target="_blank">View Uploaded File</a>
                                        <input type="hidden" name="existing_good_moral_file" value="{{ $form['good_moral_path'] }}">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Health Certificate <span style="color: red; font-weight:700">*</span></label>
                                <div class="file-upload-box drop-area">
                                    <input type="file" name="health_certificate_file" class="fileInput" hidden>
                                </div>
                                <div class="file-preview">
                                    @if (!empty($form['health_certificate_path']))
                                        <a href="{{ $form['health_certificate_path'] }}" target="_blank">View Uploaded File</a>
                                        <input type="hidden" name="existing_health_certificate_file" value="{{ $form['health_certificate_path'] }}">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group wide">
                                <label>PSA Birth Certificate <span style="color: red; font-weight:700">*</span></label>
                                <div class="file-upload-box drop-area">
                                    <input type="file" name="psa_birth_certificate_file" class="fileInput" hidden>
                                </div>
                                <div class="file-preview">
                                    @if (!empty($form['psa_birth_certificate_path']))
                                        <a href="{{ $form['psa_birth_certificate_path'] }}" target="_blank">View Uploaded File</a>
                                        <input type="hidden" name="existing_psa_birth_certificate_file" value="{{ $form['psa_birth_certificate_path'] }}">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Form 137 <span style="color: red; font-weight:700">*</span></label>
                                <div class="file-upload-box drop-area">
                                    <input type="file" name="form_137_file" class="fileInput" hidden>
                                </div>
                                <div class="file-preview">
                                    @if (!empty($form['form_137_path']))
                                        <a href="{{ $form['form_137_path'] }}" target="_blank">View Uploaded File</a>
                                        <input type="hidden" name="existing_form_137_file" value="{{ $form['form_137_path'] }}">
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div class="form-footer">
                        <button type="submit">Submit Application</button>
                    </div>
            </div>
        </form>

    @endif
    </div>


</section>


<!-- ------- SCRIPTS ------- -->

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

<script>
    const selectedRegion = @json($form['region'] ?? '');
    const selectedProvince = @json($form['province'] ?? '');
    const selectedCity = @json($form['city'] ?? '');
    const selectedBarangay = @json($form['barangay'] ?? '');
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

    function populateSelect(selectEl, data, placeholder, customMap = null, selectedValue = '') {
        selectEl.innerHTML = `<option value="" disabled ${selectedValue === '' ? 'selected' : ''}>${placeholder}</option>`;
        data.forEach(item => {
            const option = document.createElement("option");
            option.value = item.code;
            option.text = customMap ? customMap[item.code] || item.name : item.name;
            if (item.code === selectedValue) {
                option.selected = true;
            }
            selectEl.appendChild(option);
        });
        selectEl.disabled = false;
    }


    document.addEventListener('DOMContentLoaded', async () => {
        const regionSelect = document.getElementById("region");
        const provinceSelect = document.getElementById("province");
        const citySelect = document.getElementById("city");
        const barangaySelect = document.getElementById("barangay");

        // Load regions
        const regions = await fetchData(`${apiBase}/regions/`);
        populateSelect(regionSelect, regions, "Select Region", regionNameMap, selectedRegion);

        // Load provinces if region is selected
        if (selectedRegion) {
            const provinces = await fetchData(`${apiBase}/regions/${selectedRegion}/provinces/`);
            populateSelect(provinceSelect, provinces, "Select Province", null, selectedProvince);
            provinceSelect.disabled = false;

            // Load cities
            if (selectedProvince) {
                const cities = await fetchData(`${apiBase}/provinces/${selectedProvince}/cities-municipalities/`);
                populateSelect(citySelect, cities, "Select City/Municipality", null, selectedCity);
                citySelect.disabled = false;

                // Load barangays
                if (selectedCity) {
                    const barangays = await fetchData(`${apiBase}/cities-municipalities/${selectedCity}/barangays/`);
                    populateSelect(barangaySelect, barangays, "Select Barangay", null, selectedBarangay);
                    barangaySelect.disabled = false;
                }
            }
        }

        // Event listeners
        regionSelect.addEventListener("change", async () => {
            provinceSelect.innerHTML = '';
            citySelect.innerHTML = '';
            barangaySelect.innerHTML = '';
            provinceSelect.disabled = citySelect.disabled = barangaySelect.disabled = true;

            const selectedRegion = regionSelect.value;

            const provinces = await fetchData(`${apiBase}/regions/${selectedRegion}/provinces/`);

            if (provinces.length === 0) {
                provinceSelect.innerHTML = `<option value="" disabled selected>Not Applicable</option>`;
                provinceSelect.disabled = true;

                const cities = await fetchData(`${apiBase}/regions/${selectedRegion}/cities-municipalities/`);
                populateSelect(citySelect, cities, "Select City/Municipality");
            } else {
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

    async function fetchName(url) {
    try {
        const res = await fetch(url);
        const data = await res.json();
        return data.name || '';
    } catch (e) {
        console.error("Fetch failed:", url, e);
        return '';
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    const regionCode = document.getElementById("regionCode").value;
    const provinceCode = document.getElementById("provinceCode").value;
    const cityCode = document.getElementById("cityCode").value;
    const barangayCode = document.getElementById("barangayCode").value;

    // Region
    document.getElementById("regionName").value = regionNameMap[regionCode] || '';

    // Province
    if (provinceCode) {
        const provinceName = await fetchName(`${apiBase}/provinces/${provinceCode}/`);
        document.getElementById("provinceName").value = provinceName;
    }

    // City
    if (cityCode) {
        const cityName = await fetchName(`${apiBase}/cities-municipalities/${cityCode}/`);
        document.getElementById("cityName").value = cityName;
    }

    // Barangay
    if (barangayCode) {
        const barangayName = await fetchName(`${apiBase}/barangays/${barangayCode}/`);
        document.getElementById("barangayName").value = barangayName;
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Select all drop areas and file inputs
    const dropAreas = document.querySelectorAll(".drop-area");

    dropAreas.forEach(dropArea => {
        const fileInput = dropArea.querySelector(".fileInput");
        const filePreview = dropArea.parentElement.querySelector(".file-preview");
        let uploadedFiles = [];

        dropArea.addEventListener("dragover", (e) => {
            e.preventDefault();
            dropArea.style.background = "#e0e0e0";
        });

        dropArea.addEventListener("dragleave", () => {
            dropArea.style.background = "#f0f0f0";
        });

        dropArea.addEventListener("drop", (e) => {
            e.preventDefault();
            dropArea.style.background = "#f0f0f0";
            handleFiles(e.dataTransfer.files);
        });

        dropArea.addEventListener("click", () => {
            fileInput.click();
        });

        fileInput.addEventListener("change", (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            if (files.length > 0) {
                // Ensure the first file is assigned to the actual file input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);  // Only allow 1 file
                fileInput.files = dataTransfer.files;

                uploadedFiles = [files[0]]; // Only keep the one assigned
                updatePreview();
            }
        }


        function updatePreview() {
            filePreview.innerHTML = "";
            uploadedFiles.forEach((file, index) => {
                const fileItem = document.createElement("div");
                fileItem.classList.add("preview-item");
                fileItem.innerHTML = `
                    <span>📄 ${file.name}</span>
                    <button class="remove-btn" data-index="${index}">❌</button>
                `;
                filePreview.appendChild(fileItem);
            });

            // Attach remove listeners
            filePreview.querySelectorAll(".remove-btn").forEach(btn => {
                btn.addEventListener("click", function () {
                    const index = this.getAttribute("data-index");
                    uploadedFiles.splice(index, 1);
                    updatePreview();
                });
            });
        }
    });
});

document.querySelector('form').addEventListener('submit', function (e) {
    const inputs = document.querySelectorAll('input[type="file"]');
    inputs.forEach(input => {
        console.log(input.name, input.files);
    });
});

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
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('[name="first_name"]').value = 'Alexa';
        document.querySelector('[name="last_name"]').value = 'Morales';
        document.querySelector('[name="gender"]').value = 'Female';
        document.querySelector('[name="age"]').value = '12';
        document.querySelector('[name="birthday"]').value = '2013-09-15';
        document.querySelector('[name="contact_number"]').value = '09171234567';

        document.querySelector('[name="region"]').innerHTML += '<option value="Region IV-A" selected>Region IV-A</option>';
        document.querySelector('[name="province"]').innerHTML += '<option value="Batangas" selected>Batangas</option>';
        document.querySelector('[name="province"]').disabled = false;
        document.querySelector('[name="city"]').innerHTML += '<option value="Lipa City" selected>Lipa City</option>';
        document.querySelector('[name="city"]').disabled = false;
        document.querySelector('[name="barangay"]').innerHTML += '<option value="Barangay 7" selected>Barangay 7</option>';
        document.querySelector('[name="barangay"]').disabled = false;

        document.querySelector('[name="address"]').value = '123 Mabini Street';
        document.querySelector('[name="zip_code"]').value = '4217';

        document.querySelector('[name="parent_firstname"]').value = 'Carlos';
        document.querySelector('[name="parent_lastname"]').value = 'Morales';
        document.querySelector('[name="emergency_contact"]').value = '09181112222';
        document.querySelector('[name="parent_role"]').value = 'father';

        document.querySelector('[name="previous_school"]').value = 'San Pedro Elementary School';
        document.querySelector('[name="previous_grade_level"]').value = '5';
        document.querySelector('[name="enrollment_grade"]').value = 'elementary';
        handleGradeChange(); // Show grade level dropdown after setting enrollment
        setTimeout(() => {
            document.querySelector('[name="grade_level"]').innerHTML += '<option value="grade-6" selected>Grade 6</option>';
            document.querySelector('[name="grade_level"]').value = 'grade-6';
        }, 100); // Allow time for dropdown to render

        document.querySelector('[name="medical_history"]').value = 'Allergic Rhinitis';
        document.querySelector('[name="hearing_loss"]').value = 'None';
        document.querySelector('[name="hearing_identity"]').value = 'none';
        document.querySelector('[name="assistive_devices"]').value = 'None';
        document.querySelector('[name="health_notes"]').value = 'N/A';
    });
</script>



