@php
    $auditoryScore = $enrollee['Assessment']['Speech_Auditory']['Auditory']['speech_reception']['overall_score'] ?? null;

    function mapStandardScores($score) {
        if ($score >= 90) {
            return ['IELTS' => '8.5–9.0', 'TOEIC' => '900–990', 'PTE' => '85–90', 'CEFR' => 'C2'];
        } elseif ($score >= 75) {
            return ['IELTS' => '6.5–7.5', 'TOEIC' => '785–859', 'PTE' => '65–78', 'CEFR' => 'B2'];
        } elseif ($score >= 60) {
            return ['IELTS' => '5.5–6.0', 'TOEIC' => '605–784', 'PTE' => '50–64', 'CEFR' => 'B1'];
        } elseif ($score >= 45) {
            return ['IELTS' => '4.0–5.0', 'TOEIC' => '405–604', 'PTE' => '36–49', 'CEFR' => 'A2'];
        } else {
            return ['IELTS' => '<4.0', 'TOEIC' => '<405', 'PTE' => '<36', 'CEFR' => 'A1'];
        }
    }

    $standardScores = $auditoryScore !== null ? mapStandardScores($auditoryScore) : null;
@endphp

<section class="home-section">
  <div class="text">View Enrollee</div>
  <div class="teacher-container">
    <div class="button-group">
                        <button id="generateReportBtn" class="primary-btn btn" style="margin-bottom: 1rem; margin-left: 3rem">
                            Generate Printable Report
                        </button>
                    </div>
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
                                <input type="text" name="parent_firstname" value="{{ $enrollee['enrollment_form']['parent_firstname'] ?? "N/A" }}" placeholder="Parent First Name" required />
                            </div>

                            <div class="form-group wide">
                                <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" name="parent_lastname" value="{{ $enrollee['enrollment_form']['parent_lastname'] ?? "N/A" }}" placeholder="Parent Last Name" required />
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
                                            <input type="text" name="medical_history" placeholder="E.g. Asthma, Allergies, etc." value="{{ $enrollee['enrollment_form']['medical_history'] ?? '' }}" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label>Type of Hearing Loss (if applicable)</label>
                                            <input type="text" name="hearing_loss" placeholder="E.g. Sensorineural, Conductive, Mixed" value="{{ $enrollee['enrollment_form']['hearing_loss'] ?? ''  }}"  readonly/>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Do you identify as? <span style="color: red; font-weight:700">*</span></label>
                                        <input type="text" value="{{ $enrollee['enrollment_form']['hearing_identity'] ?? ''  }}"  readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Assistive Devices Used</label>
                                            <input type="text" name="assistive_devices" placeholder="E.g. Hearing Aid, Cochlear Implant, None" value="{{ $enrollee['enrollment_form']['assistive_devices'] ?? ''  }}" readonly/>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group wide">
                                            <label>Other Notes or Health Concerns</label>
                                            <input type="text" name="health_notes" placeholder="Specify any other relevant information" value="{{ $enrollee['enrollment_form']['health_notes'] ?? ''  }}" />
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
                        <p><strong>Text:</strong>
                            @foreach ($reading['words'] as $word)
                                <span class="clickable-word" data-word='@json($word)'>{{ $word['word'] }}</span>
                            @endforeach
                        </p>

                        <div class="score-table">
                        <div>
                            <span class="tag" title="MIÓ is the internal scoring system used to assess pronunciation accuracy.">MIÓ</span>
                            {{ $reading['speechace_pronunciation_score'] ?? 'N/A' }}
                        </div>
                        <div>
                            <span class="tag" title="IELTS (International English Language Testing System) assesses English proficiency for academic and migration purposes, using a band score from 0 to 9.">IELTS</span>
                            {{ $reading['ielts_pronunciation_score'] ?? 'N/A' }}
                        </div>
                        <div>
                            <span class="tag" title="TOEIC (Test of English for International Communication) measures the ability to use English in business and workplace environments. Scores range from 10 to 990.">TOEIC</span>
                            {{ $reading['toeic_pronunciation_score'] ?? 'N/A' }}
                        </div>
                        <div>
                            <span class="tag" title="PTE (Pearson Test of English) is an English proficiency test used globally for academic, migration, and work-related purposes.">PTE</span>
                            {{ $reading['pte_pronunciation_score'] ?? 'N/A' }}
                        </div>
                        <div>
                            <span class="tag" title="CEFR (Common European Framework of Reference for Languages) is a standard scale for describing language ability from A1 (Beginner) to C2 (Proficient).">CEFR</span>
                            {{ $reading['cefr_pronunciation_score'] ?? 'N/A' }}
                        </div>
                    </div>

                        @if(isset($reading['uploaded_audio']['url']))
                            <audio controls src="{{ $reading['uploaded_audio']['url'] }}"></audio>
                        @endif
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
                        <p><strong>Text:</strong>
                            @php
                                $words = explode(' ', $speech['text']);
                                $wordData = $speech['words'] ?? [];
                            @endphp
                            @foreach($words as $index => $word)
                                @php
                                    $cleanWord = preg_replace('/[^\w\-\'’]/u', '', $word); // Remove punctuation for matching
                                    $wordMatch = collect($wordData)->firstWhere('word', $cleanWord);
                                @endphp
                                @if($wordMatch)
                                    <span class="clickable-word" data-word='@json($wordMatch)'>
                                        {{ $word }}
                                    </span>
                                @else
                                    {{ $word }}
                                @endif
                            @endforeach
                        </p>

                        <div class="score-table">
                        <div>
                            <span class="tag" title="MIÓ is the internal scoring system used to assess pronunciation accuracy.">MIÓ</span>
                            {{ $speech['speechace_pronunciation_score'] ?? 'N/A' }}
                        </div>
                        <div>
                            <span class="tag" title="IELTS (International English Language Testing System) assesses English proficiency for academic and migration purposes, using a band score from 0 to 9.">IELTS</span>
                            {{ $speech['ielts_pronunciation_score'] ?? 'N/A' }}
                        </div>
                        <div>
                            <span class="tag" title="TOEIC (Test of English for International Communication) measures the ability to use English in business and workplace environments. Scores range from 10 to 990.">TOEIC</span>
                            {{ $speech['toeic_pronunciation_score'] ?? 'N/A' }}
                        </div>
                        <div>
                            <span class="tag" title="PTE (Pearson Test of English) is an English proficiency test used globally for academic, migration, and work-related purposes.">PTE</span>
                            {{ $speech['pte_pronunciation_score'] ?? 'N/A' }}
                        </div>
                        <div>
                            <span class="tag" title="CEFR (Common European Framework of Reference for Languages) is a standard scale for describing language ability from A1 (Beginner) to C2 (Proficient).">CEFR</span>
                            {{ $speech['cefr_pronunciation_score'] ?? 'N/A' }}
                        </div>
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

                    @if($standardScores)
                        <div class="assessment-card">
                            <h5>Auditory Performance Scores</h5>
                            <div class="score-table">
                                <div>
                                    <span class="tag" title="MIÓ is the internal scoring system used to assess listening comprehension.">MIÓ</span>
                                    {{ $standardScores['MIO'] ?? 'N/A' }}
                                </div>
                                <div>
                                    <span class="tag" title="IELTS (International English Language Testing System) assesses English listening and understanding skills.">IELTS</span>
                                    {{ $standardScores['IELTS'] }}
                                </div>
                                <div>
                                    <span class="tag" title="TOEIC (Test of English for International Communication) evaluates real-world listening skills.">TOEIC</span>
                                    {{ $standardScores['TOEIC'] }}
                                </div>
                                <div>
                                    <span class="tag" title="PTE (Pearson Test of English) covers listening as part of its overall score.">PTE</span>
                                    {{ $standardScores['PTE'] }}
                                </div>
                                <div>
                                    <span class="tag" title="CEFR (Common European Framework of Reference) indicates language proficiency levels from A1 to C2.">CEFR</span>
                                    {{ $standardScores['CEFR'] }}
                                </div>
                            </div>
                        </div>
                    @endif

            {{-- Grid-style Word Feedback --}}
            <div class="results-grid">
                @foreach($enrollee['Assessment']['Speech_Auditory']['Auditory']['results'] as $result)
                    @if(is_array($result) && isset($result['expected']))
                        <div class="assessment-card">
                            <div class="card-header">Item #{{ $result['index'] + 1 }}</div>

                            <div class="grid-pair">
                                <div><strong>Correct:</strong><br><span class="tag">{{ $result['expected'] }}</span></div>
                                <div><strong>Response:</strong><br><span class="tag">{{ $result['user_input'] }}</span></div>
                            </div>

                            <div class="grid-pair">
                                <div>
                                    <strong>Match:</strong><br>
                                    <span class="tag" style="background-color: {{ $result['match'] ? '#00b894' : '#d63031' }}; color: white;">
                                        {{ $result['match'] ? 'Matched' : 'Not Matched' }}
                                    </span>
                                </div>
                                <div>
                                    <strong>Status:</strong><br>
                                    <span class="tag" style="background-color: {{ $result['assessment'] === 'pass' ? '#55efc4' : '#fab1a0' }}; color: black;">
                                        {{ ucfirst($result['assessment']) }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid-pair">
                                <div><strong>Score:</strong><br>{{ $result['score'] }}/100</div>
                                <div><strong>Replay Count:</strong><br>{{ $result['replay_count'] }}</div>
                            </div>

                            <div class="grid-pair">
                                <div><strong>Reaction Time:</strong><br>{{ $result['reaction_time_seconds'] }}s</div>
                                <div><strong>Volume Level:</strong><br>{{ number_format($result['volume_level'] * 100) }}%</div>
                            </div>


                            @if(!empty($result['extra_words']))
                            <hr style="border-color: #00a8ff;">

                                <div class="grid-block">
                                    <strong>Extra Words:</strong><br>
                                    @foreach($result['extra_words'] as $word)
                                        <span class="tag" style="background-color: #ffeaa7;">{{ $word }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($result['missing_words']))
                                <div class="grid-block">
                                    <strong>Missing Words:</strong><br>
                                    @foreach($result['missing_words'] as $word)
                                        <span class="tag" style="background-color: #fab1a0;">{{ $word }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
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
                        <p><strong>Text:</strong>
                            @php
                                $words = explode(' ', $fb['text']);
                                $wordData = $fb['words'] ?? [];
                            @endphp
                            @foreach($words as $index => $word)
                                @php
                                    $cleanWord = preg_replace('/[^\w\-\'’]/u', '', $word); // Remove punctuation for matching
                                    $wordMatch = collect($wordData)->firstWhere('word', $cleanWord);
                                @endphp
                                @if($wordMatch)
                                    <span class="clickable-word" data-word='@json($wordMatch)'>
                                        {{ $word }}
                                    </span>
                                @else
                                    {{ $word }}
                                @endif
                            @endforeach
                        </p>

                        <div class="score-table">
                            <div><span class="tag">MIÓ</span> {{ $fb['speechace_pronunciation_score'] ?? 'N/A' }}</div>
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


<div id="wordModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2 class="modal-title" id="modalWord"></h2>

    <div id="phonemeBoxes" class="phoneme-boxes"></div>

    <div id="phonemeDetails"></div>
  </div>
</div>


<!-- ----- SCRIPTS ----- -->

<!-- GENERATE REPORT -->

<script>
    document.getElementById('generateReportBtn').addEventListener('click', () => {
        const combinedData = [];

        @if(isset($enrollee['Assessment']['Reading']))
            @php
                $readings = collect($enrollee['Assessment']['Reading'])
                    ->filter(fn($r) => is_array($r) && isset($r['words']))
                    ->map(fn($r) => array_merge($r, ['type' => 'Reading']))->values();
            @endphp
            combinedData.push(...@json($readings));
        @endif

        @if(isset($enrollee['Assessment']['Speech_Auditory']['Speech']))
            @php
                $speech = collect($enrollee['Assessment']['Speech_Auditory']['Speech'])
                    ->filter(fn($s) => is_array($s) && isset($s['words']))
                    ->map(fn($s) => array_merge($s, ['type' => 'Speech']))->values();
            @endphp
            combinedData.push(...@json($speech));
        @endif

        @if(isset($enrollee['Assessment']['fillblanks']))
            @php
                $fillblanks = collect($enrollee['Assessment']['fillblanks'])
                    ->filter(fn($f) => is_array($f) && isset($f['text']))
                    ->map(fn($f) => array_merge($f, ['type' => 'Fill-in-the-Blanks']))->values();
            @endphp
            combinedData.push(...@json($fillblanks));
        @endif

        @if(isset($enrollee['Assessment']['Speech_Auditory']['Auditory']))
        @php
            $auditory = $enrollee['Assessment']['Speech_Auditory']['Auditory'];
            $auditory['type'] = 'Auditory';
        @endphp
        combinedData.push(@json($auditory));
    @endif


        if (!combinedData.length) {
            return alert('No assessment data available.');
        }

        const now = new Date().toLocaleString();
        const win = window.open('', '', 'width=1000,height=800');
        if (!win) return alert('Popup blocked! Please allow popups.');

        function getScoreClass(score) {
            score = score ?? 0;
            if (score < 50) return 'red';
            if (score < 70) return 'orange-red';
            if (score < 86) return 'yellow-green';
            return 'green';
        }

        function stressLabel(level) {
            switch (level) {
                case 1: return 'Primary';
                case 2: return 'Secondary';
                case 0: return 'Unstressed';
                default: return 'Unknown';
            }
        }



        function renderAssessmentBlock(data, index) {
            let presentPhones = [];
            let practicePhones = [];
            let missingPhones = [];

            if (data.type === 'Auditory') {
                return `
                    <div class="report-box">
                        <h2>Auditory Assessment</h2>

                        <div class="score-section">
                            <h3>Performance Scores</h3>
                            <table>
                                <tr><th>Speech Reception</th><td>${data.speech_reception?.overall_score ?? 'N/A'} / ${data.speech_reception?.max_possible ?? 'N/A'}</td></tr>
                                <tr><th>Word Recognition</th><td>${data.word_recognition_score?.correct_words ?? 0} of ${data.word_recognition_score?.total_words ?? 0} (${data.word_recognition_score?.percent_correct ?? 0}%)</td></tr>
                            </table>
                        </div>

                        <div class="summary-section">
                            <h3>Individual Results</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Expected</th>
                                        <th>User Input</th>
                                        <th>Match</th>
                                        <th>Score</th>
                                        <th>Reaction Time</th>
                                        <th>Replay</th>
                                        <th>Volume</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.results?.map(r => `
                                        <tr>
                                            <td>${r.index + 1}</td>
                                            <td>${r.expected}</td>
                                            <td>${r.user_input}</td>
                                            <td style="color: ${r.match ? 'green' : 'red'}; font-weight: bold;">
                                                ${r.match ? '✔' : '✘'}
                                            </td>
                                            <td>${r.score}</td>
                                            <td>${r.reaction_time_seconds}s</td>
                                            <td>${r.replay_count}</td>
                                            <td>${Math.round(r.volume_level * 100)}%</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }


            data.words.forEach(w => {
                w.phones?.forEach(p => {
                    const score = p.quality_score ?? 0;
                    const phoneData = {
                        phone: p.phone,
                        score: score,
                        sound_most_like: p.sound_most_like ?? null
                    };

                    if (score > 70) {
                        presentPhones.push(phoneData);
                    } else if (score >= 31 && score <= 70) {
                        practicePhones.push(phoneData);
                    } else {
                        missingPhones.push(phoneData);
                    }
                });
            });

            const totalPhones = presentPhones.length + missingPhones.length;
            const phonemeAccuracy = totalPhones > 0 ? ((presentPhones.length / totalPhones) * 100).toFixed(1) : '0.0';

            const allPhones = [...presentPhones, ...practicePhones, ...missingPhones];
            const sortedLowPhones = allPhones
                .sort((a, b) => a.score - b.score)
                .slice(0, 5)
                .map(p => `${p.phone} (${p.score.toFixed(1)})`);

             if (data.type === 'Fill-in-the-Blanks') {
                return `
                    <div class="report-box">
                        <h2>Fill-in-the-Blanks Assessment</h2>

                        ${data.text ? `
                            <div style="
                                margin-top: 10px;
                                border: 2px dashed #1a4fa3;
                                background-color: #eef3fc;
                                padding: 12px 16px;
                                border-radius: 10px;
                                font-size: 16px;
                                font-weight: bold;
                                color: #1a4fa3;
                                display: inline-block;
                            ">
                                "${data.text}"
                            </div>
                        ` : ''}

                        <div class="score-section">
                            <h3>Pronunciation Scores</h3>
                            <table>
                                <tr><th>MIÓ</th><td>${data.speechace_pronunciation_score ?? 'N/A'}</td></tr>
                                <tr><th>IELTS</th><td>${data.ielts_pronunciation_score ?? 'N/A'}</td></tr>
                                <tr><th>TOEIC</th><td>${data.toeic_pronunciation_score ?? 'N/A'}</td></tr>
                                <tr><th>PTE</th><td>${data.pte_pronunciation_score ?? 'N/A'}</td></tr>
                                <tr><th>CEFR</th><td>${data.cefr_pronunciation_score ?? 'N/A'}</td></tr>
                            </table>
                        </div>

                        ${data.words ? `
                            <div class="phoneme-breakdown">
                                <h3>Phoneme Breakdown</h3>
                                <table class="phoneme-table">
                                    <thead>
                                        <tr>
                                            <th>Word</th>
                                            <th>Phonemes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.words.map(w => `
                                            <tr>
                                                <td><strong>${w.word}</strong></td>
                                                <td>
                                                    ${w.phones?.map(p => `
                                                        <span class="phoneme-tag ${getScoreClass(p.quality_score)}">
                                                            ${p.phone}
                                                            ${p.sound_most_like && p.sound_most_like !== p.phone ? ` → ${p.sound_most_like}` : ''}
                                                            <sup>${p.quality_score?.toFixed(1) ?? 0}</sup>
                                                        </span>
                                                    `).join(' ') ?? ''}
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        ` : ''}
                    </div>
                `;
            }

            return `
            <div class="report-box">
                <h2>${data.type} Assessment</h2>

                ${data.text ? `
                    <div style="
                        margin-top: 10px;
                        border: 2px dashed #1a4fa3;
                        background-color: #eef3fc;
                        padding: 12px 16px;
                        border-radius: 10px;
                        font-size: 16px;
                        font-weight: bold;
                        color: #1a4fa3;
                        display: inline-block;
                    ">
                        "${data.text}"
                    </div>
                ` : ''}

                <div class="score-section">
                    <h3>Pronunciation Scores</h3>
                    <table>
                        <tr><th>MIÓ</th><td>${data.speechace_pronunciation_score ?? 'N/A'}</td></tr>
                        <tr><th>IELTS</th><td>${data.ielts_pronunciation_score ?? 'N/A'}</td></tr>
                        <tr><th>TOEIC</th><td>${data.toeic_pronunciation_score ?? 'N/A'}</td></tr>
                        <tr><th>PTE</th><td>${data.pte_pronunciation_score ?? 'N/A'}</td></tr>
                        <tr><th>CEFR</th><td>${data.cefr_pronunciation_score ?? 'N/A'}</td></tr>
                    </table>
                </div>

                <div class="summary-section">
                    <h3>Summary</h3>
                    <p><strong>Total Words:</strong> ${data.words.length}</p>
                    <p><strong>Total Phonemes:</strong> ${totalPhones}</p>
                    <p><strong>Phoneme Accuracy:</strong> ${phonemeAccuracy}%</p>
                    <p><strong>Most Problematic:</strong> ${sortedLowPhones.join(', ') || 'None'}</p>
                </div>

                <div class="phoneme-breakdown">
                    <h3>Phoneme Breakdown</h3>
                    <table class="phoneme-table">
                        <thead>
                            <tr>
                                <th>Word</th>
                                <th>Phonemes</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.words.map(w => `
                                <tr>
                                    <td><strong>${w.word}</strong></td>
                                    <td>
                                        ${w.phones?.map(p => `
                                            <span class="phoneme-tag ${getScoreClass(p.quality_score)}">
                                                ${p.phone}
                                                ${p.sound_most_like && p.sound_most_like !== p.phone ? ` → ${p.sound_most_like}` : ''}
                                                <sup>${p.quality_score?.toFixed(1) ?? 0}</sup>
                                            </span>

                                        `).join(' ') ?? ''}

                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>

                <div class="stress-analysis">
                    <h3>Lexical Stress Accuracy (Word-Level)</h3>
                    <table class="stress-table">
                        <thead>
                            <tr>
                                <th>Word</th>
                                <th>Expected Stress</th>
                                <th>Predicted Stress</th>
                                <th>Stress Match</th>
                                <th>Stress Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.words.map(w => {
                                const syl = (w.syllables || [])[0]; // Get first syllable info (most cases are monosyllabic or one main stress)
                                if (!syl) return '';

                                const expected = stressLabel(syl.stress_level);
                                const predicted = stressLabel(syl.predicted_stress_level);
                                const match = syl.stress_level === syl.predicted_stress_level
                                    ? '<span style="color: green; font-weight: bold;">✓</span>'
                                    : '<span style="color: red; font-weight: bold;">✗</span>';
                                const score = syl.stress_score ?? 'N/A';

                                return `
                                    <tr>
                                        <td>${w.word}</td>
                                        <td>${expected}</td>
                                        <td>${predicted}</td>
                                        <td style="text-align: center;">${match}</td>
                                        <td>${score}</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>


                <div class="phoneme-details">
                <h3>Phoneme Details</h3>

                <p><strong>Elements Present:</strong><br>
                    ${presentPhones.map(p => {
                        const actual = p.sound_most_like && p.sound_most_like !== p.phone ? ` → ${p.sound_most_like}` : '';
                        return `<span class="phoneme-tag green">${p.phone}${actual} (${p.score.toFixed(1)})</span>`;
                    }).join(' ') || 'None'}
                </p>

                <p><strong>Elements Need Practice:</strong><br>
                    ${practicePhones.map(p => {
                        const actual = p.sound_most_like && p.sound_most_like !== p.phone ? ` → ${p.sound_most_like}` : '';
                        return `<span class="phoneme-tag yellow-green">${p.phone}${actual} (${p.score.toFixed(1)})</span>`;
                    }).join(' ') || 'None'}
                </p>

                <p><strong>Elements Missing:</strong><br>
                    ${missingPhones.map(p => {
                        const actual = p.sound_most_like && p.sound_most_like !== p.phone ? ` → ${p.sound_most_like}` : '';
                        return `<span class="phoneme-tag red">${p.phone}${actual} (${p.score.toFixed(1)})</span>`;
                    }).join(' ') || 'None'}
                </p>
            </div>

            </div>
            `;
        }

        function renderSpeechProfileAndRecommendations(dataList) {
    const profile = {
        'Direct Imitation Skills': false,
        'Auditory Skills': false,
        'Mouth Formation Skills': false,
        'Rhythm Skills': false,
        'Word Concepts': false,
        'Focus': false,
        'Behavior': false,
        'Mental Alertness': false,
    };

    const profileRemarks = {};
    const recommendations = {
        '1-on-1 Speech Therapy': false,
        'Occupational Therapy': false,
        'Academic Level': true,
        'Remedial': false,
    };

    let remarks = [];
    let mainRec = null;
    let backupRec = null;

    // Aggregate phoneme quality
    let totalPhonemes = 0;
    let correctPhonemes = 0;

    dataList.forEach(data => {
        if (['Speech', 'Reading', 'Fill-in-the-Blanks'].includes(data.type)) {
            const phonesInEntry = (data.words || []).reduce((sum, w) => sum + (w.phones?.length || 0), 0);
            const correctInEntry = (data.words || []).reduce((sum, w) =>
                sum + (w.phones?.filter(p => p.quality_score > 70)?.length || 0), 0);

            totalPhonemes += phonesInEntry;
            correctPhonemes += correctInEntry;
        }
    });

    const avgPhonemeAccuracy = totalPhonemes ? (correctPhonemes / totalPhonemes) * 100 : 0;

    // --- Skill: Direct Imitation ---
    if (avgPhonemeAccuracy < 60) {
        profile['Direct Imitation Skills'] = true;
        profileRemarks['Direct Imitation Skills'] =
            `Phoneme accuracy (${avgPhonemeAccuracy.toFixed(1)}%) is significantly low across speech/reading tasks, showing difficulty imitating model sounds.`;

        profile['Mouth Formation Skills'] = true;
        profileRemarks['Mouth Formation Skills'] =
            `Articulation is weak with ${avgPhonemeAccuracy.toFixed(1)}% phoneme clarity. This suggests oral motor planning or articulation issues.`;

        recommendations['1-on-1 Speech Therapy'] = true;

        remarks.push(`Low phoneme accuracy (${avgPhonemeAccuracy.toFixed(1)}%) across tasks suggests the student struggles with imitating and producing sounds correctly.`);

    } else if (avgPhonemeAccuracy < 80) {
        profile['Mouth Formation Skills'] = true;
        profileRemarks['Mouth Formation Skills'] =
            `Moderate phoneme accuracy (${avgPhonemeAccuracy.toFixed(1)}%). Speech intelligibility may improve with articulation practice.`;

        remarks.push(`Moderate articulation performance (${avgPhonemeAccuracy.toFixed(1)}%). Training in speech sounds could boost clarity.`);
    } else {
        profileRemarks['Direct Imitation Skills'] = `Strong phoneme production (${avgPhonemeAccuracy.toFixed(1)}%). Good imitation skills.`;
        profileRemarks['Mouth Formation Skills'] = `Phonemes are clearly articulated in multiple tasks.`;
    }

    // --- Skill: Auditory Skills & Focus ---
    dataList.forEach(data => {
        if (data.type === 'Auditory' && data.word_recognition_score) {
            const audScore = data.word_recognition_score.percent_correct || 0;

            if (audScore < 60) {
                profile['Auditory Skills'] = true;
                profileRemarks['Auditory Skills'] =
                    `Only ${audScore}% word recognition. Difficulty identifying heard words accurately, likely auditory processing issues.`;

                profile['Focus'] = true;
                profileRemarks['Focus'] =
                    `Frequent replays and low accuracy may be caused by inattention or short auditory focus span.`;

                recommendations['Occupational Therapy'] = true;

                remarks.push(`Auditory score (${audScore}%) indicates possible auditory discrimination or attention difficulties.`);

            } else if (audScore < 85) {
                profile['Auditory Skills'] = true;
                profileRemarks['Auditory Skills'] =
                    `Fair auditory performance (${audScore}%). May miss subtle sound differences.`;

                remarks.push(`Auditory performance is borderline (${audScore}%). Consider monitoring auditory comprehension. `);
            } else {
                profileRemarks['Auditory Skills'] = `Excellent auditory recognition (${audScore}%). No immediate concerns.`;
                profileRemarks['Focus'] = `No signs of focus issues during auditory task (accuracy and replay behavior normal).`;
            }
        }
    });

    // --- Skill: Rhythm Skills ---
    const reading = dataList.find(d => d.type === 'Reading');
    if (reading?.speechace_pronunciation_score < 70) {
        profile['Rhythm Skills'] = true;
        profileRemarks['Rhythm Skills'] =
            `Reading task has low rhythm/intonation score (${reading.speechace_pronunciation_score}). Suggests issues with speech flow.`;
        remarks.push(`Low reading rhythm score (${reading.speechace_pronunciation_score}) suggests inconsistent prosody or intonation.`);
    } else {
        profileRemarks['Rhythm Skills'] = `Speech rhythm and intonation appear normal in reading task.`;
    }

    // --- Skill: Word Concepts ---
    const speech = dataList.find(d => d.type === 'Speech');
    if (speech && speech.words?.[0]?.word?.length < 2 && speech.speechace_pronunciation_score < 50) {
        profile['Word Concepts'] = true;
        profileRemarks['Word Concepts'] =
            `Basic word (“${speech.words[0].word}”) poorly pronounced (${speech.speechace_pronunciation_score}). Student may not fully grasp word sound shape.`;

        remarks.push(`Poor pronunciation of simple word “${speech.words[0].word}” (score: ${speech.speechace_pronunciation_score}) indicates conceptual issues.`);
    } else {
        profileRemarks['Word Concepts'] = `No significant difficulty in expressing or understanding word-level concepts.`;
    }

    // --- Remaining Traits ---
    ['Behavior', 'Mental Alertness'].forEach(trait => {
        profileRemarks[trait] = `No direct behavior or alertness issues were observed in the automated assessments.`;
    });

    // Fill in any empty profile remarks
    Object.entries(profile).forEach(([key, checked]) => {
        if (!profileRemarks[key]) {
            profileRemarks[key] = checked
                ? 'This trait requires improvement based on performance.'
                : 'No significant issues observed for this trait.';
        }
    });

    const ranked = Object.entries(recommendations)
        .filter(([_, checked]) => checked)
        .map(([rec]) => rec);

    mainRec = ranked[0] ?? 'Academic Level';
    backupRec = ranked[1] ?? (ranked[0] !== 'Academic Level' ? 'Academic Level' : null);

    return `
        <div class="section report-box">
            <h2>Speech Profile Analysis</h2>
            <table class="speech-profile-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="padding: 8px; border: 1px solid #ccc;">Skill</th>
                        <th style="padding: 8px; border: 1px solid #ccc;">Status</th>
                        <th style="padding: 8px; border: 1px solid #ccc;">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    ${Object.entries(profile).map(([key, checked]) => `
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ccc;">${key}</td>
                            <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">
                                <span style="display: inline-block; width: 22px; height: 22px; border-radius: 5px; font-weight: bold;
                                    background-color: ${checked ? '#ffdddd' : '#ddffdd'};
                                    color: ${checked ? '#a00' : '#090'};
                                    border: 2px solid ${checked ? '#a00' : '#090'};">
                                    ${checked ? '✘' : '✔'}
                                </span>
                            </td>
                            <td style="padding: 8px; border: 1px solid #ccc;">${profileRemarks[key]}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>

        <div class="section report-box">
            <h2>Recommendations</h2>
            <form>
                ${Object.entries(recommendations).map(([key, checked]) => `
                    <label style="display: block; margin: 4px 0;">
                        <input type="checkbox" ${checked ? 'checked' : ''} disabled>
                        ${key}
                    </label>
                `).join('')}
            </form>
        </div>

        <div class="section report-box">
            <h2>Main & Backup Recommendation</h2>
            <p><strong>Main Recommendation:</strong> ${mainRec}</p>
            <p><strong>Backup Recommendation:</strong> ${backupRec ?? 'None'}</p>
        </div>

        <div class="section report-box">
            <h2>Remarks / Justification</h2>
            <ul>
                ${remarks.map(r => `<li>${r}</li>`).join('')}
            </ul>
        </div>
    `;
}




        const html = `
        <html>
        <head>
            <title>Combined Assessment Report</title>
            <style>
                body { font-family: 'Segoe UI', sans-serif; margin: 40px; color: #333; }
                h1, h2, h3 { color: #1a4fa3; }
                h1 { border-bottom: 3px solid #1a4fa3; font-size: 28px; padding-bottom: 10px; }
                h2 { margin-top: 30px; font-size: 22px; }
                h3 { font-size: 18px; margin-top: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { padding: 8px 10px; border: 1px solid #ccc; }
                th { background: #f4f4f4; }
                .phoneme-tag {
                    display: inline-block;
                    background: #eef3fc;
                    color: #1a4fa3;
                    padding: 4px 8px;
                    margin: 3px 3px 0 0;
                    border-radius: 6px;
                    font-size: 13px;
                }
                .section { margin-bottom: 40px; }
                .footer { margin-top: 50px; font-size: 14px; }
                .report-box {
                    background-color: #ffffff;
                    border: 2px solid #dcdde1;
                    border-radius: 12px;
                    padding: 24px;
                    margin-bottom: 40px;
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
                }

                .score-section, .summary-section, .phoneme-breakdown, .phoneme-details, .audio-section {
                    margin-top: 20px;
                    padding: 16px;
                    background-color: #f8f9fa;
                    border-radius: 10px;
                }

                .score-section table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .score-section th, .score-section td {
                    border: 1px solid #ccc;
                    padding: 6px 10px;
                    background: #fff;
                }

                .word-breakdown {
                    background-color: #eef3fc;
                    padding: 8px 12px;
                    border-radius: 6px;
                    margin-bottom: 10px;
                }

                .phoneme-tag {
                    display: inline-block;
                    background: #d0e4ff;
                    color: #0d47a1;
                    padding: 3px 7px;
                    margin: 3px 3px 0 0;
                    border-radius: 5px;
                    font-size: 12px;
                }

                .phoneme-tag.red {
                    background-color: #f8d7da;
                    color: #a71d2a;
                    font-weight: bold;
                }
                .phoneme-tag.orange-red {
                    background-color: #ffe3d3;
                    color: #d35400;
                    font-weight: bold;
                }
                .phoneme-tag.yellow-green {
                    background-color: #fff9d0;
                    color: #9c8500;
                    font-weight: bold;
                }
                .phoneme-tag.green {
                    background-color: #d4edda;
                    color: #155724;
                    font-weight: bold;
                }

                .small-legend {
                    font-size: 15px;
                    width: auto;
                    margin-top: 10px;
                    border-collapse: collapse;
                    border: 1px solid #ccc;
                }
                .small-legend th, .small-legend td {
                    border: 1px solid #ccc;
                    text-align: center;
                }
                .small-legend th {
                    background-color: #f0f0f0;
                    font-weight: bold;
                }
                .info-box {
                    background-color: #f0f8ff;
                    border: 2px solid #1a4fa3;
                    border-radius: 10px;
                    padding: 20px;
                    margin-bottom: 30px;
                }

                .info-box h2 {
                    margin-top: 0;
                    color: #1a4fa3;
                    font-size: 20px;
                    border-bottom: 1px dashed #1a4fa3;
                    padding-bottom: 6px;
                }

                .info-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                    font-size: 15px;
                }

                .info-table th {
                    text-align: left;
                    background-color: #e6f0ff;
                    padding: 8px;
                    width: 25%;
                }

                .info-table td {
                    padding: 8px;
                    background-color: #ffffff;
                }

            </style>
        </head>
        <body>
            <h1>Combined Reading and Speech Assessment Report</h1>
             <div class="info-box">
                <h2>Student Information</h2>
                <table class="info-table">
                    <tr>
                        <th>First Name</th>
                        <td>{{ $enrollee['enrollment_form']['first_name'] }}</td>
                        <th>Last Name</th>
                        <td>{{ $enrollee['enrollment_form']['last_name'] }}</td>
                    </tr>
                    <tr>
                        <th>Age</th>
                        <td>{{ $enrollee['enrollment_form']['age'] }}</td>
                        <th>Hearing Identity</th>
                        <td>{{ $enrollee['enrollment_form']['hearing_identity'] }}</td>
                    </tr>
                </table>
            </div>
            ${renderSpeechProfileAndRecommendations(combinedData)}

            ${combinedData.map(renderAssessmentBlock).join('')}

            <hr>
            <div style="page-break-before: always;"></div>
            <div style="margin-top: 20px; opacity: 0.6; font-size: 12px; color: #666;">
                <h2 style="font-size: 16px; color: #444;">Scoring Legend</h2>
                <table class="small-legend">
                    <thead>
                        <tr>
                            <th>IELTS</th>
                            <th>CEFR</th>
                            <th>PTE</th>
                            <th>TOEIC</th>
                            <th>MIÓ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>9.0</td><td>C2</td><td>90</td><td>200</td><td>97–100</td></tr>
                        <tr><td>8.5</td><td>C2</td><td>90</td><td>190</td><td>92–96</td></tr>
                        <tr><td>8.0</td><td>C1+</td><td>85</td><td>180</td><td>86–91</td></tr>
                        <tr><td>7.5</td><td>C1</td><td>76</td><td>170</td><td>81–85</td></tr>
                        <tr><td>7.0</td><td>B2</td><td>68</td><td>160</td><td>75–80</td></tr>
                        <tr><td>6.5</td><td>B1+</td><td>59</td><td>140–150</td><td>69–74</td></tr>
                        <tr><td>6.0</td><td>B1</td><td>51</td><td>120–130</td><td>64–68</td></tr>
                        <tr><td>5.5</td><td>A2+</td><td>42</td><td>110</td><td>58–63</td></tr>
                        <tr><td>5.0</td><td>A2</td><td>34</td><td>90–100</td><td>53–57</td></tr>
                        <tr><td>4.5</td><td>A1+</td><td>25</td><td>80</td><td>47–52</td></tr>
                        <tr><td>4.0</td><td>A1</td><td>20</td><td>50–70</td><td>42–46</td></tr>
                        <tr><td>0–3.5</td><td>A0</td><td>10</td><td>0–40</td><td>0–41</td></tr>
                    </tbody>
                </table>
            </div>


            <div class="footer">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td><strong>Date:</strong> ${now}</td>
                        <td><strong>Assessed by:</strong> ____________________</td>
                        <td><strong>Noted by:</strong> ____________________</td>
                    </tr>
                </table>
                <p style="color: #777;">Generated automatically. Results subject to further validation by a speech-language professional.</p>
            </div>
        </body>
        </html>`;

        win.document.write(html);
        win.document.close();
        win.focus();
        win.print();
    });
</script>



<script>
    document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById('wordModal');
    const modalClose = modal.querySelector('.close');
    const modalWord = document.getElementById('modalWord');
    const phonemeBoxesEl = document.getElementById('phonemeBoxes');
    const phonemeDetailsEl = document.getElementById('phonemeDetails');

    // Close modal on X click
    modalClose.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Global click handler
    document.addEventListener('click', function(e) {
        const el = e.target.closest('.clickable-word');
        if (!el) return;

        let wordData;
        try {
        wordData = JSON.parse(el.dataset.word);
        } catch (err) {
        console.error("Invalid word data:", err);
        return;
        }

        // Set word title
        modalWord.innerText = wordData.word || '—';

        // Color function
        const getColor = (score) => {
        if (score === undefined || score === null) return '#bdc3c7';
        if (score >= 90) return '#3cb371';
        if (score >= 80) return '#32cd32';
        if (score >= 70) return '#ffa500';
        if (score >= 60) return '#ff4500';
        return '#ff0000';
        };

        // Phoneme colored boxes
        phonemeBoxesEl.innerHTML = (wordData.phones || []).map(p => {
        const score = p.quality_score ?? null;
        const color = getColor(score);
        return `
            <div class="phoneme-box" style="background-color: ${color}1A; border: 2px solid ${color}; color: ${color};">
            ${p.phone ?? '—'}
            </div>
        `;
        }).join('');

        // Phoneme detail cards
        phonemeDetailsEl.innerHTML = `
        <div>
            <div class="detail-header">Phoneme Details</div>
            <div class="phoneme-detail-list">
            ${(wordData.phones || []).map(p => {
                const score = p.quality_score ?? null;
                const color = getColor(score);
                return `
                <div class="phoneme-card" style="background-color: ${color}1A; border-left: 6px solid ${color};">
                    <div class="phoneme-title" style="color: ${color}; font-size: 20px;">${p.phone ?? '—'}</div>
                    <div class="phoneme-meta"><strong>Score:</strong> ${score?.toFixed(2) ?? 'N/A'}</div>
                    <div class="phoneme-meta"><strong>Sounds like:</strong> ${p.sound_most_like ?? 'N/A'}</div>
                    ${p.stress_score !== undefined ? `<div class="phoneme-meta"><strong>Stress:</strong> ${p.stress_score}</div>` : ''}
                </div>
                `;
            }).join('')}
            </div>
        </div>
        `;

        // Show modal
        modal.style.display = 'block';
    });
    });
</script>





<!-- STYLES -->


<style>

    .tag {
        pointer-events: auto; /* Ensure pointer events work */
        cursor: help;         /* Optional: adds '?' cursor for clarity */
        }

        .tooltip {
            position: relative;
            cursor: help;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            background-color: #333;
            color: #fff;
            text-align: left;
            padding: 8px;
            border-radius: 6px;
            position: absolute;
            z-index: 1000;
            bottom: 125%; /* position above */
            left: 50%;
            transform: translateX(-50%);
            white-space: normal;
            width: 250px;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

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

    .results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(520px, 1fr));
        gap: 1.5rem;
    }

    .grid-pair {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        gap: 10px;
    }

    .grid-pair > div {
        flex: 1;
    }

    .grid-block {
        margin-top: 0.75rem;
    }

    .card-header {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
        color: #2d3436;
        border-bottom: 1px solid #dfe6e9;
        padding-bottom: 0.5rem;
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

    .clickable-word {
        cursor: pointer;
        color: #2980b9;
        margin-right: 5px;
        font-weight: bold;
    }

    .modal {
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.6);
    }

    .modal-content {
        background-color: #fff;
        margin: 5% auto; /* slightly reduce margin for better vertical spacing */
        padding: 20px;
        width: 90%;
        max-width: 800px; /* increased from 400px to 800px */
        border-radius: 10px;
    }


    .close {
        float: right;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
    }

    .modal-title {
    text-align: center;
    font-size: 24px;
    margin-bottom: 20px;
    font-weight: 600;
    color: #2f3640;
    }

    .phoneme-boxes {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 30px;
    }

    .phoneme-box {
    font-size: 22px;
    font-weight: bold;
    padding: 12px 20px;
    border-radius: 10px;
    min-width: 50px;
    text-align: center;
    transition: transform 0.2s ease;
    }

    .phoneme-box:hover {
    transform: scale(1.05);
    }

    .detail-header {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #2d3436;
    border-bottom: 2px solid #dfe6e9;
    padding-bottom: 5px;
    }

    .phoneme-detail-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    }

    .phoneme-card {
    padding: 12px;
    border-radius: 8px;
    background-color: #ecf0f1;
    }

    .phoneme-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 6px;
    }

    .phoneme-meta {
    font-size: 14px;
    margin-bottom: 2px;
    }



</style>



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


