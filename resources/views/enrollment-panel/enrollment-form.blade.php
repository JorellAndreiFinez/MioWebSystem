
@php
    $formData = $form ?? [];
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
                                    <input type="number" name="age" placeholder="Age" required />
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
                                    <input type="text" name="emergency_contact" placeholder="Emergency Number"  required />
                                </div>
                                <div class="form-group wide">
                                    <label>Emergency Name <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="emergency_name" value="Emergency Name" required />
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
                                    <label>Grade Level <span style="color: red; font-weight:700">*</span></label>
                                    <input type="number" name="previous_grade_level" placeholder="Previous Grade Level" required />
                                </div>
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
                                    <label>Type of Disability <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="disability" placeholder="E.g. Deaf, Hard of Hearing" required />
                                </div>
                                <div class="form-group">
                                    <label>Type of Hearing Loss (if applicable)</label>
                                    <input type="text" name="hearing_loss" placeholder="E.g. Sensorineural, Conductive, Mixed" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Do you identify as Deaf or Hard of Hearing? <span style="color: red; font-weight:700">*</span></label>
                                    <select name="hearing_identity" required>
                                        <option value="" disabled selected>Select one</option>
                                        <option value="deaf" >Deaf</option>
                                        <option value="hard-of-hearing">Hard of Hearing</option>
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
                                        <input type="file" name="good_moral_files[]" class="fileInput" multiple hidden>
                                    </div>
                                    <div class="file-preview"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Health Certificate <span style="color: red; font-weight:700">*</span></label>
                                    <div class="file-upload-box drop-area">
                                        <input type="file" name="health_certificate_files[]" class="fileInput" multiple hidden>
                                    </div>
                                    <div class="file-preview"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>PSA Birth Certificate <span style="color: red; font-weight:700">*</span></label>
                                    <div class="file-upload-box drop-area">
                                        <input type="file" name="psa_birth_certificate_files[]" class="fileInput" multiple hidden>
                                    </div>
                                    <div class="file-preview"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Form 137 <span style="color: red; font-weight:700">*</span></label>
                                    <div class="file-upload-box drop-area">
                                        <input type="file" name="form_137_files[]" class="fileInput" multiple hidden>
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

    @elseif($status === 'Registered' || $status === 'Assessment' || $status === 'Enrolled')

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
                    <div class="form-group">
                        <label>Gender</label>
                        <input type="text" name="gender" value="{{ $form['gender'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" value="{{ $form['age'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Birthday</label>
                        <input type="date" name="birthday" value="{{ $form['birthday'] ?? '' }}" readonly />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group wide">
                        <label>Street Name, Building, House No.</label>
                        <input type="text" name="street" value="{{ $form['street'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group wide">
                        <label>Barangay</label>
                        <input type="text" name="barangay" value="{{ $form['barangay'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group wide">
                        <label for="region">Region</label>
                        <select id="region" name="region" disabled>
                            <option value="">Select a Region</option>
                            <option value="NCR" {{ ($form['region'] ?? '') == 'NCR' ? 'selected' : '' }}>NCR</option>
                            <option value="CAR" {{ ($form['region'] ?? '') == 'CAR' ? 'selected' : '' }}>CAR</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group wide">
                        <label>Province</label>
                        <input type="text" name="province" value="{{ $form['province'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group wide">
                        <label>City</label>
                        <input type="text" name="city" value="{{ $form['city'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group wide">
                        <label>Zip Code</label>
                        <input type="number" name="zip_code" value="{{ $form['zip_code'] ?? '' }}" readonly />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" value="{{ $form['contact_number'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group wide">
                        <label>Emergency Contact Number</label>
                        <input type="text" name="emergency_contact" value="{{ $form['emergency_contact'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group wide">
                        <label>Emergency Name</label>
                        <input type="text" name="emergency_name" value="{{ $form['emergency_name'] ?? '' }}" readonly />
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
                        <label>Grade Level</label>
                        <input type="number" name="grade_level" value="{{ $form['grade_level'] ?? '' }}" readonly />
                    </div>
                </div>
            </div>

            <!-- Health Info -->
            <div class="section-header">Health Information</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-group">
                        <label>Medical History</label>
                        <input type="text" name="medical_history" value="{{ $form['medical_history'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Type of Disability</label>
                        <input type="text" name="disability" value="{{ $form['disability'] ?? '' }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Type of Hearing Loss (if applicable)</label>
                        <input type="text" name="hearing_loss" value="{{ $form['hearing_loss'] ?? '' }}" readonly />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Do you identify as Deaf or Hard of Hearing?</label>
                        <select name="hearing_identity" disabled>
                            <option value="">Select one</option>
                            <option value="deaf" {{ ($form['hearing_identity'] ?? '') == 'deaf' ? 'selected' : '' }}>Deaf</option>
                            <option value="hard_of_hearing" {{ ($form['hearing_identity'] ?? '') == 'hard_of_hearing' ? 'selected' : '' }}>Hard of Hearing</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="section-header">Proof of Payment Upload</div>
                <div class="section-content">
                    {{-- Payment Proof --}}
                    @if (!empty($formData['payment_proof_path']))
                        <div class="form-group">
                            <label>Payment Proof</label>
                            <div class="review-toggle" data-target="payment-proof-preview">
                                <button type="button" class="review-btn btn btn-primary btn-sm">Review</button>
                                <button type="button" class="hide-btn btn btn-secondary btn-sm d-none">Hide</button>
                            </div>
                            <div id="payment-proof-preview" class="file-preview-area d-none mt-2">
                                <img src="{{ asset($formData['payment_proof_path']) }}" class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                            </div>
                        </div>
                    @endif
                </div>

                        <!-- File Upload Section -->
                        <div class="section-header">File Upload</div>
                        <div class="section-content">
                            <div class="form-row">
                                {{-- Good Moral --}}
                                @if (!empty($formData['good_moral_paths']))
                                    <div class="form-group">
                                        <label>Good Moral</label>
                                        <div class="review-toggle" data-target="good-moral-preview">
                                            <button type="button" class="review-btn btn btn-primary btn-sm">Review</button>
                                            <button type="button" class="hide-btn btn btn-secondary btn-sm d-none">Hide</button>
                                        </div>
                                        <div id="good-moral-preview" class="file-preview-area d-none mt-2">
                                           @foreach ($form['form_137_paths'] ?? [] as $filePath)
                                                <img src="{{ asset($filePath) }}" alt="Form 137 File" style="max-width: 200px;">
                                            @endforeach

                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="form-row">
                                @if (!empty($formData['health_certificate_paths']))
                            <div class="form-group">
                                <label>Health Certificate</label>
                                <div class="review-toggle" data-target="health-certificate-preview">
                                    <button type="button" class="review-btn btn btn-primary btn-sm">Review</button>
                                    <button type="button" class="hide-btn btn btn-secondary btn-sm d-none">Hide</button>
                                </div>
                                <div id="health-certificate-preview" class="file-preview-area d-none mt-2">
                                    @foreach ($formData['health_certificate_paths'] as $path)
                                        <img src="{{ asset($path) }}" class="img-fluid rounded shadow-sm mb-2" style="max-height: 300px;">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                            </div>

                            <div class="form-row">
                               @if (!empty($formData['psa_birth_certificate_paths']))
                                <div class="form-group">
                                    <label>PSA Birth Certificate</label>
                                    <div class="review-toggle" data-target="psa-birth-preview">
                                        <button type="button" class="review-btn btn btn-primary btn-sm">Review</button>
                                        <button type="button" class="hide-btn btn btn-secondary btn-sm d-none">Hide</button>
                                    </div>
                                    <div id="psa-birth-preview" class="file-preview-area d-none mt-2">
                                        @foreach ($formData['psa_birth_certificate_paths'] as $path)
                                            <img src="{{ asset($path) }}" class="img-fluid rounded shadow-sm mb-2" style="max-height: 300px;">
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            </div>

                            <div class="form-row">
                                @if (!empty($formData['form_137_paths']))
                                <div class="form-group">
                                    <label>Form 137</label>
                                    <div class="review-toggle" data-target="form-137-preview">
                                        <button type="button" class="review-btn btn btn-primary btn-sm">Review</button>
                                        <button type="button" class="hide-btn btn btn-secondary btn-sm d-none">Hide</button>
                                    </div>
                                    <div id="form-137-preview" class="file-preview-area d-none mt-2">
                                        @foreach ($formData['form_137_paths'] as $path)
                                            <img src="{{ asset($path) }}" class="img-fluid rounded shadow-sm mb-2" style="max-height: 300px;">
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            </div>
                        </div>

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
                                    <input type="text" name="first_name" value="{{ $form['first_name'] ?? '' }}" required />
                                </div>
                                <div class="form-group">
                                    <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="last_name" value="{{ $form['last_name'] ?? '' }}" required />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Gender <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="gender" value="Female" required />
                                </div>
                                <div class="form-group">
                                    <label>Age <span style="color: red; font-weight:700">*</span></label>
                                    <input type="number" name="age" value="{{ $form['age'] ?? '' }}" placeholder="Age" required min="1" max="100" />
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
                                <input type="text" name="address"  placeholder="Home Address" value="{{ $form['street'] ?? '' }}"  required />
                                </div>

                                <div class="form-group">
                                <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
                                <input type="number" name="zip_code" placeholder="Zip Code" minlength="4" maxlength="4" required />
                                </div>
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
                                <div class="form-group wide">
                                    <label>Emergency Name <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="emergency_name" value="Pepito Manaloto" required />
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information Section -->
                        <div class="section-header">Academic Information</div>
                        <div class="section-content">
                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Previous School Attended <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="previous_school" value="Blah blah High school" required />
                                </div>
                                <div class="form-group">
                                    <label>Grade Level <span style="color: red; font-weight:700">*</span></label>
                                    <input type="number" name="grade_level" value="10" required />
                                </div>
                            </div>
                        </div>


                        <!-- Health Information Section -->
                        <div class="section-header">Health Information</div>
                        <div class="section-content">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Medical History <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="medical_history" placeholder="E.g. Asthma, Allergies, etc." required value="Asthma, Peanut Allergy" />
                                </div>
                                <div class="form-group">
                                    <label>Type of Disability <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="disability" placeholder="E.g. Deaf, Hard of Hearing" required value="Deaf" />
                                </div>
                                <div class="form-group">
                                    <label>Type of Hearing Loss (if applicable)</label>
                                    <input type="text" name="hearing_loss" placeholder="E.g. Sensorineural, Conductive, Mixed" value="Sensorineural" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Do you identify as Deaf or Hard of Hearing? <span style="color: red; font-weight:700">*</span></label>
                                    <select name="hearing_identity" required>
                                        <option value="" disabled>Select one</option>
                                        <option value="deaf" selected>Deaf</option>
                                        <option value="hard-of-hearing">Hard of Hearing</option>
                                        <option value="none">Neither</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Assistive Devices Used</label>
                                    <input type="text" name="assistive_devices" placeholder="E.g. Hearing Aid, Cochlear Implant, None" value="Hearing Aid" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Other Notes or Health Concerns</label>
                                    <input type="text" name="health_notes" placeholder="Specify any other relevant information" value="Requires regular checkups" />
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
                                        <input type="file" name="payment" class="fileInput" multiple hidden>
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
                                        <input type="file" name="good_moral_files[]" class="fileInput" multiple hidden required>
                                    </div>
                                    <div class="file-preview"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Health Certificate <span style="color: red; font-weight:700">*</span></label>
                                    <div class="file-upload-box drop-area">
                                        <input type="file" name="health_certificate_files[]" class="fileInput" multiple hidden required>
                                    </div>
                                    <div class="file-preview"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>PSA Birth Certificate <span style="color: red; font-weight:700">*</span></label>
                                    <div class="file-upload-box drop-area">
                                        <input type="file" name="psa_birth_certificate_files[]" class="fileInput" multiple hidden required>
                                    </div>
                                    <div class="file-preview"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Form 137 <span style="color: red; font-weight:700">*</span></label>
                                    <div class="file-upload-box drop-area">
                                        <input type="file" name="form_137_files[]" class="fileInput" multiple hidden required>
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

    document.querySelector('form').addEventListener('submit', function (e) {
    const requiredFileInputs = [
        'input[name="good_moral_files[]"]',
        'input[name="health_certificate_files[]"]',
        'input[name="psa_birth_certificate_files[]"]',
        'input[name="form_137_files[]"]',
    ];

    let valid = true;

    requiredFileInputs.forEach(selector => {
        const input = document.querySelector(selector);
        if (input && input.files.length === 0) {
            valid = false;
            alert('Please upload all required documents.');
            // Optional: Scroll to the missing file field or visually highlight it
        }
    });

    if (!valid) {
        e.preventDefault(); // Prevent form submission
    }
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
            uploadedFiles = [...uploadedFiles, ...files]; // Append new files
            updatePreview();
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
</script>

<!-- FOR TESTING -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dummy personal info
    document.querySelector('input[name="first_name"]').value = "Pedro";
    document.querySelector('input[name="last_name"]').value = "Dela Franz";
    document.querySelector('select[name="gender"]').value = "Male";
    document.querySelector('input[name="age"]').value = 16;
    document.querySelector('input[name="birthday"]').value = "2010-05-15";

    // Dummy address info
    document.querySelector('input[name="address"]').value = "123 St. Example";
    document.querySelector('input[name="zip_code"]').value = "1234";
    document.querySelector('input[name="contact_number"]').value = "09171234567";
    document.querySelector('input[name="emergency_contact"]').value = "09179876543";
    document.querySelector('input[name="emergency_name"]').value = "Maria Cruz";

   
    // Dummy academic info
    document.querySelector('input[name="previous_school"]').value = "Sample Elementary School";
    document.querySelector('input[name="previous_grade_level"]').value = 6;

    // Dummy health info
    document.querySelector('input[name="medical_history"]').value = "Asthma";
    document.querySelector('input[name="disability"]').value = "Hard of Hearing";
    document.querySelector('input[name="hearing_loss"]').value = "Sensorineural";
    document.querySelector('select[name="hearing_identity"]').value = "hard-of-hearing";
    document.querySelector('input[name="assistive_devices"]').value = "Hearing Aid";
    document.querySelector('input[name="health_notes"]').value = "Needs seat near the teacher.";

    // Optional: Simulate file input preview (but can't assign files via JS for security reasons)
    const dummyFileInputs = document.querySelectorAll('.file-upload-box input[type="file"]');
    dummyFileInputs.forEach(input => {
        const preview = input.closest('.form-group').querySelector('.file-preview');
        if (preview) {
            preview.innerHTML = '<div class="file-dummy">DummyFile.pdf</div>';
        }
    });
});


</script>
