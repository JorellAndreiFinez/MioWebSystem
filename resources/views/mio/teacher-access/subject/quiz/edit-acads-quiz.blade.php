<section class="home-section">
  <div class="teacher-container">
    <main class="main-banner">
      <div class="banner">
        <h2>Edit Quiz - {{ $subject['title'] }}</h2>
      </div>
    </main>

    <main class="main-content">
      <form method="POST" enctype="multipart/form-data" action="{{ route('mio.subject-teacher.update-acads-quiz', ['subjectId' => $subjectId, 'quizId' => $quiz['id']]) }}" id="quiz-form">
        @csrf
        @method('PUT')

        <button type="submit" class="btn btn-primary">Update Academic Quiz</button>

        <!-- Quiz Info Section -->
        <div class="section-header">Quiz Information</div>
        <div class="section-content">

          <div class="form-row">
            <div class="form-group wide">
              <label>Quiz Title <span style="color:red">*</span></label>
              <input type="text" name="title" value="{{ old('quiz.title', $quiz['title']) }}" placeholder="Enter Quiz Title" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group wide">
              <label>Description</label>
              <textarea name="description" placeholder="Brief description or instructions...">{{ old('quiz.description', $quiz['description']) }}</textarea>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Publish Date <span style="color:red">*</span></label>
              <input type="date" name="publish_date" value="{{ old('quiz.publish_date', $quiz['publish_date']) }}" required />
            </div>
            <div class="form-group">
              <label>Start Time <span style="color:red">*</span></label>
              <input type="time" name="start_time" value="{{ old('quiz.start_time', $quiz['start_time']) }}" required />
            </div>
            <div class="form-group">
              <label>Deadline Date (Optional)</label>
              <input type="date" name="deadline" value="{{ old('quiz.deadline_date', $quiz['deadline']) }}" />
            </div>
            <div class="form-group">
              <label>End Time (Optional)</label>
              <input type="time" name="end_time" value="{{ old('quiz.end_time', $quiz['end_time']) }}" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Time Limit (in minutes) <span style="color:red">*</span></label>
              <input type="number" name="time_limit" min="0" value="{{ old('quiz.time_limit', $quiz['time_limit']) }}" required />
              <div style="margin-top: 5px;">
                    <label style="font-weight: normal;">
                    <input type="checkbox" id="no-time-limit" name="no_time_limit" value="1" onchange="toggleTimeLimit()" />
                    No Time Limit
                    </label>
                </div>
            </div>
                </div>

            <div class="form-group">
              <label>Total Points <span style="color:red">*</span></label>
              <input type="number" name="total" min="1" value="{{ old('quiz.total_points', $quiz['total']) }}" required />
            </div>

            <div class="form-group">
              <label>Attempts Allowed <span style="color:red">*</span></label>
              <input type="number" name="attempts" min="1" value="{{ old('quiz.attempts', $quiz['attempts']) }}" required />
            </div>
            <div class="form-row">
        <div class="form-group">
            <label>One Question at a Time</label>
            <label class="switch">
            <input type="checkbox" name="quiz[one_question_at_a_time]" value="1" checked>
            <span class="slider round"></span>
            </label>
        </div>

        <div class="form-group">
            <label>Allow Navigation to Previous Question</label>
            <label class="switch">
            <input type="checkbox" name="quiz[can_go_back]" value="1" >
            <span class="slider round"></span>
            </label>
        </div>
        <div class="form-group">
            <label>Show Correct Answers</label>
            <label class="switch">
                <input type="checkbox" name="quiz[show_correct_answers]" value="1">
                <span class="slider round"></span>
            </label>
        </div>
        </div>
          </div>
        </div>

        <!-- Quiz Questions Section -->
        <div class="section-header">Quiz Questions</div>
        <div class="section-content" id="questions-section">

          @foreach ($questions as $questionKey => $question)
          <div class="question-block" data-question-key="{{ $questionKey }}" id="question-block-{{ $questionKey }}">
            <div class="form-row">
              <div class="form-group">
                <label>Question Type</label>
                <select name="questions[{{ $questionKey }}][type]" class="question-type" data-key="{{ $questionKey }}" onchange="handleQuestionTypeChange('{{ $questionKey }}')">
                  <option value="multiple_choice" {{ $question['type'] == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                  <option value="essay" {{ $question['type'] == 'essay' ? 'selected' : '' }}>Essay</option>
                  <option value="file_upload" {{ $question['type'] == 'file_upload' ? 'selected' : '' }}>File Upload</option>
                  <option value="fill_blank" {{ $question['type'] == 'fill_blank' ? 'selected' : '' }}>Fill in the Blanks</option>
                  <option value="dropdown" {{ $question['type'] == 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group wide">
                <label>Question <span style="color:red">*</span></label>
                <input type="text" name="questions[{{ $questionKey }}][question]" placeholder="Enter the question" value="{{ old("questions.$questionKey.question", $question['question']) }}" required />
              </div>
            </div>

            <div class="question-type-container" data-index="{{ $questionKey }}">
              @if(in_array($question['type'], ['multiple_choice', 'dropdown']))
              <div class="choices-container form-row" data-question-id="question-{{ $questionKey }}">
                @foreach ($question['options'] ?? [] as $optKey => $option)
                <div class="form-group choice-block" data-key="{{ $optKey }}">
                  <label>Option</label>
                  <div class="input-with-icon">
                    <input type="text" name="questions[{{ $questionKey }}][options][{{ $optKey }}]" value="{{ old("questions.$questionKey.options.$optKey", is_array($option) ? $option['text'] ?? '' : $option) }}" required />

                    <button type="button" class="icon-btn" onclick="removeChoice(this)" title="Remove Choice">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                @endforeach
              </div>
              <button type="button" class="btn add-choice-btn" onclick="addChoice('{{ $questionKey }}')">+ Add Choice</button>

              <div class="form-row" style="margin-top:10px;">
                <div class="form-group">
                  <label>Correct Answer</label>
                  <select name="questions[{{ $questionKey }}][answer]" required class="correct-answer-select" data-question-id="question-{{ $questionKey }}">
                    <option value="">Select</option>
                    @foreach($question['options'] ?? [] as $optKey => $option)
                    <option value="{{ $optKey }}" {{ (old("questions.$questionKey.answer", $question['answer']) == $optKey) ? 'selected' : '' }}>
                      {{ $option['text'] ?? '' }}

                    </option>
                    @endforeach
                  </select>
                </div>
              </div>

              @elseif($question['type'] == 'essay')
              <div class="choices-container form-row">
                <div class="form-group wide">
                  <label>Answer Guide (Optional)</label>
                  <textarea name="questions[{{ $questionKey }}][answer]" placeholder="Expected answer or notes...">{{ old("questions.$questionKey.answer", $question['answer']) }}</textarea>
                </div>
              </div>

              @elseif($question['type'] == 'file_upload')
              <div class="choices-container form-row">
                <div class="form-group wide">
                  <label>File Instructions (Optional)</label>
                  <textarea name="questions[{{ $questionKey }}][answer]" placeholder="e.g., Upload a PDF report...">{{ old("questions.$questionKey.answer", $question['answer']) }}</textarea>
                </div>
              </div>

              @elseif($question['type'] == 'fill_blank')
              <div class="choices-container form-row">
                <div class="form-group wide">
                  <label>Answer (Exact Match)</label>
                  <input type="text" name="questions[{{ $questionKey }}][answer]" value="{{ old("questions.$questionKey.answer", $question['answer']) }}" placeholder="e.g., Manila" />
                </div>
              </div>
              @endif
            </div>

            <button type="button" class="btn remove-question-btn" onclick="removeQuestion('{{ $questionKey }}')" style="background:#e74c3c; margin-top:10px;">Remove Question</button>
            <hr />
          </div>
          @endforeach

      </form>
    </main>

    <button type="button" class="btn add-btn" onclick="addQuestion()" style="display: block; margin: 0 auto; margin-bottom: 2rem; margin-top: 2rem;">
      + Add Question
    </button>
        </div>
</section>

<script>
  function generateUniqueKey() {
    return 'q_' + Math.random().toString(36).substr(2, 9);
  }

  function addQuestion() {
  const key = generateUniqueKey();

  const questionBlock = document.createElement('div');
  questionBlock.classList.add('question-block');
  questionBlock.setAttribute('data-question-key', key);
  questionBlock.setAttribute('id', `question-block-${key}`);

  questionBlock.innerHTML = `
    <div class="form-row">
      <div class="form-group">
        <label>Question Type</label>
        <select name="questions[${key}][type]" class="question-type" data-key="${key}" onchange="handleQuestionTypeChange('${key}')">
          <option value="multiple_choice">Multiple Choice</option>
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
        <input type="text" name="questions[${key}][question]" placeholder="Enter the question" required />
      </div>
    </div>

    <div class="question-type-container" data-index="${key}">
      <div class="choices-container form-row" data-question-id="question-${key}">
        <!-- One default choice -->
        <div class="form-group choice-block" data-key="opt1">
          <label>Option</label>
          <div class="input-with-icon">
            <input type="text" name="questions[${key}][options][opt1]" required />
            <button type="button" class="icon-btn" onclick="removeChoice(this)" title="Remove Choice">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>

      <button type="button" class="btn add-choice-btn" onclick="addChoice('${key}')">+ Add Choice</button>

      <div class="form-row" style="margin-top:10px;">
        <div class="form-group">
          <label>Correct Answer</label>
          <select name="questions[${key}][answer]" required class="correct-answer-select" data-question-id="question-${key}">
            <option value="opt1">Option 1</option>
          </select>
        </div>
      </div>
    </div>

    <button type="button" class="btn remove-question-btn" onclick="removeQuestion('${key}')" style="background:#e74c3c; margin-top:10px;">Remove Question</button>
    <hr />
  `;

  document.getElementById('questions-section').appendChild(questionBlock);
}


  function addChoice(questionKey) {
    const container = document.querySelector(`.choices-container[data-question-id="question-${questionKey}"]`);
    const select = document.querySelector(`select[name="questions[${questionKey}][answer]"]`);
    const choiceBlocks = container.querySelectorAll('.choice-block');
    const newIndex = choiceBlocks.length + 1;
    const optionKey = `opt${newIndex}`;

    const div = document.createElement('div');
    div.classList.add('form-group', 'choice-block');
    div.setAttribute('data-key', optionKey);
    div.innerHTML = `
        <label>Option</label>
        <div class="input-with-icon">
        <input type="text" name="questions[${questionKey}][options][${optionKey}]" required />
        <button type="button" class="icon-btn" onclick="removeChoice(this)" title="Remove Choice">
            <i class="fas fa-times"></i>
        </button>
        </div>
    `;

    container.appendChild(div);

    // Add to correct answer dropdown
    const option = document.createElement('option');
    option.value = optionKey;
    option.textContent = `Option ${newIndex}`;
    select.appendChild(option);
    }

    function removeChoice(button) {
    const choiceBlock = button.closest('.choice-block');
    const container = choiceBlock.parentElement;
    const select = container.parentElement.querySelector('select.correct-answer-select');
    const key = choiceBlock.dataset.key;

    // Remove the corresponding option from the correct answer dropdown
    const optionToRemove = select.querySelector(`option[value="${key}"]`);
    if (optionToRemove) optionToRemove.remove();

    choiceBlock.remove();
    }

  function updateCorrectAnswerOptions(key) {
    const questionBlock = document.querySelector(`#question-block-${key}`);
    const select = questionBlock.querySelector('.correct-answer-select');

    if (!select) return;

    select.innerHTML = `<option value="">Select</option>`;
    const choices = questionBlock.querySelectorAll('.choice-block');
    choices.forEach(choice => {
      const optKey = choice.getAttribute('data-key');
      const value = choice.querySelector('input').value;
      const option = document.createElement('option');
      option.value = optKey;
      option.textContent = value || '(Empty)';
      select.appendChild(option);
    });
  }

  function removeQuestion(key) {
    const block = document.querySelector(`#question-block-${key}`);
    if (block) block.remove();
  }

  let questionIndex = Date.now(); // avoid collision on reload

  function addQuestion() {
    const key = generateUniqueKey();
    const section = document.getElementById('questions-section');

    const template = `
    <div class="question-block" id="question-block-${key}" data-question-key="${key}">
      <div class="form-row">
        <div class="form-group">
          <label>Question Type</label>
          <select name="questions[${key}][type]" class="question-type" onchange="handleQuestionTypeChange('${key}')">
           <option value="" selected disabled>Select Question Type</option>
          <option value="multiple_choice">Multiple Choice</option>
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
          <input type="text" name="questions[${key}][question]" placeholder="Enter the question" required />
        </div>
      </div>

      <div class="question-type-container" data-index="${key}"></div>
      <button type="button" class="btn remove-question-btn" onclick="removeQuestion('${key}')" style="background:#e74c3c; margin-top:10px;">Remove Question</button>
      <hr />
    </div>`;
    section.insertAdjacentHTML('beforeend', template);
  }

  function handleQuestionTypeChange(key) {
  const container = document.querySelector(`.question-type-container[data-index="${key}"]`);
  const selectedType = document.querySelector(`select[name="questions[${key}][type]"]`).value;

  let html = '';

  if (selectedType === 'multiple_choice' || selectedType === 'dropdown') {
    html += `
      <div class="choices-container form-row" data-question-id="question-${key}">
        <div class="form-group choice-block" data-key="opt1">
          <label>Option</label>
          <div class="input-with-icon">
            <input type="text" name="questions[${key}][options][opt1]" required />
            <button type="button" class="icon-btn" onclick="removeChoice(this)" title="Remove Choice">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>
      <button type="button" class="btn add-choice-btn" onclick="addChoice('${key}')">+ Add Choice</button>

      <div class="form-row" style="margin-top:10px;">
        <div class="form-group">
          <label>Correct Answer</label>
          <select name="questions[${key}][answer]" required class="correct-answer-select" data-question-id="question-${key}">
            <option value="">Select</option>
            <option value="opt1">Option 1</option>
          </select>
        </div>
      </div>
    `;
  } else if (selectedType === 'essay') {
    html += `
      <div class="choices-container form-row">
        <div class="form-group wide">
          <label>Answer Guide (Optional)</label>
          <textarea name="questions[${key}][answer]" placeholder="Expected answer or notes..."></textarea>
        </div>
      </div>
    `;
  } else if (selectedType === 'file_upload') {
    html += `
      <div class="choices-container form-row">
        <div class="form-group wide">
          <label>File Instructions (Optional)</label>
          <textarea name="questions[${key}][answer]" placeholder="e.g., Upload a PDF report..."></textarea>
        </div>
      </div>
    `;
  } else if (selectedType === 'fill_blank') {
    html += `
      <div class="choices-container form-row">
        <div class="form-group wide">
          <label>Answer (Exact Match)</label>
          <input type="text" name="questions[${key}][answer]" placeholder="e.g., Manila" />
        </div>
      </div>
    `;
  }

  container.innerHTML = html;
}
</script>

<script>
function toggleTimeLimit() {
  const checkbox = document.getElementById('no-time-limit');
  const timeInput = document.getElementById('time-limit');

  if (checkbox.checked) {
    timeInput.disabled = true;
    timeInput.removeAttribute('required');
    timeInput.value = '';
  } else {
    timeInput.disabled = false;
    timeInput.setAttribute('required', 'required');
    timeInput.value = 30; // Default or previous value
  }
}
</script>
