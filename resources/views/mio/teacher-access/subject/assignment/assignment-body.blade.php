<section class="home-section">
    <!-- 🟦 Header Banner -->
    <main class="main-banner">
        <div class="welcome-banner">
            <div class="banner">
                <div class="content">
                    <h5>Assignments</h5>
                </div>
            </div>
        </div>
    </main>

    <!-- 📄 Assignment Cards List -->
    <main class="main-assignment-content">
        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
       <div class="assignment-card">
            <div class="activity-info">
                <h1>{{ $assignment['title'] }}</h1>
            </div>

            <div class="details">
                 <div>
                    <span>Publish at</span>
                        <strong>{{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($assignment['published_at'])->format('Y-m-d') . ' ' . ($assignment['availability']['start'] ?? '00:00'))->format('F j, Y g:i A') }}</strong>
                </div>
                <div>
                <span>Deadline</span>
                <strong>
                    @if (!empty($assignment['deadline']))
                        {{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($assignment['deadline'])->format('Y-m-d') . ' ' . ($assignment['availability']['end'] ?? '00:00'))->format('F j, Y g:i A') }}
                    @else
                        No Due Date
                    @endif
                </strong>
            </div>

                <div>
                    <span>Points</span>
                    <strong>{{ $assignment['total'] ?? '0' }}</strong>
                </div>

                <div>
                    <span>Attempt/s</span>
                    <strong>{{ $assignment['attempts'] }}</strong>
                </div>
            </div>

            <!-- Assignment Description -->
            @if (!empty($assignment['description']))
                <div class="assignment-description" style="margin-top: 15px;">
                    <h4>Description</h4>
                    <p>{{ $assignment['description'] }}</p>
                </div>
            @endif

            <!-- Assignment Attachments -->
            @if (!empty($assignment['attachments']))
                <div class="assignment-attachments" style="margin-top: 15px;">
                    <h4>Attachments</h4>
                    <ul>
                        @foreach ($assignment['attachments'] as $attachment)
                            @if (!empty($attachment['file']))
                                <li>
                                    📎 <a href="{{ $attachment['file'] }}" target="_blank">{{ basename($attachment['file']) }}</a>
                                </li>
                            @endif
                            @if (!empty($attachment['link']))
                                <li>
                                    🔗 <a href="{{ $attachment['link'] }}" target="_blank">{{ $attachment['link'] }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

                <div class="edit-assignment-container">
                            <a href="#" class="primary-btn" id="openAssignmentModal">Edit Assignment</a>

                            @php
                                $submittedCount = 0;
                                $totalStudents = 0;

                                if (!empty($assignment['people'])) {
                                    $totalStudents = count($assignment['people']);
                                    foreach ($assignment['people'] as $student) {
                                        if (!empty($student['work'])) {
                                            $submittedCount++;
                                        }
                                    }
                                }
                            @endphp

                            <a href="#" class="secondary-btn" id="openReviewModal">
                                Submissions [{{ $submittedCount }} / {{ $totalStudents }}]
                            </a>




                            <form action="{{ route('mio.subject-teacher.deleteAssignment', ['subjectId' => $subjectId, 'assignmentId' => $assignmentId]) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" style="background: none; border: none; cursor: pointer;">
                                    <i class="fas fa-trash-alt" style="color: red; font-size: 20px;"></i>
                                </button>
                        </form>

                </div>
            </div>

    </main>

    <!-- /Edit Assignment Modal -->
    <div id="assignmentModal" class="modal assignment-modal" style="display: none;">
        <div class="modal-content">
            <span class="close" id="closeAssignmentModal">&times;</span>
            <h2 id="modalTitle">Edit Assignment</h2>

            <form id="assignmentForm" action="{{ route('mio.subject-teacher.assignment.edit', ['subjectId' => $subjectId, 'assignmentId' => $assignmentId]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <label for="title">Title</label>
                <input type="text" name="title" id="assignmentTitle" placeholder="Assignment Title" value="{{ $assignment['title'] ?? '' }}" required>

                <label for="description">Description</label>
                <textarea name="description" id="assignmentDescription" rows="3" placeholder="Write a brief description...">{{ $assignment['description'] ?? '' }}</textarea>

                <label>Attachments</label>
                <div id="attachment-container">
                @php $attachmentIndex = 0; @endphp
                @if (!empty($assignment['attachments']))
                    @foreach ($assignment['attachments'] as $attachment)
                        <div class="attachment-wrapper" style="margin-bottom: 15px;">
                            @if (!empty($attachment['file']))
                                <div>
                                    <p>Existing File: <a href="{{ $attachment['file'] }}" target="_blank">{{ basename($attachment['file']) }}</a></p>
                                </div>
                            @endif
                            <input type="file" name="attachments[{{ $attachmentIndex }}][file]" style="display:block; margin-bottom:5px;" />
                            <input type="url" name="attachments[{{ $attachmentIndex }}][link]" placeholder="Or paste a media URL (optional)" style="width:100%;" value="{{ $attachment['link'] ?? '' }}" />
                        </div>
                        @php $attachmentIndex++; @endphp
                    @endforeach
                @endif
            </div>
                <button type="button" id="add-attachment-btn" style="margin-top: 10px;">+ Add File or Link</button>

                <label for="publish_date">Publish Date</label>
                <input type="date" name="publish_date" id="publish_date" value="{{\Carbon\Carbon::parse($assignment['published_at'])->format('Y-m-d') }}" required>


                <label for="availability_start">Availability - Start Time</label>
                <input type="time" name="availability_start" id="assignmentAvailabilityStart" value="{{ $assignment['availability']['start'] ?? '' }}"  required>

                <label for="deadline">Deadline (Blank - No Due Date)</label>
                <input type="date" name="deadline" id="assignmentDeadline"
                    value="{{ isset($assignment['deadline']) && $assignment['deadline'] ? \Carbon\Carbon::parse($assignment['deadline'])->format('Y-m-d') : '' }}">


                <label for="availability_end">Availability - End Time</label>
                <input type="time" name="availability_end" id="assignmentAvailabilityEnd" value="{{ $assignment['availability']['end'] ?? '' }}" >

                <label for="points_total">Total Points</label>
                <input type="number" name="points_total" id="points_total" min="1" required class="no-spinner" value="{{ $assignment['total'] ?? 0 }}" placeholder="Total Points" required>

                <label for="attempts">Attempts</label>
                <input type="number" name="attempts" id="assignmentAttempts" value="{{ $assignment['attempts'] }}" min="1" required>

                <button type="submit">Save Assignment</button>
            </form>
        </div>
    </div>


    <!-- 🔍 Review Modal -->
    <div id="reviewModal" class="modal" style="display: none;">
        <div class="modal-content modal-styled">
            <span class="close" id="closeReviewModal">&times;</span>
            <h2>Review Assignment: {{ $assignment['title'] ?? 'Untitled' }}</h2>
            <div class="review-content">
                <h4>Student's Work Review</h4>

                <label for="studentSelect">Select Student:</label>
                    <select id="studentSelect" style="margin-bottom: 15px;">
                        @foreach ($assignment['people'] as $studentId => $student)
                            <option value="{{ $studentId }}">{{ $student['name'] }}</option>
                        @endforeach
                    </select>

                    <div id="submissionViewer">
                        <div id="workViewer">
                            <p>No submission selected.</p>
                        </div>
                    </div>

                    <div style="margin-top: 20px; text-align: center;">
                        <button id="prevStudentBtn">⟨ Previous</button>
                        <button id="nextStudentBtn">Next ⟩</button>
                    </div>

                    <form id="reviewForm" method="POST" action="{{ route('mio.subject-teacher.assignment.review-save', ['subjectId' => $subjectId, 'assignmentId' => $assignmentId, 'studentId' => $studentId]) }}">
                @csrf

                <input type="hidden" name="student_id" id="studentIdInput" value="">

                <label for="scoreInput">Score</label>
                <div style="display: flex; align-items: center; gap: 5px; margin-bottom: 10px;">
                    <input
                        type="number"
                        id="scoreInput"
                        name="score"
                        min="0"
                        max="{{ $assignment['points']['total'] ?? 0 }}"
                        step="0.01"
                        required
                        style="width: 80px;"
                    />
                    <span>/ {{ $assignment['points']['total'] ?? 0 }}</span>
                </div>

                <label for="feedback">Feedback</label>
                <textarea
                    name="feedback"
                    id="feedbackInput"
                    rows="4"
                    placeholder="Enter your feedback"
                    required
                    style="width: 100%;"
                ></textarea>

                <div style="text-align: center; margin-top: 15px;">
                    <button type="submit"  class="primary-btn">Save Review</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ⚙️ SCRIPTS -->
<script>
    function getTodayDateString() {
        const today = new Date();
        return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    }

    window.onload = function () {
        document.getElementById("assignmentDeadline").min = getTodayDateString();
    };

    // Open Assignment Modal
    document.getElementById("openAssignmentModal").addEventListener("click", function (e) {
        e.preventDefault();
        document.getElementById("assignmentModal").style.display = "block";
    });

    // Close Assignment Modal
    document.getElementById("closeAssignmentModal").addEventListener("click", function () {
        document.getElementById("assignmentModal").style.display = "none";
    });

    // Open Review Modal
    document.getElementById("openReviewModal").addEventListener("click", function (e) {
        e.preventDefault();
        document.getElementById("reviewModal").style.display = "block";
    });

    // Close Review Modal
    document.getElementById("closeReviewModal").addEventListener("click", function () {
        document.getElementById("reviewModal").style.display = "none";
    });

    // Close modals on outside click
    window.addEventListener("click", function (event) {
        const modals = document.querySelectorAll(".modal");
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const studentSelect = document.getElementById("studentSelect");
        const workViewer = document.getElementById("workViewer");
        const reviewForm = document.getElementById("reviewForm");
        const studentIdInput = document.getElementById("studentIdInput");

    const baseActionUrl = "{{ route('mio.subject-teacher.assignment.review-save', ['subjectId' => $subjectId, 'assignmentId' => $assignmentId, 'studentId' => 'STUDENT_ID_PLACEHOLDER']) }}";

        const prevBtn = document.getElementById("prevStudentBtn");
        const nextBtn = document.getElementById("nextStudentBtn");

        const people = @json($assignment['people']);

        const studentIds = Object.keys(people);

        if (!studentIds.length) {d
            workViewer.innerHTML = "<p>No students available.</p>";
            return;
        }

        let originalAction = reviewForm.getAttribute("action") || "";

        function updateReviewFields(studentId) {
            const student = people[studentId];
            if (!student) return;

            // Set hidden student ID input
            studentIdInput.value = studentId;

            // Fill score and feedback if present
            scoreInput.value = student.score || '';
            feedbackInput.value = student.feedback || '';
        }


        // Unified form action updater
        function updateFormAction(studentId) {
             const newAction = baseActionUrl.replace('STUDENT_ID_PLACEHOLDER', studentId);
            reviewForm.action = newAction;
            studentIdInput.value = studentId;
        }

        // Initial load for the first student
        if (studentSelect.value) {
            updateReviewFields(studentSelect.value);
        }

        // Initial load
        updateFormAction(studentSelect.value);


        let currentIndex = 0;

        function escapeHTML(str) {
            if (!str) return "";
            return str.replace(/[&<>"']/g, function (char) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                })[char];
            });
        }

        function renderStudentWork(studentId) {
            const student = people[studentId];
            const work = student?.work ?? '';

            let content = `<h4>${escapeHTML(student.name)}'s Submission</h4>`;

            if (!work) {
                content += "<p>No work submitted.</p>";
            } else if (/\.(jpeg|jpg|png|gif)$/i.test(work)) {
                content += `<img src="${work}" alt="Submitted image" style="max-width:100%; height:auto;">`;
            } else if (/\.(mp4|webm|ogg)$/i.test(work)) {
                content += `<video controls style="max-width:100%; height:auto;"><source src="${work}">Your browser does not support the video tag.</video>`;
            } else if (/\.(mp3|wav|ogg)$/i.test(work)) {
                content += `<audio controls><source src="${work}">Your browser does not support the audio element.</audio>`;
            } else if (/\.pdf$/i.test(work)) {
                content += `<embed src="${work}" type="application/pdf" width="100%" height="600px" />`;
            } else if (/^https?:\/\//i.test(work)) {
                content += `<a href="${work}" target="_blank">${work}</a>`;
            } else {
                content += `<p>${escapeHTML(work)}</p>`;
            }

            workViewer.innerHTML = content;

            if (studentSelect) studentSelect.value = studentId;
            updateFormAction(studentId);
            studentIdInput.value = studentId;
        }

        studentSelect.addEventListener("change", function () {
            const selectedId = this.value;
            currentIndex = studentIds.indexOf(selectedId);
            updateReviewFields(this.value);
            renderStudentWork(selectedId);
        });

         document.getElementById("prevStudentBtn").addEventListener("click", function (e) {
        e.preventDefault();
        const currentIndex = studentSelect.selectedIndex;
        if (currentIndex > 0) {
            studentSelect.selectedIndex = currentIndex - 1;
            studentSelect.dispatchEvent(new Event("change"));
        }
    });

    document.getElementById("nextStudentBtn").addEventListener("click", function (e) {
        e.preventDefault();
        const currentIndex = studentSelect.selectedIndex;
        if (currentIndex < studentSelect.options.length - 1) {
            studentSelect.selectedIndex = currentIndex + 1;
            studentSelect.dispatchEvent(new Event("change"));
        }
    });

        renderStudentWork(studentIds[0]);
    });
</script>

<script>
let attachmentIndex = 0;

    document.getElementById('add-attachment-btn').addEventListener('click', function () {
        const container = document.getElementById('attachment-container');
        const wrapper = document.createElement('div');
        wrapper.classList.add('attachment-wrapper');
        wrapper.style.marginBottom = '15px';

    wrapper.innerHTML = `
        <div style="border: 2px dashed #ccc; padding: 10px; border-radius: 5px; text-align: center; position: relative;">
            <input type="url" name="attachments[${index}][link]" placeholder="Paste a media URL (optional)" style="width: 100%; margin-bottom: 10px;" />

           <input type="file" name="attachments[${index}][file]" accept=".jpg,.jpeg,.png,.gif,.mp4,.mov,.avi,.mp3,.wav,.webm,.pdf" style="display: none;" />
            <button type="button" class="upload-btn">Choose File</button>
            <span class="file-name" style="display: inline-block; margin-top: 5px; color: #555;"></span>

            <img src="" alt="Preview" class="preview" style="max-width: 100%; margin-top: 10px; display: none;" />

            <button type="button" class="remove-attachment" style="position: absolute; top: 5px; right: 5px;">🗑️</button>
        </div>
    `;

    container.appendChild(wrapper);

    const fileInput = wrapper.querySelector('input[type="file"]');
    const urlInput = wrapper.querySelector('input[type="url"]');
    const previewImg = wrapper.querySelector('.preview');

    wrapper.querySelector('.upload-btn').addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                urlInput.value = '';
            };
            reader.readAsDataURL(file);
        }
    });

    urlInput.addEventListener('input', () => {
        if (urlInput.value.trim()) {
            fileInput.value = '';
            previewImg.style.display = 'none';
        }
    });

    wrapper.querySelector('.remove-attachment').addEventListener('click', () => {
        wrapper.remove();
    });

    fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        const fileNameSpan = document.createElement('span');
        fileNameSpan.textContent = file.name;
        fileNameSpan.style.display = 'inline-block';
        fileNameSpan.style.marginTop = '10px';
        fileNameSpan.style.fontWeight = 'bold';

        // Remove old name if exists
        const oldName = wrapper.querySelector('.file-name');
        if (oldName) oldName.remove();

        fileNameSpan.classList.add('file-name');
        wrapper.appendChild(fileNameSpan);

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                urlInput.value = '';
            };
            reader.readAsDataURL(file);
        } else {
            previewImg.style.display = 'none';
        }
    }
});

});


</script>

