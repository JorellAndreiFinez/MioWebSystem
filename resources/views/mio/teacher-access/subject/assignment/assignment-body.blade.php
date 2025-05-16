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
                        {{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($assignment['created_at'])->format('Y-m-d') . ' ' . ($assignment['availability']['end'] ?? '00:00'))->format('F j, Y g:i A')}}

                    </strong>
                </div>
                <div>
                    <span>Points</span>
                    <strong>{{ $assignment['points']['earned'] }} / {{ $assignment['points']['total']}}</strong>
                </div>
                <div>
                    <span>Attempts</span>
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
            <h2 id="modalTitle">Add Assignment</h2>

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

                <label for="deadline">Deadline</label>
                <input type="date" name="deadline" id="assignmentDeadline" value="{{ $assignment['deadline'] ?? '' }}"  required>

                <label for="availability_start">Availability - Start Time</label>
                <input type="time" name="availability_start" id="assignmentAvailabilityStart" value="{{ $assignment['availability']['start'] ?? '' }}"  required>

                <label for="availability_end">Availability - End Time</label>
                <input type="time" name="availability_end" id="assignmentAvailabilityEnd" value="{{ $assignment['availability']['end'] ?? '' }}"  required>

                <label for="points_earned">Points</label>
                <div style="display: flex; align-items: center; gap: 5px;">
                    <input type="number" name="points_earned" id="pointsEarned" min="0" required style="width: 60px;" value="{{ $assignment['points']['earned'] ?? '' }}" >
                    <span>/</span>
                    <input type="number" name="points_total" id="pointsTotal" value="{{ $assignment['points']['total'] ?? '' }}"  min="1" required style="width: 60px;">
                </div>

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

                <label for="studentSelector">Select Student</label>
                <select id="studentSelector" name="student_id" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%;">
                    @if (!empty($assignment['people']))
                        @foreach ($assignment['people'] as $studentId => $student)
                            <option value="{{ $studentId }}">
                                {{ $student['name'] ?? ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '') }}
                            </option>
                        @endforeach
                    @else
                        <option disabled selected>No students assigned</option>
                    @endif
                </select>

                <div id="studentWorkPreview" style="margin-top: 20px;">
                    @if($submission)
                        <p>{{ $submission->content }}</p>
                    @else
                        <p>No submission yet.</p>
                    @endif
                </div>

                <label for="feedback">Feedback</label>
                <textarea name="feedback" rows="4" placeholder="Enter your feedback" required></textarea>
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

    // Attachments dynamic input
    let attachmentIndex = {{ $attachmentIndex ?? 0 }};

    document.getElementById('add-attachment-btn').addEventListener('click', function () {
        const container = document.getElementById('attachment-container');
        const wrapper = document.createElement('div');
        wrapper.classList.add('attachment-wrapper');
        wrapper.style.marginBottom = '15px';

        wrapper.innerHTML = `
            <input type="file" name="attachments[${attachmentIndex}][file]" style="display:block; margin-bottom:5px;" />
            <input type="url" name="attachments[${attachmentIndex}][link]" placeholder="Or paste a media URL (optional)" style="width:100%;" />
        `;

        container.appendChild(wrapper);
        attachmentIndex++;
    });

    // Student work preview
    const studentSelector = document.getElementById("studentSelector");
    const preview = document.getElementById("studentWorkPreview");

    if (studentSelector) {
        studentSelector.addEventListener("change", function () {
            const selectedStudentId = this.value;
            const submissions = @json($assignment['people']);

            if (submissions[selectedStudentId] && submissions[selectedStudentId]['work']) {
                const work = submissions[selectedStudentId]['work'];
                preview.innerHTML = `<p>${work.content ?? 'Submitted work found'}</p>`;
            } else {
                preview.innerHTML = `<p>No submission yet.</p>`;
            }
        });
    }
</script>
