
@php
    $formData = $form ?? [];
@endphp

<section class="home-section">
    <div class="text" style="margin-top: 2rem">
        Enrollment Form
    </div>
    <div class="dashboard-grid">
    <!-- FORM -->

    @if ($status === 'NotStarted')

        <div class="form-container">
            <form action="{{ route('enrollment.submit') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-container">

                    <!-- Personal Information Section -->
                        <div class="section-header">Personal Information</div>
                        <div class="section-content">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="first_name" value="Jorell Andrei" required />
                                </div>
                                <div class="form-group">
                                    <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="last_name" value="Finez" required />
                                </div>
                                <div class="form-group">
                                    <label>Gender <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="gender" value="Female" required />
                                </div>
                                <div class="form-group">
                                    <label>Age <span style="color: red; font-weight:700">*</span></label>
                                    <input type="number" name="age" value="17" required />
                                </div>
                                <div class="form-group">
                                    <label>Birthday <span style="color: red; font-weight:700">*</span></label>
                                    <input type="date" name="birthday" value="2006-05-24" required />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Street Name, Building, House No. <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="street" value="13 Blk Lot 8, Camella Homes, Valenzuela City" required />
                                </div>
                                <div class="form-group wide">
                                    <label>Barangay <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="barangay" value="Brgy. Lapuk" required />
                                </div>
                                <div class="form-group wide">
                                    <label for="region">Region <span style="color: red; font-weight:700">*</span></label>
                                    <select id="region" name="region" required>
                                        <option value="" disabled>Select a Region</option>
                                        <option value="NCR" selected>National Capital Region (NCR)</option>
                                        <option value="CAR">Cordillera Administrative Region (CAR)</option>
                                        <!-- Add others... -->
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>Province <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="province" value="Bulacan" required />
                                </div>
                                <div class="form-group wide">
                                    <label>City <span style="color: red; font-weight:700">*</span></label>
                                    <input type="text" name="city" value="Valenzuela" required />
                                </div>
                                <div class="form-group wide">
                                    <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
                                    <input type="number" name="zip_code" value="3333" minlength="4" maxlength="4" required />
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
        </div>

    @elseif($status === 'Registered')

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
    @endif


</section>


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
