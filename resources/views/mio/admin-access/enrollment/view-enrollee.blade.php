<section class="home-section">
  <div class="text">View Enrollee</div>
  <div class="teacher-container">
      <div class="form-container">
    @include('mio.dashboard.status-message')

            <div id="page-1" class="enrollee-section">
                <!-- Enrollee Info Display -->
                <div class="section-header">Personal Information</div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="{{ $enrollee['enrollment_form']['first_name'] }}" readonly />
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="{{ $enrollee['enrollment_form']['last_name'] }}" readonly />
                            </div>
                        </div>

                        <hr>

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

                        <hr>

                        <div class="form-row">
                            <div class="form-group wide">
                                <label for="region">Region <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" id="region" name="region" readonly />
                            </div>

                            <div class="form-group wide">
                                <label>Province <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" id="province" name="province" readonly />
                            </div>

                            <div class="form-group wide">
                                <label>City/ Municipality <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" id="city" name="city" readonly />
                            </div>

                            <div class="form-group wide">
                                <label>Barangay <span style="color: red; font-weight:700">*</span> </label>
                                <input type="text" id="barangay" name="barangay" readonly />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Building/House No., Street <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="address" placeholder="Home Address" readonly />
                            </div>

                            <div class="form-group">
                                <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
                                <input type="number" name="zip_code" placeholder="Zip Code" minlength="4" maxlength="4" readonly />
                            </div>
                        </div>

                            <hr>


                        <div class="form-row">
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="text" name="contact_number" value="{{ $enrollee['enrollment_form']['contact_number'] }}" readonly />
                            </div>
                        </div>
                    </div>

                    <div class="section-header">Parent/Guardian Information</div>
                        <div class="section-content">

                         <div class="form-row">
                            <div class="form-group wide">
                                <label>First Name <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="parent_firstname" value="{{ $enrollee['enrollment_form']['parent_firstname'] }}" placeholder="Parent First Name" required />
                            </div>

                            <div class="form-group wide">
                                <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="parent_lastname" value="{{ $enrollee['enrollment_form']['parent_lastname'] }}" placeholder="Parent Last Name" required />
                            </div>
                        </div>
                        <hr>
                             <div class="form-row">

                        <div class="form-group">
                            <label>Parent/Guardian Contact Number <span style="color: red; font-weight:700">*</span></label>
                            <input type="text" name="emergency_contact" placeholder="Emergency Number" required
                                value="{{ $enrollee['enrollment_form']['emergency_contact'] }}" readonly />
                        </div>

                        <div class="form-group">
                            <label>Parent Role <span style="color: red; font-weight:700">*</span></label>
                            <select name="parent_role" disabled>
                                <option value="" disabled {{ old('parent_role', '') == '' ? 'selected' : '' }}>Select role</option>
                                <option value="father" {{ old('parent_role', $enrollee['enrollment_form']['parent_role'] ?? '') == 'father' ? 'selected' : '' }}>Father</option>
                                <option value="mother" {{ old('parent_role', $enrollee['enrollment_form']['parent_role'] ?? '') == 'mother' ? 'selected' : '' }}>Mother</option>
                                <option value="guardian" {{ old('parent_role', $enrollee['enrollment_form']['parent_role'] ?? '') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                            </select>
                        </div>
                    </div>
                            </div>


                    @php
                        $gradeMap = [
                            'kinder' => 'Kinder',
                            'elementary' => 'Elementary',
                            'junior-highschool' => 'Junior High School',
                            'senior-highschool' => 'Senior High School',
                            'one-on-one-therapy' => 'One-on-One Therapy',
                        ];

                        $enrollmentGradeKey = $enrollee['enrollment_form']['enrollment_grade'] ?? '';
                        $enrollmentGradeDisplay = $gradeMap[$enrollmentGradeKey] ?? $enrollmentGradeKey;
                    @endphp

                    <!-- Academic Information Section -->
                    <div class="section-header">Academic Information</div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Previous School Attended <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="previous_school" id="previous_school" value="{{ $enrollee['enrollment_form']['previous_school'] ?? '' }}" readonly />
                            </div>
                            <div class="form-group">
                                <label>Previous Grade Level <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="previous_grade_level" id="previous_grade_level" value="{{ $enrollee['enrollment_form']['previous_grade_level'] ?? '' }}" readonly />
                            </div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label>What are you enrolling in PID? <span style="color: red; font-weight:700">*</span></label>
                            <input type="text" name="enrollment_grade" id="enrollment_grade" value="{{ $enrollmentGradeDisplay }}" readonly />
                        </div>

                        <div class="form-group" id="grade_level_group">
                            <label>Grade Level <span style="color: red; font-weight:700">*</span></label>
                            <input type="text" name="grade_level" id="grade_level" value="{{ $enrollee['enrollment_form']['grade_level'] ?? '' }}" readonly />
                        </div>

                        @if (!empty($enrollee['enrollment_form']['strand']))
                            <div class="form-group" id="strand_group">
                                <label>Strand <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="strand" id="strand" value="{{ $enrollee['enrollment_form']['strand'] }}" readonly />
                            </div>
                        @endif

                    </div>

                <!-- Health Information Section -->
                    <div class="section-header">Health Information</div>
                                <div class="section-content">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Medical History <span style="color: red; font-weight:700">*</span></label>
                                            <input type="text" name="medical_history" placeholder="E.g. Asthma, Allergies, etc." value="{{ $enrollee['enrollment_form']['medical_history'] }}" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label>Type of Hearing Loss (if applicable)</label>
                                            <input type="text" name="hearing_loss" placeholder="E.g. Sensorineural, Conductive, Mixed" value="{{ $enrollee['enrollment_form']['hearing_loss'] }}"  readonly/>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Do you identify as? <span style="color: red; font-weight:700">*</span></label>
                                        <input type="text" value="{{ $enrollee['enrollment_form']['hearing_identity'] }}"  readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Assistive Devices Used</label>
                                            <input type="text" name="assistive_devices" placeholder="E.g. Hearing Aid, Cochlear Implant, None" value="{{ $enrollee['enrollment_form']['assistive_devices'] }}" readonly/>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group wide">
                                            <label>Other Notes or Health Concerns</label>
                                            <input type="text" name="health_notes" placeholder="Specify any other relevant information" value="{{ $enrollee['enrollment_form']['health_notes'] }}" />
                                        </div>
                                    </div>
                                </div>

                    <div class="form-group">
                    <div class="section-header">Uploaded Documents</div>
                    <div class="section-content">

                        <!-- File Preview Area -->
                        <div id="filePreviewContainer" style="margin-top: 20px; display: none; position: relative;">
                        <h3>Document Preview</h3>

                        <!-- Loader -->
                        <div id="previewLoader" style="
                            position: absolute;
                            top: 40px;
                            left: 0;
                            right: 0;
                            height: 30px;
                            background: #f0f0f0;
                            text-align: center;
                            line-height: 30px;
                            font-weight: bold;
                            display: none;
                            z-index: 10;
                            ">
                            Loading document...
                        </div>

                        <div id="filePreview" style="border: 1px solid #ccc; padding: 10px;">
                            <iframe id="previewFrame" src="" style="width: 100%; height: 500px;" frameborder="0"></iframe>
                        </div>
                        </div>




                        <!-- PSA Birth Certificate -->
                        <div class="document-group" style="margin-top: 3rem;">
                            <p>
                                <strong>PSA Birth Certificate:</strong>
                                <button type="button" onclick="previewFile('{{ $enrollee['enrollment_form']['psa_birth_certificate_path'] ?? '' }}')">Preview</button>
                            </p>
                            <div id="psa" class="doc-files" style="display: none;">
                                @if (!empty($enrollee['enrollment_form']['psa_birth_certificate_path']))
                                    <a href="{{ $enrollee['enrollment_form']['psa_birth_certificate_path'] }}" target="_blank">View PSA Birth Certificate</a><br>
                                @endif
                            </div>
                        </div>


                        <!-- Form 137 -->
                        <div class="document-group">
                            <p>
                                <strong>Form 137:</strong>
                                <button type="button" onclick="previewFile('{{ $enrollee['enrollment_form']['form_137_path'] ?? '' }}')">Preview</button>
                            </p>
                            <div id="form137" class="doc-files" style="display: none;">
                                @if (!empty($enrollee['enrollment_form']['form_137_path']))
                                    <a href="{{ $enrollee['enrollment_form']['form_137_path'] }}" target="_blank">View Form 137</a><br>
                                @endif
                            </div>
                        </div>


                        <!-- Good Moral -->
                    <div class="document-group">
                        <p>
                            <strong>Good Moral:</strong>
                            <button type="button" onclick="previewFile('{{ $enrollee['enrollment_form']['good_moral_path'] ?? '' }}')">Preview</button>
                        </p>
                        <div id="goodMoral" class="doc-files" style="display: none;">
                            @if (!empty($enrollee['enrollment_form']['good_moral_path']))
                                <a href="{{ $enrollee['enrollment_form']['good_moral_path'] }}" target="_blank">View Good Moral</a><br>
                            @endif
                        </div>
                    </div>


                        <!-- Health Certificate -->
                    <div class="document-group">
                        <p>
                            <strong>Health Certificate:</strong>
                            <button type="button" onclick="previewFile('{{ $enrollee['enrollment_form']['health_certificate_path'] ?? '' }}')">Preview</button>
                        </p>
                        <div id="healthCert" class="doc-files" style="display: none;">
                            @if (!empty($enrollee['enrollment_form']['health_certificate_path']))
                                <a href="{{ $enrollee['enrollment_form']['health_certificate_path'] }}" target="_blank">View Health Certificate</a><br>
                            @endif
                        </div>
                    </div>

                        <!-- Payment Proof -->
                    <div class="document-group">
                        <p>
                            <strong>Proof of Payment:</strong>
                            <button type="button" onclick="previewFile('{{ $enrollee['enrollment_form']['payment_proof_path'] ?? '' }}')">Preview</button>
                        </p>
                        <div id="paymentProof" class="doc-files" style="display: none;">
                            @if (!empty($enrollee['enrollment_form']['payment_proof_path']))
                                <a href="{{ $enrollee['enrollment_form']['payment_proof_path'] }}" target="_blank">View Payment Proof</a>
                            @endif
                        </div>
                    </div>

                    </div>
            </div>
            </div>

            <!-- ASSESSMENT INFO DISPLAY -->

            <div id="page-2" class="enrollee-section assessment-section" style="display: none;">
            <div class="section-header">📋 Assessment Overview</div>

            {{-- Reading Assessment --}}
            @if(isset($enrollee['Assessment']['Reading']))
            <div class="assessment-group">
                <div class="assessment-type">📘 Reading Assessment</div>
                @foreach($enrollee['Assessment']['Reading'] as $reading)
                    @if(is_array($reading) && isset($reading['text']))
                    <div class="assessment-card">
                        <h5>Reading Item</h5>
                        <p><strong>Text:</strong> {{ $reading['text'] }}</p>
                        <div class="score-table">
                            <div><span class="tag">SpeechAce</span> {{ $reading['speechace_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">IELTS</span> {{ $reading['ielts_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">TOEIC</span> {{ $reading['toeic_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">PTE</span> {{ $reading['pte_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">CEFR</span> {{ $reading['cefr_pronunciation_score'] ?? 'N/A' }}</div>
                        </div>
                        @if(isset($reading['uploaded_audio']['url']))
                            <audio controls src="{{ $reading['uploaded_audio']['url'] }}"></audio>
                        @endif
                    </div>
                    @endif


                    @if(isset($reading['words']))
                    <div class="phoneme-breakdown">
                        @foreach ($reading['words'] as $word)
                            <div class="word-phoneme">
                                <strong>{{ $word['word'] ?? 'Unknown' }}:</strong>
                                @if(isset($word['phones']))
                                    @foreach($word['phones'] as $phoneme)
                                        <span class="phoneme-tag">{{ $phoneme['phone'] ?? '' }}</span>
                                    @endforeach
                                @else
                                    <span class="phoneme-tag">No phonemes</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @endforeach
            </div>
            @endif

            {{-- Speech Assessment --}}
            @if(isset($enrollee['Assessment']['Speech_Auditory']['Speech']))
            <div class="assessment-group">
                <div class="assessment-type">🗣️ Speech Assessment</div>
                @foreach($enrollee['Assessment']['Speech_Auditory']['Speech'] as $speech)
                    @if(is_array($speech) && isset($speech['text']))
                    <div class="assessment-card">
                        <h5>Speech Item</h5>
                        <p><strong>Text:</strong> {{ $speech['text'] }}</p>
                        <div class="score-table">
                            <div><span class="tag">SpeechAce</span> {{ $speech['speechace_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">IELTS</span> {{ $speech['ielts_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">TOEIC</span> {{ $speech['toeic_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">PTE</span> {{ $speech['pte_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">CEFR</span> {{ $speech['cefr_pronunciation_score'] ?? 'N/A' }}</div>
                        </div>
                        @if(isset($speech['uploaded_audio']['url']))
                            <audio controls src="{{ $speech['uploaded_audio']['url'] }}"></audio>
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>
            @endif

            {{-- Auditory Assessment --}}
            @if(isset($enrollee['Assessment']['Speech_Auditory']['Auditory']['results']))
            <div class="assessment-group">
                <div class="assessment-type">🎧 Auditory Assessment</div>
                @foreach($enrollee['Assessment']['Speech_Auditory']['Auditory']['results'] as $result)
                    @if(is_array($result) && isset($result['expected']))
                    <div class="assessment-card">
                        <h5>Auditory Item</h5>
                        <div class="auditory-row"><strong>Expected:</strong> {{ $result['expected'] }}</div>
                        <div class="auditory-row"><strong>User Input:</strong> {{ $result['user_input'] }}</div>
                        <div class="auditory-row"><strong>Score:</strong> {{ $result['score'] }} <span class="tag">{{ ucfirst($result['assessment']) }}</span></div>
                        <div class="auditory-row"><strong>Replay Count:</strong> {{ $result['replay_count'] }}</div>
                        <div class="auditory-row"><strong>Reaction Time:</strong> {{ $result['reaction_time_seconds'] }}s</div>
                    </div>
                    @endif
                @endforeach
            </div>
            @endif

            {{-- Fill-in-the-Blanks --}}
            @if(isset($enrollee['Assessment']['fillblanks']))
            <div class="assessment-group">
                <div class="assessment-type">✍️ Fill in the Blanks</div>
                @foreach($enrollee['Assessment']['fillblanks'] as $fb)
                    @if(is_array($fb) && isset($fb['text']))
                    <div class="assessment-card">
                        <h5>Fill Item</h5>
                        <p><strong>Text:</strong> {{ $fb['text'] }}</p>
                        <div class="score-table">
                            <div><span class="tag">SpeechAce</span> {{ $fb['speechace_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">IELTS</span> {{ $fb['ielts_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">TOEIC</span> {{ $fb['toeic_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">PTE</span> {{ $fb['pte_pronunciation_score'] ?? 'N/A' }}</div>
                            <div><span class="tag">CEFR</span> {{ $fb['cefr_pronunciation_score'] ?? 'N/A' }}</div>
                        </div>
                        @if(isset($fb['audio_url']))
                            <audio controls src="{{ $fb['audio_url'] }}"></audio>
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>
            @endif
        </div>



             <!-- Navigation Buttons -->
            <div class="form-row">
                <div class="button-group">
                <button type="button" class="btn add-btn" id="prevBtn" style="display: none;">← Previous</button>
                <button type="button" class="btn add-btn" id="nextBtn">Next →</button>
                </div>
            </div>

            <!-- Teacher Action Section -->
                <form id="teacherActionForm" method="POST" action="{{ route('mio.update-enrollee', ['id' => $id]) }}" data-status="{{ $enrollee['enroll_status'] }}">
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
                        <option value="Qualified" {{ $enrollee['enroll_status'] == 'Qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="Enrolled" {{ $enrollee['enroll_status'] == 'Enrolled' ? 'selected' : '' }}>Enrolled</option>
                        <option value="Rejected" {{ $enrollee['enroll_status'] == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="button-group">
                        <button type="button" class="btn cancel-btn">
                            <a href="{{ route("mio.enrollment") }}">Cancel</a>
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

<!-- ----- SCRIPTS ----- -->

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

    // Firebase enrollment_form data with codes:
    const firebaseEnrollmentForm = {
        region: "010000000",
        province: "012900000",
        city: "012903000",
        barangay: "012903006",
        address: "456 Sample St.",
        zip_code: "1000"
    };

    async function fetchData(url) {
        const res = await fetch(url);
        return res.json();
    }

    // Utility to find name by code from a list
    function findNameByCode(list, code) {
        const item = list.find(i => i.code === code);
        return item ? item.name : "";
    }

    document.addEventListener('DOMContentLoaded', async () => {
        // Set region name from the map immediately
        document.getElementById('region').value = regionNameMap[firebaseEnrollmentForm.region] || "";

        // Fetch provinces under region and find province name
        const provinces = await fetchData(`${apiBase}/regions/${firebaseEnrollmentForm.region}/provinces/`);
        document.getElementById('province').value = findNameByCode(provinces, firebaseEnrollmentForm.province);

        // Fetch cities under province and find city name
        const cities = await fetchData(`${apiBase}/provinces/${firebaseEnrollmentForm.province}/cities-municipalities/`);
        document.getElementById('city').value = findNameByCode(cities, firebaseEnrollmentForm.city);

        // Fetch barangays under city and find barangay name
        const barangays = await fetchData(`${apiBase}/cities-municipalities/${firebaseEnrollmentForm.city}/barangays/`);
        document.getElementById('barangay').value = findNameByCode(barangays, firebaseEnrollmentForm.barangay);

        // Set address and zip code
        document.querySelector("input[name='address']").value = firebaseEnrollmentForm.address;
        document.querySelector("input[name='zip_code']").value = firebaseEnrollmentForm.zip_code;
    });
</script>

<script>
  function previewFile(filePath) {
    const previewContainer = document.getElementById('filePreviewContainer');
    const previewFrame = document.getElementById('previewFrame');
    const previewLoader = document.getElementById('previewLoader');

    // Toggle off if same file clicked again
    if (previewContainer.style.display !== 'none' && previewFrame.src === filePath) {
      previewFrame.src = '';
      previewContainer.style.display = 'none';
      previewLoader.style.display = 'none';
      return;
    }

    // Show container and loader
    previewContainer.style.display = 'block';
    previewLoader.style.display = 'block';

    // Clear current src first to retrigger load event
    previewFrame.src = '';

    // Set new src after a tiny delay to ensure load event fires
    setTimeout(() => {
      previewFrame.src = filePath;
    }, 50);

    // When iframe finishes loading, hide the loader
    previewFrame.onload = () => {
      previewLoader.style.display = 'none';
    };
  }
</script>

<script>
  let currentPage = 1;
  const totalPages = 2;

  document.getElementById('nextBtn').addEventListener('click', () => {
    if (currentPage < totalPages) {
      document.getElementById(`page-${currentPage}`).style.display = 'none';
      currentPage++;
      document.getElementById(`page-${currentPage}`).style.display = 'block';
      updateButtons();
    }
  });

  document.getElementById('prevBtn').addEventListener('click', () => {
    if (currentPage > 1) {
      document.getElementById(`page-${currentPage}`).style.display = 'none';
      currentPage--;
      document.getElementById(`page-${currentPage}`).style.display = 'block';
      updateButtons();
    }
  });

  function updateButtons() {
    document.getElementById('prevBtn').style.display = currentPage === 1 ? 'none' : 'inline-block';
    document.getElementById('nextBtn').style.display = currentPage === totalPages ? 'none' : 'inline-block';
  }
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('teacherActionForm');
    const currentStatus = form.dataset.status;

    form.addEventListener('submit', function (e) {
        if (currentStatus === 'Enrolled') {
            e.preventDefault();
            alert('This enrollee is already enrolled. You cannot make further changes.');
        }
    });
});
</script>


<!-- STYLES -->

<style>
.assessment-section {
    background: #f4f6f9;
    padding: 25px;
    border-radius: 16px;
    font-family: 'Segoe UI', sans-serif;
    color: #2d3436;
}

.section-header {
    font-size: 26px;
    font-weight: bold;
    margin-bottom: 30px;
    border-bottom: 3px solid #dcdde1;
    padding-bottom: 10px;
}

.assessment-group {
    margin-bottom: 35px;
}

.assessment-type {
    font-size: 22px;
    font-weight: 600;
    color: #353b48;
    margin-bottom: 20px;
    border-left: 6px solid #00a8ff;
    padding-left: 10px;
}

.assessment-card {
    background-color: #ffffff;
    border: 1px solid #dcdde1;
    border-left: 4px solid #40739e;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 5px rgba(0,0,0,0.05);
}

.assessment-card h5 {
    font-size: 18px;
    font-weight: 600;
    color: #2f3640;
    margin-bottom: 12px;
}

.assessment-card p {
    margin: 6px 0;
}

.score-table {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 8px;
    margin: 10px 0 14px;
}

.tag {
    display: inline-block;
    background-color: #dcdde1;
    color: #2f3640;
    border-radius: 4px;
    padding: 2px 8px;
    font-size: 13px;
    margin-right: 6px;
}

audio {
    margin-top: 10px;
    width: 100%;
}

.auditory-row {
    margin-bottom: 5px;
}

</style>



