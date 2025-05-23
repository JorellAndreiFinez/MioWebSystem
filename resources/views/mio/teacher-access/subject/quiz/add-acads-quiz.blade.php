<section class="home-section">
  <div class="teacher-container">
    <main class="main-banner">
      <div class="banner">
        <h2>Add New Quiz - {{ $subject['title'] }}</h2>
      </div>
    </main>

    <main class="main-content">
      <form method="POST" enctype="multipart/form-data"action="{{ route('mio.subject-teacher.store-acads-quiz', ['subjectId' => $subjectId]) }}" id="quiz-form">
        @csrf

        <button type="submit" class="btn btn-primary">Create Academic Quiz</button>
        <!-- Quiz Info Section -->
        <div class="section-header">Quiz Information</div>
        <div class="section-content">
        <div class="form-row">
            <div class="form-group wide">
            <label>Quiz Title <span style="color:red">*</span></label>
            <input type="text" name="quiz[title]" value="Sample Quiz: Basic Math" placeholder="Enter Quiz Title" required />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group wide">
            <label>Description</label>
            <textarea name="quiz[description]" placeholder="Brief description or instructions...">A simple quiz to test basic arithmetic skills.</textarea>
            </div>
        </div>

        <div class="form-group">
            <label>Access Code (Optional)</label>
            <input type="text" name="quiz[access_code]" placeholder="Enter access code if needed" />
            </div>


        <div class="form-row">
            <div class="form-group">
            <label>Publish Date <span style="color:red">*</span></label>
            <input type="date" name="quiz[publish_date]" value="{{ date('Y-m-d') }}" required />
            </div>
            <div class="form-group">
            <label>Start Time <span style="color:red">*</span></label>
            <input type="time" name="quiz[start_time]" value="" required />
            </div>
            <div class="form-group">
            <label>Deadline Date (Optional)</label>
            <input type="date" name="quiz[deadline_date]" value="{{ date('Y-m-d', strtotime('+1 day')) }}" />
            </div>
            <div class="form-group">
            <label>End Time (Optional)</label>
            <input type="time" name="quiz[end_time]" value="17:00" />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Time Limit (in minutes)</label>
                <input type="number" name="quiz[time_limit]" id="time-limit" min="1" value="30" />

                <div style="margin-top: 5px;">
                    <label style="font-weight: normal;">
                    <input type="checkbox" id="no-time-limit" name="quiz[no_time_limit]" value="1" onchange="toggleTimeLimit()" />
                    No Time Limit
                    </label>
                </div>
                </div>


            <div class="form-group">
            <label>Total Points <span style="color:red">*</span></label>
            <input type="number" name="quiz[total_points]" min="1" value="10" required />
            </div>

            <div class="form-group">
            <label>Attempts Allowed <span style="color:red">*</span></label>
            <input type="number" name="quiz[attempts]" min="1" value="1" required />
            </div>
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


        <!-- Quiz Questions Section -->
        <div class="section-header">Quiz Questions</div>
        <div class="section-content" id="questions-section">

          <!-- Initial Question Block -->
          <div class="form-row">
            <div class="form-group">
                <label>Question Type</label>
                <select name="questions[0][type]" class="question-type" data-index="0" onchange="handleQuestionTypeChange(0)">
                <option value="multiple_choice" selected>Multiple Choice</option>
                <option value="essay">Essay</option>
                <option value="file_upload">File Upload</option>
                <option value="fill_blank">Fill in the Blanks</option>
                <option value="dropdown">Dropdown</option>
                </select>
            </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="max-width: 150px;">
                    <label>Points <span style="color:red">*</span></label>
                    <input
                    type="number"
                    name="questions[0][points]"
                    step="0.01" min="0.01"
                    class="question-points"
                    data-index="${idx}"
                    value=""
                    required
                    />
                </div>
            <div class="form-group wide">
                <label>Question <span style="color:red">*</span></label>
                <input type="text" name="questions[0][question]" placeholder="Enter the question" required />
            </div>
            </div>

            <!-- Dynamic question input types -->
            <div class="question-type-container" data-index="0">
            <div class="choices-container form-row" data-question-index="0"></div>
            <button type="button" class="btn add-choice-btn" onclick="addChoice(0)">+ Add Choice</button>
            <div class="form-row" style="margin-top:10px;">
                <div class="form-group">
                <label>Correct Answer</label>
                <select name="questions[0][answer]" required class="correct-answer-select" data-question-index="0">
                    <option value="">Select</option>
                </select>
                </div>
            </div>
            </div>

      </form>

    </main>
    <button type="button" class="btn add-btn" onclick="addQuestion()" style="display: block; margin: 0 auto; margin-bottom: 2rem; margin-top: 2rem;">
    + Add Question
    </button>
  </div>
</section>

<script>
   distributePointsEqually();

    function generateUUID() {
    return 'xxxxxxx'.replace(/[x]/g, function() {
        return (Math.random() * 16 | 0).toString(16);
    });
    }

    document.addEventListener('DOMContentLoaded', () => {
    const totalPointsInput = document.querySelector('input[name="quiz[total_points]"]');
        totalPointsInput.addEventListener('input', checkAndDistributePoints);

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('question-points')) {
                checkAndDistributePoints();
            }
        });

        initializeChoices(0);

    });

// Function to check if points exceed total and redistribute
function checkAndDistributePoints() {
    const totalPoints = parseFloat(document.querySelector('input[name="quiz[total_points]"]').value) || 0;
    const pointInputs = document.querySelectorAll('.question-points');
    const currentTotal = Array.from(pointInputs).reduce((sum, input) => {
        return sum + (parseFloat(input.value) || 0);
    }, 0);

    if (currentTotal > totalPoints) {
        const newPoint = (totalPoints / pointInputs.length).toFixed(2);
        pointInputs.forEach(input => input.value = newPoint);
    }
}

  let questionIndex = 1; // next question id
  const letters = 'abcdefghijklmnopqrstuvwxyz';

  // Add initial 4 choices (a-d) for question 0
 function initializeChoices(questionIdx) {
    const container = document.querySelector(`.choices-container[data-question-index="${questionIdx}"]`);
    if (!container) return;

    for (let i = 0; i < 4; i++) {
        addChoice(questionIdx);  // remove second param; just call with questionIdx
    }
    updateCorrectAnswerOptions(questionIdx);
    }

  // Add choice input to a question
function addChoice(questionIdx) {
  const container = document.querySelector(`.choices-container[data-question-index="${questionIdx}"]`);
  if (!container) return;

    const choiceUUID = generateUUID();

  const div = document.createElement('div');
  div.className = 'form-group choice-block';
  div.setAttribute('data-number', choiceUUID);

  div.innerHTML = `
      <label>Option</label>
      <div class="input-with-icon">
      <input type="text" name="questions[${questionIdx}][options][${choiceUUID}]" required />
      <button type="button" class="icon-btn" onclick="removeChoice(${questionIdx}, this)" title="Remove Choice">
          <i class="fas fa-times"></i>
      </button>
      </div>
  `;

  container.appendChild(div);

  // Add event listener to update correct answer dropdown on input change
  const input = div.querySelector('input');
  input.addEventListener('input', () => updateCorrectAnswerOptions(questionIdx));

  updateCorrectAnswerOptions(questionIdx);
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

  blocks.forEach((block, i) => {
    const num = i + 1; // Numbering starts at 1
    block.setAttribute('data-number', num);
    block.querySelector('label').textContent = `Choice`;
    const input = block.querySelector('input');
    input.setAttribute('name', `questions[${questionIdx}][options][${num}]`);
  });

  updateCorrectAnswerOptions(questionIdx);
}

  // Update the correct answer select options based on current choices
  function updateCorrectAnswerOptions(questionIdx) {
  const container = document.querySelector(`.choices-container[data-question-index="${questionIdx}"]`);
  const select = document.querySelector(`select.correct-answer-select[data-question-index="${questionIdx}"]`);
  if (!container || !select) return;

  const currentVal = select.value;
  select.innerHTML = `<option value="">Select</option>`;

  const choiceBlocks = container.querySelectorAll('.choice-block');

  choiceBlocks.forEach((block) => {
    const input = block.querySelector('input');
    const val = input.value.trim();
    const key = block.getAttribute('data-number'); // get the UUID key directly from choice-block div

    if (val && key) {
        const option = document.createElement('option');
        option.value = key; // set value to actual UUID key
        option.textContent = val;
        select.appendChild(option);
    }
    });


  // Keep the selected answer if it still exists
  if ([...select.options].some(opt => opt.value === currentVal)) {
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
    <div class="form-group" style="max-width: 150px;">
        <label>Points <span style="color:red">*</span></label>
        <input
        type="number"
        name="questions[${idx}][points]"
        step="0.01" min="0.01"
        class="question-points"
        data-index="${idx}"
        value=""
        required
        />
    </div>
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
   distributePointsEqually();
}

  // Remove question block
  function removeQuestion(idx) {
    const container = document.getElementById('questions-section');
    const block = container.querySelector(`.question-block[data-index="${idx}"]`);
    if (block) {
      container.removeChild(block);
    }
  }

  function distributePointsEqually() {
     const totalPointsInput = document.querySelector('input[name="quiz[total_points]"]');
        const totalPoints = parseFloat(totalPointsInput.value);
        const pointInputs = document.querySelectorAll('.question-points');

        if (!totalPoints || pointInputs.length === 0) return;

        const perQuestion = (totalPoints / pointInputs.length).toFixed(2);

        pointInputs.forEach(input => {
            input.value = perQuestion;
        });
    }

    function updateTotalPoints() {
        let total = 0;

        document.querySelectorAll('.question-points').forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val)) total += val;
        });

        const totalPointsInput = document.querySelector('input[name="quiz[total_points]"]');
        totalPointsInput.value = total.toFixed(2);
    }


  // When total points input changes, redistribute points
    document.getElementById('total-points-input').addEventListener('input', () => {
        distributePoints();
    });

    document.addEventListener('input', function (e) {
    if (e.target.classList.contains('question-points')) {
      updateTotalPoints();
    }
  });

  // Initialize points for the first question on load
    window.onload = function () {
    initializeChoices(0);
    distributePoints();
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
      for (let i = 0; i < 4; i++) {
        addChoice(index, i);
      }
      updateCorrectAnswerOptions(index);
      if (answerContainer) answerContainer.style.display = 'block';
      if (addChoiceBtn) addChoiceBtn.style.display = 'inline-block';
      break;

    // case 'dropdown':

    //     break;

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

