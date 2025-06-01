<section class="home-section">
    <!-- 🟦 Header Banner -->
    <main class="main-banner">
        <div class="welcome-banner">
            <div class="banner">
                <div class="content">
                    <h5>Quizzes</h5>
                </div>
            </div>
        </div>
    </main>

    <!-- 📄 Quiz Details -->
    <main class="main-assignment-content">
        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <div class="assignment-card">
            <div class="activity-info">
                <h1>{{ $quiz['title'] }}</h1>
            </div>

            <div class="details">
                <div>
                    <span>Publish at</span>
                    <strong>{{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($quiz['publish_date'])->format('Y-m-d') . ' ' . ($quiz['start_time'] ?? '00:00'))->format('F j, Y g:i A') }}</strong>
                </div>
                <div>
                    <span>Deadline</span>
                    <strong>
                        @if (!empty($quiz['deadline']))
                            {{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($quiz['deadline'])->format('Y-m-d') . ' ' . ($quiz['end_time'] ?? '00:00'))->format('F j, Y g:i A') }}
                        @else
                            No Due Date
                        @endif
                    </strong>
                </div>
                <div>
                    <span>Points</span>
                    <strong>{{ $quiz['total'] ?? '0' }}</strong>
                </div>
                <div>
                    <span>Attempt/s</span>
                    <strong>{{ $quiz['attempts'] }}</strong>
                </div>
            </div>

            @if (!empty($quiz['description']))
                <div class="assignment-description" style="margin-top: 15px;">
                    <h4>Description</h4>
                    <p>{{ $quiz['description'] }}</p>
                </div>
            @endif

            @if (!empty($quiz['attachments']))
                <div class="assignment-attachments" style="margin-top: 15px;">
                    <h4>Attachments</h4>
                    <ul>
                        @foreach ($quiz['attachments'] as $attachment)
                            @if (!empty($attachment['file']))
                                <li>📎 <a href="{{ $attachment['file'] }}" target="_blank">{{ basename($attachment['file']) }}</a></li>
                            @endif
                            @if (!empty($attachment['link']))
                                <li>🔗 <a href="{{ $attachment['link'] }}" target="_blank">{{ $attachment['link'] }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="edit-assignment-container">
                <a href="{{ route('mio.subject-teacher.edit-acads-quiz', ['subjectId' => $subjectId, 'quizId' => $quiz['id']]) }}" class="primary-btn">Edit Quiz</a>

                @php
                    $submittedCount = 0;
                    $totalStudents = 0;
                    if (!empty($quiz['people'])) {
                        $totalStudents = count($quiz['people']);
                        foreach ($quiz['people'] as $student) {
                            if (!empty($student['work'])) $submittedCount++;
                        }
                    }
                @endphp

                <a href="#" class="secondary-btn" id="openReviewModal">Submissions [{{ $submittedCount }} / {{ $totalStudents }}]</a>

                <form action="{{ route('mio.subject-teacher.deleteQuiz', ['subjectId' => $subjectId, 'quizId' => $quizId]) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="delete-btn" style="background: none; border: none; cursor: pointer;">
                        <i class="fas fa-trash-alt" style="color: red; font-size: 20px;"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Quiz Questions -->
        <div class="assignment-card">
            @if (!empty($questions))
                <div class="quiz-questions mt-4">
                    <h4>Questions</h4>
                   @foreach ($questions as $question)
                    <div class="question-box mb-4 p-3 border rounded" style="background-color: #f9f9f9;">
                        <p class="font-weight-bold mb-2">Question: {{ $question['question'] }}</p>

                        @if (!empty($question['options']) && is_array($question['options']))
                            <div class="options-list">
                                @foreach ($question['options'] as $optionKey => $optionText)
                                    <div class="option-item p-2 mb-1 rounded"
                                        style="background-color: {{ $optionKey === $question['answer'] ? '#d4edda' : '#f1f1f1' }};
                                                border-left: 5px solid {{ $optionKey === $question['answer'] ? '#28a745' : '#ccc' }};">
                                        {{ $optionText }}
                                    </div>
                                @endforeach
                            </div>
                        @elseif($question['type'] === 'fill_in_the_blank')
                            <p><em>Answer:</em> {{ $question['answer'] ?? 'No answer provided' }}</p>
                        @elseif($question['type'] === 'essay')
                            <p><em>Essay question (subjective answer)</em></p>
                        @elseif($question['type'] === 'fileupload')
                            <p><em>File upload question</em></p>
                        @else
                            <p><em>Answer type not supported or no options provided.</em></p>
                        @endif
                    </div>
                @endforeach

                </div>
            @endif
        </div>
    </main>

   <!--Review Modal -->
    <div id="attemptPreviewModal" class="modal">
        <div class="modal-content p-4">
            <span id="closeReviewModal" style="float: right; cursor: pointer;">&times;</span>
            <h2 class="text-xl font-semibold mb-4">Quiz Attempt Preview</h2>

            <!-- Student Selection -->
            <div class="mb-4">
                <label class="block mb-2 font-medium">Select Student:</label>
                <div class="flex gap-2 items-center">
                    <button id="prevStudentBtn" class="bg-gray-200 px-3 py-1 rounded">&larr;</button>
                    <select id="studentSelect" class="p-2 border rounded flex-1"></select>
                    <button id="nextStudentBtn" class="bg-gray-200 px-3 py-1 rounded">&rarr;</button>
                </div>
            </div>

            <!-- Attempt Selection -->
            <div class="mb-4">
                <label class="block mb-2 font-medium">Select Attempt:</label>
                <select id="attemptSelector" class="p-2 border rounded w-full"></select>
            </div>

            <!-- Attempt Preview Area -->
            <div id="attemptDetails" class="bg-gray-100 p-4 rounded"></div>
        </div>
    </div>

</section>

<!-- ⚙️ Script for Modals -->
<script>
    // Open Review Modal
    document.getElementById("openReviewModal").addEventListener("click", function (e) {
        e.preventDefault();
        document.getElementById("attemptPreviewModal").style.display = "block";
    });

    // Close Review Modal
    document.getElementById("closeReviewModal").addEventListener("click", function () {
        document.getElementById("attemptPreviewModal").style.display = "none";
    });

    // Close modal on outside click
    window.addEventListener("click", function (event) {
        const modal = document.getElementById("attemptPreviewModal");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
</script>

<script>
    const quizPeople = @json($quiz['people']);
const studentSelect = document.getElementById('studentSelect');
const attemptSelector = document.getElementById('attemptSelector');
const attemptDetails = document.getElementById('attemptDetails');

let studentIds = Object.keys(quizPeople);
let currentStudentIndex = 0;

function populateStudentDropdown() {
    studentSelect.innerHTML = '';
    studentIds.forEach(id => {
        const opt = document.createElement('option');
        opt.value = id;
        opt.text = quizPeople[id]?.name ?? 'Unknown';
        studentSelect.appendChild(opt);
    });
}

function loadAttempts(studentId) {
    const studentData = quizPeople[studentId];
    if (!studentData) {
        attemptSelector.innerHTML = '<option>No attempts</option>';
        attemptDetails.innerHTML = '<p class="text-red-500">No submissions for this student.</p>';
        return;
    }

    // Get attempts starting with 'ATTM'
    let attempts = Object.entries(studentData)
        .filter(([key, val]) => key.startsWith('ATTM') && val && val.answers)
        .sort((a, b) => new Date(b[1].submitted_at) - new Date(a[1].submitted_at));

    if (attempts.length === 0) {
        attemptSelector.innerHTML = '<option>No attempts</option>';
        attemptDetails.innerHTML = '<p class="text-red-500">No attempts available.</p>';
        return;
    }

    attempts.forEach(([attemptId, attemptData], index) => {
        const option = document.createElement('option');
        option.value = attemptId;

        // Format date string
        const submittedAtDate = new Date(attemptData.submitted_at);
        const formattedDate = submittedAtDate.toLocaleString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
        });

        option.text = `Attempt ${index + 1} (${formattedDate})`;
        attemptSelector.appendChild(option);
    });


    // Save attempts globally for render function
    window.currentAttempts = Object.fromEntries(attempts);

    // Render the first attempt by default
    renderAttempt(attempts[0][0], attempts[0][1]);
}

const quizQuestions = @json($quiz['questions']);


function renderAttempt(attemptId, attemptData) {
    if (!attemptData) return;

    let html = `
        <p><strong>Score:</strong> <span id="scoreDisplay">${attemptData.score}</span> / ${attemptData.total_points}</p>
        <p><strong>Time Spent:</strong> ${attemptData.time_spent?.formatted || 'N/A'}</p>
        <p><strong>Submitted At:</strong> ${attemptData.submitted_at}</p>
        <hr class="my-3" />
    `;

    for (const [qid, answerData] of Object.entries(attemptData.answers)) {
        const questionMeta = quizQuestions[qid];
        if (!questionMeta) continue;

        html += `<div class="mb-4 p-3 rounded border";">
            <p class="font-semibold mb-2">${answerData.question}</p>
        `;

        for (const [optionKey, optionText] of Object.entries(questionMeta.options)) {
            const isSelected = optionKey === answerData.student_answer;
            const isCorrect = optionKey === answerData.correct_answer;

            let optionStyle = 'background-color: #f1f1f1;';
            if (isCorrect) {
                optionStyle = 'background-color: #d4edda; border-left: 4px solid #28a745;';
            } else if (isSelected && !isCorrect) {
                optionStyle = 'background-color: #f8d7da; border-left: 4px solid #dc3545;';
            }

            html += `
                <div class="p-2 mb-1 rounded" style="${optionStyle}">
                    <label>
                        <input type="radio" name="question_${qid}" value="${optionKey}" ${isSelected ? 'checked' : ''} disabled>
                        ${optionText}
                    </label>
                </div>
            `;
        }

        html += `
            <p class="mt-2">
                <strong>Points:</strong>
                <input type="number"
                        class="editable-points"
                        data-question-id="${qid}"
                        value="${answerData.points}"
                        min="0"
                        max="${questionMeta.max_points || 10}"
                        style="width: 60px;">
                </p>
        </div>`;
    }

    attemptDetails.innerHTML = html;
}


// Event when student changes
studentSelect.addEventListener('change', () => {
    const studentId = studentSelect.value;
    loadAttempts(studentId);
});

// Event when attempt changes
attemptSelector.addEventListener('change', () => {
    const studentId = studentSelect.value;
    const attemptId = attemptSelector.value;
    if (window.currentAttempts && window.currentAttempts[attemptId]) {
        renderAttempt(attemptId, window.currentAttempts[attemptId]);
    }
});

// Navigation buttons for students
document.getElementById('prevStudentBtn').addEventListener('click', () => {
    currentStudentIndex = (currentStudentIndex - 1 + studentIds.length) % studentIds.length;
    studentSelect.value = studentIds[currentStudentIndex];
    studentSelect.dispatchEvent(new Event('change'));
});

document.getElementById('nextStudentBtn').addEventListener('click', () => {
    currentStudentIndex = (currentStudentIndex + 1) % studentIds.length;
    studentSelect.value = studentIds[currentStudentIndex];
    studentSelect.dispatchEvent(new Event('change'));
});

// When modal opens
document.getElementById("openReviewModal").addEventListener("click", function (e) {
    e.preventDefault();
    document.getElementById("attemptPreviewModal").style.display = "block";
    populateStudentDropdown();
    studentSelect.value = studentIds[currentStudentIndex];
    studentSelect.dispatchEvent(new Event('change'));
});

document.getElementById("closeReviewModal").addEventListener("click", function () {
    document.getElementById("attemptPreviewModal").style.display = "none";
});

window.addEventListener("click", function (event) {
    const modal = document.getElementById("attemptPreviewModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
});


</script>

<script>
    const allAttempts = @json($studentAttempts);

    const renderAttempt = (attemptId) => {
        const attempt = allAttempts[attemptId];
        if (!attempt) return;

        let html = `
        <p><strong>Score:</strong> ${attempt.score} / ${attempt.total_points}</p>
        <p><strong>Time Spent:</strong> ${attempt.time_spent?.formatted || attempt.time_spent_formatted || 'N/A'}</p>
        <p><strong>Submitted At:</strong> ${attempt.submitted_at}</p>
        <hr class="my-3">
        `;

        for (const [qid, answerData] of Object.entries(attempt.answers)) {
        html += `
            <div class="mb-2">
            <p><strong>Q:</strong> ${answerData.question}</p>
            <p><strong>Your Answer:</strong> ${studentAnswerText}</p>
            <p><strong>Correct Answer:</strong> ${correctAnswerText}</p>
            <p><strong>Correct:</strong> ${answerData.is_correct ? '✅ Yes' : '❌ No'}</p>
            <p><strong>Points:</strong> ${answerData.points}</p>
            <hr class="my-2">
            </div>
        `;
        }

        document.getElementById('attemptDetails').innerHTML = html;
        html += `<button id="saveScoresBtn" class="primary-btn mt-4">Save Scores</button>`;

    };

    document.getElementById('attemptSelector').addEventListener('change', function () {
        renderAttempt(this.value);
    });

    // Initial render for latest attempt
    window.addEventListener('DOMContentLoaded', () => {
        renderAttempt(document.getElementById('attemptSelector').value);
    });
    </script>
