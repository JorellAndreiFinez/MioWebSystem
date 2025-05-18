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

    <!-- /Edit Assignment Modal -->
    <div id="editQuizModal" class="modal assignment-modal" style="display: none;">
  <div class="modal-content" style="max-height: 90vh; overflow-y: auto;">
    <span class="close" id="closeEditQuizModal">&times;</span>
    <h2>Edit Quiz - {{ $subject['title'] }}</h2>

    <form id="editQuizForm" action="{{ route('mio.subject-teacher.quiz.edit', ['subjectId' => $subjectId, 'quizId' => $quizId]) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <!-- Quiz Info Section -->
      <div class="section-header">Quiz Information</div>
      <div class="section-content">

        <div class="form-row">
          <div class="form-group wide">
            <label>Quiz Title <span style="color:red">*</span></label>
            <input type="text" name="quiz[title]" value="{{ $quiz['title'] ?? '' }}" placeholder="Enter Quiz Title" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group wide">
            <label>Description</label>
            <textarea name="quiz[description]" placeholder="Brief description or instructions...">{{ $quiz['description'] ?? '' }}</textarea>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Publish Date <span style="color:red">*</span></label>
            <input type="date" name="quiz[publish_date]" value="{{ \Carbon\Carbon::parse($quiz['published_at'])->format('Y-m-d') }}" required />
          </div>
          <div class="form-group">
            <label>Start Time <span style="color:red">*</span></label>
            <input type="time" name="quiz[start_time]" value="{{ $quiz['availability']['start'] ?? '' }}" required />
          </div>
          <div class="form-group">
            <label>Deadline Date (Optional)</label>
            <input type="date" name="quiz[deadline_date]" value="{{ $quiz['deadline'] ? \Carbon\Carbon::parse($quiz['deadline'])->format('Y-m-d') : '' }}" />
          </div>
          <div class="form-group">
            <label>End Time (Optional)</label>
            <input type="time" name="quiz[end_time]" value="{{ $quiz['availability']['end'] ?? '' }}" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Time Limit (in minutes) <span style="color:red">*</span></label>
            <input type="number" name="quiz[time_limit]" min="1" value="{{ $quiz['time_limit'] ?? 30 }}" required />
          </div>

          <div class="form-group">
            <label>Total Points <span style="color:red">*</span></label>
            <input type="number" name="quiz[total_points]" min="1" value="{{ $quiz['total'] ?? 10 }}" required />
          </div>

          <div class="form-group">
            <label>Attempts Allowed <span style="color:red">*</span></label>
            <input type="number" name="quiz[attempts]" min="1" value="{{ $quiz['attempts'] ?? 1 }}" required />
          </div>
        </div>
      </div>

      <!-- Attachments -->
      <div class="section-header">Attachments</div>
      <div class="section-content">
        <div id="attachment-container">
          @php $attachmentIndex = 0; @endphp
          @if (!empty($quiz['attachments']))
            @foreach ($quiz['attachments'] as $attachment)
              <div class="attachment-wrapper" style="margin-bottom: 15px;">
                @if (!empty($attachment['file']))
                  <div>
                    <p>Existing File: <a href="{{ $attachment['file'] }}" target="_blank">{{ basename($attachment['file']) }}</a></p>
                  </div>
                @endif
                <input type="file" name="attachments[{{ $attachmentIndex }}][file]" />
                <input type="url" name="attachments[{{ $attachmentIndex }}][link]" placeholder="Or paste a media URL (optional)" value="{{ $attachment['link'] ?? '' }}" />
              </div>
              @php $attachmentIndex++; @endphp
            @endforeach
          @endif
        </div>
        <button type="button" id="add-attachment-btn">+ Add File or Link</button>
      </div>

      <!-- Quiz Questions Section -->
      <div class="section-header">Quiz Questions</div>
      <div class="section-content" id="questions-section">
        <!-- Populate questions dynamically using JS from existing $quiz['questions'] -->
        {{-- JS will fill this area with editable question blocks --}}
      </div>

      <button type="button" class="btn add-btn" onclick="addQuestion()" style="display: block; margin: 1rem auto;">
        + Add Question
      </button>

      <div class="form-row" style="text-align:center; margin-top: 2rem;">
        <button type="submit" class="btn btn-primary">Save Quiz Changes</button>
      </div>

    </form>
  </div>
</div>

</section>


<script>
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


