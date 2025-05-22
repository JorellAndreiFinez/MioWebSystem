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

    <!-- 📄 Assignment Cards List -->
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

            <!-- Assignment Description -->
            @if (!empty($quiz['description']))
                <div class="assignment-description" style="margin-top: 15px;">
                    <h4>Description</h4>
                    <p>{{ $quiz['description'] }}</p>
                </div>
            @endif

            <!-- Assignment Attachments -->
            @if (!empty($quiz['attachments']))
                <div class="assignment-attachments" style="margin-top: 15px;">
                    <h4>Attachments</h4>
                    <ul>
                        @foreach ($quiz['attachments'] as $attachment)
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
                            <a href="#" class="primary-btn" id="openAssignmentModal">Edit Quiz</a>

                            @php
                                $submittedCount = 0;
                                $totalStudents = 0;

                                if (!empty($quiz['people'])) {
                                    $totalStudents = count($quiz['people']);
                                    foreach ($quiz['people'] as $student) {
                                        if (!empty($student['work'])) {
                                            $submittedCount++;
                                        }
                                    }
                                }
                            @endphp

                            <a href="#" class="secondary-btn" id="openReviewModal">
                                Submissions [{{ $submittedCount }} / {{ $totalStudents }}]
                            </a>




                            <form action="{{ route('mio.subject-teacher.deleteQuiz', ['subjectId' => $subjectId, 'quizId' => $quizId]) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" style="background: none; border: none; cursor: pointer;">
                                    <i class="fas fa-trash-alt" style="color: red; font-size: 20px;"></i>
                                </button>
                        </form>

                </div>
            </div>

    </main>

    <!-- /Edit quiz Modal -->
    <div id="editQuizModal" class="modal assignment-modal" style="display: none;">
        <div class="modal-content" style="max-height: 90vh; overflow-y: auto;">
            <span class="close" id="closeEditQuizModal">&times;</span>
            <h2>Edit Quiz - {{ $subject['title'] }}</h2>

            <form method="POST" enctype="multipart/form-data"
                action="{{ route('mio.subject-teacher.edit-acads-quiz', ['subjectId' => $subjectId, 'quizId' => $quizId]) }}" id="quiz-form">

                @csrf
                @method('PUT')


                <!-- Quiz Info Section -->
                <label for="quiz_title">Quiz Title <span style="color:red">*</span></label>
                <input type="text" name="title" id="quiz_title" value="{{ $quiz['title'] ?? '' }}" placeholder="Enter Quiz Title" required>

                <label for="quiz_description">Description</label>
                <textarea name="description" id="quiz_description" rows="3" placeholder="Brief description or instructions...">{{ $quiz['description'] ?? '' }}</textarea>

                <label for="quiz_publish_date">Publish Date <span style="color:red">*</span></label>
                <input type="date" name="publish_date" id="quiz_publish_date" value="{{ isset($quiz['publish_date']) ? $quiz['publish_date'] : date('Y-m-d') }}" required>

                <label for="quiz_start_time">Start Time <span style="color:red">*</span></label>
                <input type="time" name="start_time" id="quiz_start_time" value="{{ $quiz['start_time'] ?? '' }}" required>

                <label for="quiz_deadline_date">Deadline Date (Optional)</label>
                <input type="date" name="deadline_date" id="quiz_deadline_date" value="{{ $quiz['deadline'] ?? date('Y-m-d', strtotime('+1 day')) }}">

                <label for="quiz_end_time">End Time (Optional)</label>
                <input type="time" name="end_time" id="quiz_end_time" value="{{ $quiz['end_time'] ?? '17:00' }}">

                <label for="quiz_time_limit">Time Limit (in minutes) <span style="color:red">*</span></label>
                <input type="number" name="time_limit" id="quiz_time_limit" min="1" value="{{ $quiz['time_limit'] ?? 30 }}" required>

                <label for="quiz_total_points">Total Points <span style="color:red">*</span></label>
                <input type="number" name="total" id="quiz_total_points" min="1" value="{{ $quiz['total'] ?? 10 }}" required>

                <label for="quiz_attempts">Attempts Allowed <span style="color:red">*</span></label>
                <input type="number" name="attempts" id="quiz_attempts" min="1" value="{{ $quiz['attempts'] ?? 1 }}" required>

                <!-- Quiz Questions Section -->
                <div class="section-header" style="margin-top: 20px;">Quiz Questions</div>

                <div id="questions-section">
            @if(!empty($questions))
                @foreach($questions as $index => $question)
                    <div class="question-block" data-index="{{ $index }}">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Question Type</label>
                                <select name="questions[{{ $index }}][type]" class="question-type" data-index="{{ $index }}" onchange="handleQuestionTypeChange({{ $index }})">
                                    <option value="multiple_choice" {{ ($question['type'] ?? '') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="essay" {{ ($question['type'] ?? '') == 'essay' ? 'selected' : '' }}>Essay</option>
                                    <option value="file_upload" {{ ($question['type'] ?? '') == 'file_upload' ? 'selected' : '' }}>File Upload</option>
                                    <option value="fill_blank" {{ ($question['type'] ?? '') == 'fill_blank' ? 'selected' : '' }}>Fill in the Blanks</option>
                                    <option value="dropdown" {{ ($question['type'] ?? '') == 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Question <span style="color:red">*</span></label>
                                <input type="text" name="questions[{{ $index }}][question]" placeholder="Enter the question" required
                                    value="{{ $question['question'] ?? '' }}" />
                            </div>
                        </div>

                        <div class="question-type-container" data-index="{{ $index }}">
                            <div class="choices-container form-row" data-question-index="{{ $index }}">
                                @if(isset($question['options']) && is_array($question['options']))
                                    @foreach($question['options'] as $letter => $option)
                                        <div class="form-group choice-block" data-letter="{{ $letter }}">
                                            <label>Option {{ strtoupper($letter) }}</label>
                                            <div class="input-with-icon">
                                                <input type="text" name="questions[{{ $index }}][options][{{ $letter }}]" required value="{{ $option }}" />
                                                <button type="button" class="icon-btn" onclick="removeChoice({{ $index }}, this)" title="Remove Choice">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            @if(($question['type'] ?? '') == 'multiple_choice')
                                <button type="button" class="btn add-choice-btn" onclick="addChoice({{ $index }})">+ Add Choice</button>
                            @endif

                            <div class="form-row" style="margin-top:10px;">
                                <div class="form-group">
                                    <label>Correct Answer</label>
                                    <select name="questions[{{ $index }}][answer]" required class="correct-answer-select" data-question-index="{{ $index }}">
                                        <option value="">Select</option>
                                        @if(isset($question['options']) && is_array($question['options']))
                                            @foreach(array_keys($question['options']) as $letter)
                                                <option value="{{ $letter }}" {{ (isset($question['answer']) && $question['answer'] === $letter) ? 'selected' : '' }}>
                                                    {{ strtoupper($letter) }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn remove-question-btn" onclick="removeQuestion({{ $index }})" style="background:#e74c3c; margin-top:10px;">Remove Question</button>
                        <hr />
                    </div>
                @endforeach
            @else
                <!-- No questions, render one empty question block -->
                <script>document.addEventListener('DOMContentLoaded', () => addQuestion());</script>
            @endif
        </div>


                <button type="button" class="btn add-btn" onclick="addQuestion()" style="display: block; margin: 2rem auto;">+ Add Another Question</button>

                <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Save Quiz</button>
            </form>
        </div>
    </div>


</section>


<script>
    window.onload = function () {
    // For each existing question-block, reorder and update choices and correct answer options
    document.querySelectorAll('.question-block').forEach(block => {
        const idx = block.getAttribute('data-index');
        reorderChoices(parseInt(idx));
        updateCorrectAnswerOptions(parseInt(idx));
        handleQuestionTypeChange(parseInt(idx));
    });

    // If no questions, initialize first question (handled above)
};


  let questionIndex = 1; // next question id
  const letters = 'abcdefghijklmnopqrstuvwxyz';

  // Add initial 4 choices (a-d) for question 0
  function initializeChoices(questionIdx) {
    const container = document.querySelector(`.choices-container[data-question-index="${questionIdx}"]`);
    for (let i = 0; i < 4; i++) {
      addChoice(questionIdx, i);
    }
    updateCorrectAnswerOptions(questionIdx);
  }

  // Add choice input to a question
  function addChoice(questionIdx) {
  const container = document.querySelector(`.choices-container[data-question-index="${questionIdx}"]`);
  if (!container) return;

  // Create empty div with temporary letter (will fix in reorder)
  const div = document.createElement('div');
  div.className = 'form-group choice-block';

  div.innerHTML = `
    <label>Option</label>
    <div class="input-with-icon">
      <input type="text" required />
      <button type="button" class="icon-btn" onclick="removeChoice(${questionIdx}, this)" title="Remove Choice">
        <i class="fas fa-times"></i>
      </button>
    </div>
  `;

  container.appendChild(div);

  reorderChoices(questionIdx); // Fix labels and name attributes
}

  // Remove choice input from a question
  function removeChoice(questionIdx, btn) {
  const choiceBlock = btn.closest('.choice-block');
  const container = document.querySelector(`.choices-container[data-question-index="${questionIdx}"]`);
  if (choiceBlock && container) {
    container.removeChild(choiceBlock);
    reorderChoices(questionIdx); // Fix labels and name attributes
  }
}

function reorderChoices(questionIdx) {
  const container = document.querySelector(`.choices-container[data-question-index="${questionIdx}"]`);
  const blocks = container.querySelectorAll('.choice-block');
  const lettersArr = 'abcdefghijklmnopqrstuvwxyz';

  blocks.forEach((block, i) => {
    const letter = lettersArr[i];
    block.setAttribute('data-letter', letter);
    block.querySelector('label').textContent = `Option ${letter.toUpperCase()}`;
    const input = block.querySelector('input');
    input.setAttribute('name', `questions[${questionIdx}][options][${letter}]`);
  });

  updateCorrectAnswerOptions(questionIdx);
}

  // Update the correct answer select options based on current choices
  function updateCorrectAnswerOptions(questionIdx) {
    const container = document.querySelector(`.choices-container[data-question-index="${questionIdx}"]`);
    const select = document.querySelector(`select.correct-answer-select[data-question-index="${questionIdx}"]`);
    if (!container || !select) return;

    // Get current choice letters
    const letters = Array.from(container.querySelectorAll('.choice-block')).map(el => el.getAttribute('data-letter'));

    // Save currently selected value
    const currentVal = select.value;

    // Clear options
    select.innerHTML = `<option value="">Select</option>`;

    letters.forEach(letter => {
      const option = document.createElement('option');
      option.value = letter;
      option.textContent = letter.toUpperCase();
      select.appendChild(option);
    });

    // Restore selection if still valid
    if (letters.includes(currentVal)) {
      select.value = currentVal;
    } else {
      select.value = '';
    }
  }

  // Add new question block
  function addQuestion() {
  const container = document.getElementById('questions-section');
  const idx = questionIndex++;

  const block = document.createElement('div');
  block.className = 'question-block';
  block.setAttribute('data-index', idx);

  block.innerHTML = `
    <div class="form-row">
      <div class="form-group">
        <label>Question Type</label>
        <select name="questions[${idx}][type]" class="question-type" data-index="${idx}" onchange="handleQuestionTypeChange(${idx})">
          <option value="multiple_choice" selected>Multiple Choice</option>
          <option value="essay">Essay</option>
          <option value="file_upload">File Upload</option>
          <option value="fill_blank">Fill in the Blanks</option>
          <option value="dropdown">Dropdown</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group wide">
        <label>Question <span style="color:red">*</span></label>
        <input type="text" name="questions[${idx}][question]" placeholder="Enter the question" required />
      </div>
    </div>

    <div class="question-type-container" data-index="${idx}">
      <div class="choices-container form-row" data-question-index="${idx}"></div>
      <button type="button" class="btn add-choice-btn" onclick="addChoice(${idx})">+ Add Choice</button>

      <div class="form-row" style="margin-top:10px;">
        <div class="form-group">
          <label>Correct Answer</label>
          <select name="questions[${idx}][answer]" required class="correct-answer-select" data-question-index="${idx}">
            <option value="">Select</option>
          </select>
        </div>
      </div>
    </div>

    <button type="button" class="btn remove-question-btn" onclick="removeQuestion(${idx})" style="background:#e74c3c; margin-top:10px;">Remove Question</button>
    <hr />
  `;

  container.appendChild(block);

  // Initialize default question type behavior (multiple_choice)
  handleQuestionTypeChange(idx);
}

  // Remove question block
  function removeQuestion(idx) {
    const container = document.getElementById('questions-section');
    const block = container.querySelector(`.question-block[data-index="${idx}"]`);
    if (block) {
      container.removeChild(block);
    }
  }

  // Initialize first question choices on page load
  window.onload = function () {
    initializeChoices(0);
  };
</script>

<script>
function handleQuestionTypeChange(index) {
  const type = document.querySelector(`select[name="questions[${index}][type]"]`).value;
  const container = document.querySelector(`.question-type-container[data-index="${index}"]`);
  const choicesContainer = container.querySelector(`.choices-container`);
  const answerContainer = container.querySelector(`.correct-answer-select`)?.closest('.form-row');
  const addChoiceBtn = container.querySelector('.add-choice-btn');

  // Clear current inputs
  choicesContainer.innerHTML = '';
  if (answerContainer) answerContainer.style.display = 'none';
  if (addChoiceBtn) addChoiceBtn.style.display = 'none'; // default hide

  switch (type) {
    case 'multiple_choice':
    case 'dropdown':
      for (let i = 0; i < 4; i++) {
        addChoice(index, i);
      }
      updateCorrectAnswerOptions(index);
      if (answerContainer) answerContainer.style.display = 'block';
      if (addChoiceBtn) addChoiceBtn.style.display = 'inline-block';
      break;

    case 'essay':
      choicesContainer.innerHTML = `
        <div class="form-group wide">
          <label>Answer Guide (Optional)</label>
          <textarea name="questions[${index}][answer]" placeholder="Expected answer or notes..."></textarea>
        </div>
      `;
      break;

    case 'file_upload':
      choicesContainer.innerHTML = `
        <div class="form-group wide">
          <label>File Instructions (Optional)</label>
          <textarea name="questions[${index}][answer]" placeholder="e.g., Upload a PDF report..."></textarea>
        </div>
      `;
      break;

    case 'fill_blank':
      choicesContainer.innerHTML = `
        <div class="form-group wide">
          <label>Answer (Exact Match)</label>
          <input type="text" name="questions[${index}][answer]" placeholder="e.g., Manila" />
        </div>
      `;
      break;
  }
}

</script>


<script>
document.querySelector('form').addEventListener('submit', function (e) {
    const form = e.target;
    const errors = [];

    const publishDateInput = form.querySelector('input[name="quiz[publish_date]"]');
    const startTimeInput = form.querySelector('input[name="quiz[start_time]"]');

    const publishDate = new Date(publishDateInput.value);
    const startTime = startTimeInput.value;

    const now = new Date();
    const today = new Date();
    today.setHours(0, 0, 0, 0); // midnight for date-only comparison

    // Validate: publish date must be today or in the future
    if (publishDate < today) {
        errors.push("Publish date must be today or a future date.");
    }

    // If publish date is today, check that start time is not in the past
    if (publishDate.toDateString() === now.toDateString()) {
        const [startHour, startMinute] = startTime.split(':').map(Number);
        const startDateTime = new Date(publishDate);
        startDateTime.setHours(startHour, startMinute, 0, 0);

        if (startDateTime < now) {
            errors.push("Start time must not be in the past for today.");
        }
    }

    // Validate all required fields
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            const label = field.closest('.form-group')?.querySelector('label')?.innerText || field.name;
            errors.push(`"${label.replace('*', '').trim()}" is required.`);
        }
    });

    // Show alert and stop submission if errors exist
    if (errors.length > 0) {
        e.preventDefault();
        alert("Please fix the following issues:\n\n" + errors.join("\n"));
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const editQuizBtn = document.getElementById("openAssignmentModal");
    const editQuizModal = document.getElementById("editQuizModal");
    const closeEditQuizBtn = document.getElementById("closeEditQuizModal");

    // Show modal on click
    editQuizBtn.addEventListener("click", function (e) {
        e.preventDefault();
        editQuizModal.style.display = "block";

        // Optional: Initialize default choices for the first question if needed
        initializeChoices(0);
    });

    // Close modal on click of "×"
    closeEditQuizBtn.addEventListener("click", function () {
        editQuizModal.style.display = "none";
    });

    // Close modal when clicking outside the modal content
    window.addEventListener("click", function (e) {
        if (e.target === editQuizModal) {
            editQuizModal.style.display = "none";
        }
    });
});
</script>



