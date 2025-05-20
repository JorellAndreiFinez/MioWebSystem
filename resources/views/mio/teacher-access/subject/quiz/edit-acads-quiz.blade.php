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
              <input type="text" name="quiz[title]" value="{{ old('quiz.title', $quiz['title']) }}" placeholder="Enter Quiz Title" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group wide">
              <label>Description</label>
              <textarea name="quiz[description]" placeholder="Brief description or instructions...">{{ old('quiz.description', $quiz['description']) }}</textarea>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Publish Date <span style="color:red">*</span></label>
              <input type="date" name="quiz[publish_date]" value="{{ old('quiz.publish_date', $quiz['publish_date']) }}" required />
            </div>
            <div class="form-group">
              <label>Start Time <span style="color:red">*</span></label>
              <input type="time" name="quiz[start_time]" value="{{ old('quiz.start_time', $quiz['start_time']) }}" required />
            </div>
            <div class="form-group">
              <label>Deadline Date (Optional)</label>
              <input type="date" name="quiz[deadline]" value="{{ old('quiz.deadline_date', $quiz['deadline']) }}" />
            </div>
            <div class="form-group">
              <label>End Time (Optional)</label>
              <input type="time" name="quiz[end_time]" value="{{ old('quiz.end_time', $quiz['end_time']) }}" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Time Limit (in minutes) <span style="color:red">*</span></label>
              <input type="number" name="quiz[time_limit]" min="1" value="{{ old('quiz.time_limit', $quiz['time_limit']) }}" required />
            </div>

            <div class="form-group">
              <label>Total Points <span style="color:red">*</span></label>
              <input type="number" name="quiz[total]" min="1" value="{{ old('quiz.total_points', $quiz['total']) }}" required />
            </div>

            <div class="form-group">
              <label>Attempts Allowed <span style="color:red">*</span></label>
              <input type="number" name="quiz[attempts]" min="1" value="{{ old('quiz.attempts', $quiz['attempts']) }}" required />
            </div>
          </div>
        </div>

        <!-- Quiz Questions Section -->
        <div class="section-header">Quiz Questions</div>
        <div class="section-content" id="questions-section">

          @foreach ($questions as $idx => $question)
          <div class="question-block" data-index="{{ $idx }}">
            <div class="form-row">
              <div class="form-group">
                <label>Question Type</label>
                <select name="questions[{{ $idx }}][type]" class="question-type" data-index="{{ $idx }}" onchange="handleQuestionTypeChange({{ $idx }})">
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
                <input type="text" name="questions[{{ $idx }}][question]" placeholder="Enter the question" value="{{ old("questions.$idx.question", $question['question']) }}" required />
              </div>
            </div>

            <div class="question-type-container" data-index="{{ $idx }}">
              @if(in_array($question['type'], ['multiple_choice', 'dropdown']))
              <div class="choices-container form-row" data-question-index="{{ $idx }}">
                @foreach ($question['options'] ?? [] as $letter => $option)
                <div class="form-group choice-block" data-letter="{{ $letter }}">
                  <label>Option {{ strtoupper($letter) }}</label>
                  <div class="input-with-icon">
                    <input type="text" name="questions[{{ $idx }}][options][{{ $letter }}]" value="{{ old("questions.$idx.options.$letter", $option) }}" required />
                    <button type="button" class="icon-btn" onclick="removeChoice({{ $idx }}, this)" title="Remove Choice">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                @endforeach
              </div>
              <button type="button" class="btn add-choice-btn" onclick="addChoice({{ $idx }})">+ Add Choice</button>

              <div class="form-row" style="margin-top:10px;">
                <div class="form-group">
                  <label>Correct Answer</label>
                  <select name="questions[{{ $idx }}][answer]" required class="correct-answer-select" data-question-index="{{ $idx }}">
                    <option value="">Select</option>
                    @foreach(array_keys($question['options'] ?? []) as $letter)
                    <option value="{{ $letter }}" {{ (old("questions.$idx.answer", $question['answer']) == $letter) ? 'selected' : '' }}>{{ strtoupper($letter) }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              @elseif($question['type'] == 'essay')
              <div class="choices-container form-row" data-question-index="{{ $idx }}">
                <div class="form-group wide">
                  <label>Answer Guide (Optional)</label>
                  <textarea name="questions[{{ $idx }}][answer]" placeholder="Expected answer or notes...">{{ old("questions.$idx.answer", $question['answer']) }}</textarea>
                </div>
              </div>

              @elseif($question['type'] == 'file_upload')
              <div class="choices-container form-row" data-question-index="{{ $idx }}">
                <div class="form-group wide">
                  <label>File Instructions (Optional)</label>
                  <textarea name="questions[{{ $idx }}][answer]" placeholder="e.g., Upload a PDF report...">{{ old("questions.$idx.answer", $question['answer']) }}</textarea>
                </div>
              </div>

              @elseif($question['type'] == 'fill_blank')
              <div class="choices-container form-row" data-question-index="{{ $idx }}">
                <div class="form-group wide">
                  <label>Answer (Exact Match)</label>
                  <input type="text" name="questions[{{ $idx }}][answer]" value="{{ old("questions.$idx.answer", $question['answer']) }}" placeholder="e.g., Manila" />
                </div>
              </div>
              @endif
            </div>

            <button type="button" class="btn remove-question-btn" onclick="removeQuestion({{ $idx }})" style="background:#e74c3c; margin-top:10px;">Remove Question</button>
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
  let questionIndex = {{ count($questions) }};
  const letters = 'abcdefghijklmnopqrstuvwxyz';

  // Add choice input to a question
  function addChoice(questionIdx) {
    const container = document.querySelector(`.choices-container[data-question-index="${questionIdx}"]`);
    if (!container) return;

    const div = document.createElement('div');
    div.classList.add('form-group', 'choice-block');

    // Get current letters used
    const usedLetters = Array.from(container.querySelectorAll('.choice-block')).map(el => el.getAttribute('data-letter'));
    const nextLetter = letters.split('').find(l => !usedLetters.includes(l));

    if (!nextLetter) {
      alert('Maximum choices reached!');
      return;
    }

    div.setAttribute('data-letter', nextLetter);
    div.innerHTML = `
      <label>Option ${nextLetter.toUpperCase()}</label>
      <div class="input-with-icon">
        <input type="text" name="questions[${questionIdx}][options][${nextLetter}]" required />
        <button type="button" class="icon-btn" onclick="removeChoice(${questionIdx}, this)" title="Remove Choice">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
    container.appendChild(div);

    updateCorrectAnswerOptions(questionIdx);
  }

  // Remove a choice input from a question
  function removeChoice(questionIdx, btn) {
    const choiceDiv = btn.closest('.choice-block');
    if (!choiceDiv) return;

    const container = choiceDiv.parentElement;
    const removedLetter = choiceDiv.getAttribute('data-letter');

    // Remove the choice block
    choiceDiv.remove();

    // Update the correct answer dropdown
    const correctSelect = document.querySelector(`select.correct-answer-select[data-question-index="${questionIdx}"]`);
    if (correctSelect) {
      // Remove the option for the deleted letter
      const optionToRemove = correctSelect.querySelector(`option[value="${removedLetter}"]`);
      if (optionToRemove) optionToRemove.remove();

      // If the removed option was the selected one, reset selection
      if (correctSelect.value === removedLetter) {
        correctSelect.value = "";
      }
    }
  }


    function updateCorrectAnswerOptions(questionIdx) {
        const container = document.querySelector(`.choices-container[data-question-index="${questionIdx}"]`);
        const correctSelect = document.querySelector(`select.correct-answer-select[data-question-index="${questionIdx}"]`);
        const choices = container.querySelectorAll('.choice-block');

        correctSelect.innerHTML = '<option value="">Select</option>';
        choices.forEach((choice, index) => {
        const letter = letters[index];
        const option = document.createElement('option');
        option.value = letter;
        option.textContent = letter.toUpperCase();
        correctSelect.appendChild(option);
        });
    }

  // Add a new question block
  function addQuestion() {
    const container = document.getElementById('questions-section');
    const idx = questionIndex++;

    const div = document.createElement('div');
    div.classList.add('question-block');
    div.setAttribute('data-index', idx);
    div.innerHTML = `
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
        <div class="choices-container form-row" data-question-index="${idx}">
          <div class="form-group choice-block" data-letter="a">
            <label>Option A</label>
            <div class="input-with-icon">
              <input type="text" name="questions[${idx}][options][a]" required />
              <button type="button" class="icon-btn" onclick="removeChoice(${idx}, this)" title="Remove Choice">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <div class="form-group choice-block" data-letter="b">
            <label>Option B</label>
            <div class="input-with-icon">
              <input type="text" name="questions[${idx}][options][b]" required />
              <button type="button" class="icon-btn" onclick="removeChoice(${idx}, this)" title="Remove Choice">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
        <button type="button" class="btn add-choice-btn" onclick="addChoice(${idx})">+ Add Choice</button>

        <div class="form-row" style="margin-top:10px;">
          <div class="form-group">
            <label>Correct Answer</label>
            <select name="questions[${idx}][answer]" required class="correct-answer-select" data-question-index="${idx}">
              <option value="">Select</option>
              <option value="a">A</option>
              <option value="b">B</option>
            </select>
          </div>
        </div>
      </div>

      <button type="button" class="btn remove-question-btn" onclick="removeQuestion(${idx})" style="background:#e74c3c; margin-top:10px;">Remove Question</button>
      <hr />
    `;

    container.appendChild(div);
  }

  // Remove entire question block
  function removeQuestion(idx) {
    const block = document.querySelector(`.question-block[data-index="${idx}"]`);
    if (block) block.remove();
  }

  // Handle question type change to show/hide relevant inputs
  function handleQuestionTypeChange(idx) {
    const block = document.querySelector(`.question-block[data-index="${idx}"]`);
    if (!block) return;

    const select = block.querySelector('select.question-type');
    const container = block.querySelector('.question-type-container');
    const type = select.value;

    container.innerHTML = '';

    if (type === 'multiple_choice' || type === 'dropdown') {
      container.innerHTML = `
        <div class="choices-container form-row" data-question-index="${idx}">
          <div class="form-group choice-block" data-letter="a">
            <label>Option A</label>
            <div class="input-with-icon">
              <input type="text" name="questions[${idx}][options][a]" required />
              <button type="button" class="icon-btn" onclick="removeChoice(${idx}, this)" title="Remove Choice">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <div class="form-group choice-block" data-letter="b">
            <label>Option B</label>
            <div class="input-with-icon">
              <input type="text" name="questions[${idx}][options][b]" required />
              <button type="button" class="icon-btn" onclick="removeChoice(${idx}, this)" title="Remove Choice">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
        <button type="button" class="btn add-choice-btn" onclick="addChoice(${idx})">+ Add Choice</button>
        <div class="form-row" style="margin-top:10px;">
          <div class="form-group">
            <label>Correct Answer</label>
            <select name="questions[${idx}][answer]" required class="correct-answer-select" data-question-index="${idx}">
              <option value="">Select</option>
              <option value="a">A</option>
              <option value="b">B</option>
            </select>
          </div>
        </div>
      `;
    } else if (type === 'essay') {
      container.innerHTML = `
        <div class="choices-container form-row" data-question-index="${idx}">
          <div class="form-group wide">
            <label>Answer Guide (Optional)</label>
            <textarea name="questions[${idx}][answer]" placeholder="Expected answer or notes..."></textarea>
          </div>
        </div>
      `;
    } else if (type === 'file_upload') {
      container.innerHTML = `
        <div class="choices-container form-row" data-question-index="${idx}">
          <div class="form-group wide">
            <label>File Instructions (Optional)</label>
            <textarea name="questions[${idx}][answer]" placeholder="e.g., Upload a PDF report..."></textarea>
          </div>
        </div>
      `;
    } else if (type === 'fill_blank') {
      container.innerHTML = `
        <div class="choices-container form-row" data-question-index="${idx}">
          <div class="form-group wide">
            <label>Answer (Exact Match)</label>
            <input type="text" name="questions[${idx}][answer]" placeholder="e.g., Manila" />
          </div>
        </div>
      `;
    }
  }
</script>
