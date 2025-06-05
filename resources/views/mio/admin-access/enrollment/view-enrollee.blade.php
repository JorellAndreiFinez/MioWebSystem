<section class="home-section">
  <div class="text">View Enrollee</div>
  <div class="teacher-container">
    @include('mio.dashboard.status-message')
      <div class="form-container">
        <!-- Enrollee Info Display -->

        <div class="section-header">Personal Information</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="{{ $enrollee['fname'] }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="{{ $enrollee['enrollment_form']['last_name'] }}" readonly />
                    </div>
                </div>

                 <div class="form-row">
                    <div class="form-group">
                        <label>Gender</label>
                        <input type="text" name="gender" value="{{ $enrollee['enrollment_form']['gender'] }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" value="{{ $enrollee['enrollment_form']['age'] }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Birthday</label>
                        <input type="date" name="birthday" value="{{ $enrollee['enrollment_form']['birthday'] }}" readonly />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group wide">
                        <label>Street Name, Building, House No.</label>
                        <input type="text" name="street" value="{{ $enrollee['enrollment_form']['street'] }}" readonly />
                    </div>
                    <div class="form-group wide">
                        <label>Barangay</label>
                        <input type="text" name="barangay" value="{{ $enrollee['enrollment_form']['barangay'] }}" readonly />
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
                        <input type="text" name="province" value="{{ $enrollee['enrollment_form']['province'] }}" readonly />
                    </div>
                    <div class="form-group wide">
                        <label>City</label>
                        <input type="text" name="city" value="{{ $enrollee['enrollment_form']['city'] }}"readonly />
                    </div>
                    <div class="form-group wide">
                        <label>Zip Code</label>
                        <input type="number" name="zip_code" value="{{ $enrollee['enrollment_form']['zip_code'] }}" readonly />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" value="{{ $enrollee['enrollment_form']['contact_number'] }}" readonly />
                    </div>
                    <div class="form-group wide">
                        <label>Emergency Contact Number</label>
                        <input type="text" name="emergency_contact" value="{{ $enrollee['enrollment_form']['emergency_contact'] }}" readonly />
                    </div>
                    <div class="form-group wide">
                        <label>Emergency Name</label>
                        <input type="text" name="emergency_name" value="{{ $enrollee['enrollment_form']['emergency_name'] }}" readonly />
                    </div>
                </div>
            </div>

            <!-- Academic Info -->
            <div class="section-header">Academic Information</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-group wide">
                        <label>Previous School Attended</label>
                        <input type="text" name="previous_school" value="{{ $enrollee['enrollment_form']['previous_school'] }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Grade Level</label>
                        <input type="number" name="grade_level"  value="{{ $enrollee['enrollment_form']['grade_level'] }}"  readonly />
                    </div>
                </div>
            </div>

            <!-- Health Info -->
            <div class="section-header">Health Information</div>
            <div class="section-content">
                <div class="form-row">
                    <div class="form-group">
                        <label>Medical History</label>
                        <input type="text" name="medical_history"  value="{{ $enrollee['enrollment_form']['medical_history'] }}" readonly />
                    </div>
                    <div class="form-group">
                        <label>Type of Disability</label>
                        <input type="text" name="disability"  value="{{ $enrollee['enrollment_form']['disability'] }}"  readonly />
                    </div>
                    <div class="form-group">
                        <label>Type of Hearing Loss (if applicable)</label>
                        <input type="text" name="hearing_loss"  value="{{ $enrollee['enrollment_form']['hearing_loss'] }}"  readonly />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Do you identify as Deaf or Hard of Hearing?</label>
                        <select name="hearing_identity" disabled>
                            <option value="">Select one</option>
                            <option value="deaf" {{ ($enrollee['enrollment_form']['hearing_identity'] ?? '') == 'deaf' ? 'selected' : '' }}>Deaf</option>
                            <option value="hard_of_hearing" {{ ($enrollee['enrollment_form']['hearing_identity'] ?? '') == 'hard_of_hearing' ? 'selected' : '' }}>Hard of Hearing</option>
                        </select>
                    </div>
                </div>
            </div>

        <div class="form-group">
        <div class="section-header">Uploaded Documents</div>
        <div class="section-content">

        <!-- File Preview Area -->
        <div id="filePreviewContainer" style="margin-top: 20px;">
        <h3>Document Preview</h3>
        <div id="filePreview" style="border: 1px solid #ccc; padding: 10px;">
            <iframe id="previewFrame" src="" style="width: 100%; height: 500px;" frameborder="0"></iframe>
        </div>
        </div>

            <!-- PSA Birth Certificate -->
            <div class="document-group" style="margin-top: 3rem;">
                <p>
                    <strong>PSA Birth Certificate:</strong>
                    <button type="button" class="toggle-btn " onclick="toggleDoc('psa')">Show/Hide</button>
                </p>
                <div id="psa" class="doc-files" style="display: none;">
                    @foreach ($enrollee['enrollment_form']['psa_birth_certificate_paths'] ?? [] as $path)
                        <a href="{{ asset($path) }}" target="_blank">View PSA Birth Certificate</a><br>
                    @endforeach
                </div>
            </div>

            <!-- Form 137 -->
            <div class="document-group">
            <p>
                <strong>Form 137:</strong>
                <button type="button" class="toggle-btn" onclick="toggleDoc('form137')">Show/Hide</button>
            </p>
            <div id="form137" class="doc-files" style="display: none;">
                @foreach ($enrollee['enrollment_form']['form_137_paths'] ?? [] as $path)
                <a href="{{ asset($path) }}" target="_blank">View Form 137</a><br>
                @endforeach
            </div>
            </div>

            <!-- Good Moral -->
            <div class="document-group">
            <p>
                <strong>Good Moral:</strong>
                <button type="button" class="toggle-btn" onclick="toggleDoc('goodMoral')">Show/Hide</button>
            </p>
            <div id="goodMoral" class="doc-files" style="display: none;">
                @foreach ($enrollee['enrollment_form']['good_moral_paths'] ?? [] as $path)
                <a href="{{ asset($path) }}" target="_blank">View Good Moral</a><br>
                @endforeach
            </div>
            </div>

            <!-- Health Certificate -->
            <div class="document-group">
            <p>
                <strong>Health Certificate:</strong>
                <button type="button" class="toggle-btn" onclick="toggleDoc('healthCert')">Show/Hide</button>
            </p>
            <div id="healthCert" class="doc-files" style="display: none;">
                @foreach ($enrollee['enrollment_form']['health_certificate_paths'] ?? [] as $path)
                <a href="{{ asset($path) }}" target="_blank">View Health Certificate</a><br>
                @endforeach
            </div>
            </div>

            <!-- Payment Proof -->
            <div class="document-group">
            <p>
                <strong>Proof of Payment:</strong>
                <button type="button" class="toggle-btn" onclick="toggleDoc('paymentProof')">Show/Hide</button>
            </p>
            <div id="paymentProof" class="doc-files" style="display: none;">
                @if (!empty($enrollee['enrollment_form']['payment_proof_path']))
                <a href="{{ asset($enrollee['enrollment_form']['payment_proof_path']) }}" target="_blank">View Payment Proof</a>
                @endif
            </div>
            </div>
        </div>


        <!-- Teacher Action Section -->
            <form id="teacherActionForm" method="POST" action="{{ route('mio.update-enrollee', ['id' => $id]) }}">
                @csrf
                @method('PUT')
                <div class="section-header">Admin Action</div>
                <div class="section-content">
                <div class="form-group wide">
                    <label>Enrollment Feedback</label>
                    <textarea name="feedback_admin" placeholder="Write your feedback here..." required>{{ $enrollee['feedback_admin'] ?? '' }}</textarea>
                </div>
                <div class="form-group">
                    <label>Enrollment Status</label>
                    <select name="enroll_status" required>
                    <option value="Revision" {{ $enrollee['enroll_status'] == 'Revision' ? 'selected' : '' }}>Revision</option>
                    <option value="Registered" {{ $enrollee['enroll_status'] == 'Registered' ? 'selected' : '' }}>Registered</option>
                    <option value="Assessment" {{ $enrollee['enroll_status'] == 'Assessment' ? 'selected' : '' }}>Assessment</option>
                    <option value="Enrolled" {{ $enrollee['enroll_status'] == 'Enrolled' ? 'selected' : '' }}>Enrolled</option>
                    <option value="Rejected" {{ $enrollee['enroll_status'] == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                 <div class="form-row">
                     <div class="button-group">
                    <button type="button" class="btn cancel-btn">
                        <a href="{{ url()->previous() }}">Cancel</a>
                    </button>
                    <button class="btn add-btn" type="submit">
                        <span class="icon">✔</span> Save Changes
                    </button>
                    </div>
                 </div>
                </div>


            </form>



      </div>
  </div>
</section>

<script>
  function previewFile(filePath) {
    const previewFrame = document.getElementById('previewFrame');
    previewFrame.src = filePath;
  }
</script>
