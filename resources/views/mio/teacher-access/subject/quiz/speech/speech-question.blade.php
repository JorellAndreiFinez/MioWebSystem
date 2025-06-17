<section class="home-section">
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject-teacher.quiz', ['subjectId' => $subjectId]) }}">
                Quizzes
            </a>
        </div>

        <div class="breadcrumb-item active" style="font-size: 1.3rem;">Question Activity</div>

    </div>

    @if(session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif


    <main class="main-assignment-content">
        @if (!empty($speech))
            @foreach ($speech as $difficulty => $activities)
                <h4 class="text-gray-600 mt-6 mb-2 font-semibold">Difficulty: {{ ucfirst($difficulty) }}</h4>
                @foreach ($activities as $activityId => $activity)
                    <div class="assignment-card activity-toggle" onclick="toggleActivityDetails('{{ $activityId }}')">
                        <div class="activity-info">
                            <h3>{{ $activity['activity_title'] ?? 'Untitled Activity' }}</h3>
                        </div>

                        <div class="details">
                            <div>
                                <span>Created at</span>
                                <strong>{{ \Carbon\Carbon::parse($activity['created_at'])->format('F j, Y g:i A') }}</strong>
                            </div>
                        </div>

                        <div class="activity-actions" style="margin-top: 10px; display: flex; gap: 10px;">
                            <!-- Edit Button -->
                            <a href="#" class="take-quiz-btn"
                                data-activity='@json($activity)'
                                data-activity-id="{{ $activityId }}"
                                data-difficulty="{{ $difficulty }}"
                                onclick="event.stopPropagation(); handleEditButtonClick(this)">
                                ✏️ Edit Activity
                            </a>

                            <!-- Delete Button -->
                            <form action="{{ route('mio.subject-teacher.speech-question.delete', ['subjectId' => request()->route('subjectId'), 'difficulty' => $difficulty, 'activityId' => $activityId]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this activity?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="take-quiz-btn" style="background-color: #e74c3c;" onclick="event.stopPropagation();">🗑️ Delete</button>
                            </form>
                        </div>

                        {{-- Hidden Picture Grid (toggle on click) --}}
                        {{-- Picture Items --}}
                        <div class="phrase-items-grid" id="activity-{{ $activityId }}" style="display: none;">
                            @if(isset($activity['items']) && is_array($activity['items']))
                                @foreach($activity['items'] as $phraseId => $item)
                                    <div class="phrase-card" style="border: 1px solid #ddd; padding: 10px; margin: 10px 0; width: 100%; max-width: 300px;">
                                        <div class="phrase-text" style="font-weight: bold;">{{ $item['text'] ?? 'No text' }}</div>
                                        <div class="phrase-image" style="margin-top: 8px;">
                                            @if(!empty($item['image_url']))
                                                <img src="{{ $item['image_url'] }}" alt="Phrase Image" style="width: 100%; height: auto; max-height: 200px; object-fit: contain; border: 1px solid #ccc;">
                                            @else
                                                <div class="no-image" style="color: #aaa;">No image</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p>No phrase items found for this activity.</p>
                            @endif
                        </div>

                    </div>

                @endforeach

            @endforeach
        @else
            <p>No question activities found.</p>
        @endif

        <!-- Add New Activity Button -->
        <div class="assignment-card">
            <div class="add-assignment-container">
                <a href="#" class="add-assignment-btn" onclick="toggleModal('addActivityModal')">+ Add Activity</a>
            </div>
        </div>
    </main>

    <br>

    <!-- Add Activity Modal -->
   <div id="addActivityModal" class="modal assignment-modal" style="display: none; margin-top: 2rem;">
  <div class="modal-content">
    <span class="close" onclick="toggleModal('addActivityModal')">&times;</span>
    <h2>Add Question Activity</h2>
    <form action="{{ route('mio.subject-teacher.speech-question.add', ['subjectId' => request()->route('subjectId')]) }}" method="POST" enctype="multipart/form-data">
      @csrf

      <label for="activity_title">Activity Title</label>
      <input type="text" name="activity_title" required>

      <label for="difficulty">Difficulty</label>
      <select name="difficulty" required style="margin-bottom: 2rem;">
        <option value="easy">Easy</option>
        <option value="medium">Medium</option>
        <option value="hard">Hard</option>
      </select>


      <div id="questions-container">
        <!-- The first question block -->
        <div class="question-block" data-qindex="0">
          <hr>
          <h3>Question 1</h3>

          <label>Question Text</label>
          <input type="text" name="questions[0][question_text]" placeholder="Enter your question here" required>

          <label>Add Image for Question (optional)</label>
          <input type="file" name="questions[0][question_image]" accept="image/*">

          <label>Choices</label>
          <div class="choices-container">
            <div class="choice-item">
              <input type="text" name="questions[0][items][0][text]" placeholder="Choice text (e.g. 'Dog')" required>
              <button type="button" class="remove-item-btn" onclick="removeChoiceItem(this)">🗑️</button>
            </div>
          </div>
          <button type="button" class="add-icon-btn" onclick="addChoiceItem(this)" title="Add Choice">➕ Add Choice</button>
        </div>
      </div>

      <button type="button" onclick="addNewQuestion()" class="add-question-btn" style="margin-top: 20px;">+ Add Question</button>

      <div class="form-footer" style="margin-top: 30px;">
        <button type="submit">Save Activity</button>
      </div>
    </form>
  </div>
</div>



    <!-- Edit Activity Modal -->
    <div id="editActivityModal" class="modal assignment-modal" style="display: none;">
  <div class="modal-content">
    <span class="close" onclick="toggleModal('editActivityModal')">&times;</span>
    <h2>Edit Question Activity</h2>
    <form id="editActivityForm" method="POST" action="{{ route('mio.subject-teacher.speech-question.edit', ['subjectId' => request()->route('subjectId')]) }}" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <input type="hidden" name="activity_id" id="editActivityId">

      <label for="editActivityTitle">Activity Title</label>
      <input type="text" name="activity_title" id="editActivityTitle" required>

      <label for="editActivityDifficulty">Difficulty</label>
      <select name="difficulty" id="editActivityDifficultySelect" required style="margin-bottom: 2rem;">
        <option value="easy">Easy</option>
        <option value="medium">Medium</option>
        <option value="hard">Hard</option>
      </select>

      <div id="edit-questions-container"></div>

      <button type="button" onclick="addEditQuestion()" class="add-question-btn" style="margin-top: 20px;">+ Add Question</button>

      <div class="form-footer" style="margin-top: 30px;">
        <button type="submit">Update Activity</button>
      </div>
    </form>
  </div>
</div>



</section>


<script>
function toggleModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    if (modal.style.display === "none" || modal.style.display === "") {
        modal.style.display = "block";
    } else {
        modal.style.display = "none";
    }
}

// Optional: close modal when clicking outside
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
};
</script>



<script>
let choiceIndex = 1;

function addChoiceItem(button) {
  const questionBlock = button.closest('.question-block');
  const choicesContainer = questionBlock.querySelector('.choices-container');
  const qIndex = questionBlock.dataset.qindex;

  const currentChoicesCount = choicesContainer.querySelectorAll('.choice-item').length;

  const itemDiv = document.createElement('div');
  itemDiv.classList.add('choice-item');

  itemDiv.innerHTML = `
    <input type="text" name="questions[${qIndex}][items][${currentChoicesCount}][text]" placeholder="Choice text (e.g. 'Dog')" required>
    <button type="button" class="remove-item-btn" onclick="removeChoiceItem(this)">🗑️</button>
  `;

  choicesContainer.appendChild(itemDiv);
}


function removeChoiceItem(button) {
    const itemDiv = button.closest('.choice-item');
    if (itemDiv) itemDiv.remove();
}
</script>

<script>
let questionIndex = 1; // Start from 1 because 0 is already in DOM

function addNewQuestion() {
  const container = document.getElementById('questions-container');

  // Create new question block
  const newQuestion = document.createElement('div');
  newQuestion.classList.add('question-block');
  newQuestion.dataset.qindex = questionIndex;

  newQuestion.innerHTML = `
    <hr>
    <h3>Question ${questionIndex + 1}</h3>

    <label>Question Text</label>
    <input type="text" name="questions[${questionIndex}][question_text]" placeholder="Enter your question here" required>

    <label>Add Image for Question (optional)</label>
    <input type="file" name="questions[${questionIndex}][question_image]" accept="image/*">

    <label>Choices</label>
    <div class="choices-container">
      <div class="choice-item">
        <input type="text" name="questions[${questionIndex}][items][0][text]" placeholder="Choice text (e.g. 'Dog')" required>
        <button type="button" class="remove-item-btn" onclick="removeChoiceItem(this)">🗑️</button>
      </div>
    </div>
    <button type="button" class="add-icon-btn" onclick="addChoiceItem(this)" title="Add Choice">➕ Add Choice</button>
  `;

  container.appendChild(newQuestion);
  questionIndex++;
}

// This function adds a choice input inside the relevant question block
function addChoiceItem(button) {
  // The button is inside question-block > find its .choices-container sibling
  let questionBlock = button.closest('.question-block');
  let choicesContainer = questionBlock.querySelector('.choices-container');

  // Count existing choices to index new one properly
  const qIndex = questionBlock.dataset.qindex;
  const currentChoicesCount = choicesContainer.querySelectorAll('.choice-item').length;

  // Create new choice item div
  const itemDiv = document.createElement('div');
  itemDiv.classList.add('choice-item');

  itemDiv.innerHTML = `
    <input type="text" name="questions[${qIndex}][items][${currentChoicesCount}][text]" placeholder="Choice text (e.g. 'Dog')" required>
    <button type="button" class="remove-item-btn" onclick="removeChoiceItem(this)">🗑️</button>
  `;

  choicesContainer.appendChild(itemDiv);
}

function removeChoiceItem(button) {
  const itemDiv = button.closest('.choice-item');
  if (itemDiv) itemDiv.remove();
}

function removeQuestionBlock(button) {
    const questionBlock = button.closest('.question-block');
    if (questionBlock) questionBlock.remove();
}

</script>


<script>
function handleEditButtonClick(element) {
    try {
        const activity = JSON.parse(element.getAttribute('data-activity'));
        const activityId = element.getAttribute('data-activity-id');
        const difficulty = element.getAttribute('data-difficulty');

        openEditModal(activity, activityId, difficulty);
    } catch (err) {
        console.error('Failed to open edit modal:', err);
    }
}

    function toggleActivityDetails(id) {
        const section = document.getElementById(`activity-${id}`);
        section.style.display = section.style.display === 'none' ? 'grid' : 'none';
    }

let editQuestionIndex = 0;

function openEditModal(activity, activityId, difficulty) {
    document.getElementById('editActivityId').value = activityId;
    document.getElementById('editActivityTitle').value = activity.activity_title || '';
    document.getElementById('editActivityDifficultySelect').value = difficulty;

    const container = document.getElementById('edit-questions-container');
    container.innerHTML = '';
    editQuestionIndex = 0;

    if (activity.items) {
        Object.entries(activity.items).forEach(([qid, question]) => {
            const questionBlock = document.createElement('div');
            questionBlock.classList.add('question-block');
            questionBlock.dataset.qindex = editQuestionIndex;

            let imagePreview = '';
            if (question.image_url) {
                imagePreview = `
                    <div>
                        <small>Current Image:</small><br>
                        <img src="${question.image_url}" style="max-width: 100px; max-height: 80px;">
                    </div>
                `;
            }

            const choiceItems = Object.entries(question.items || {}).map(([cid, choice], i) => `
                <div class="choice-item">
                  <input type="hidden" name="questions[${editQuestionIndex}][items][${i}][cid]" value="${cid}">
                  <input type="text" name="questions[${editQuestionIndex}][items][${i}][text]" value="${choice.text}" required>
                  <button type="button" class="remove-item-btn" onclick="removeChoiceItem(this)">🗑️</button>
                </div>
            `).join('');

            questionBlock.innerHTML = `
    <hr>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Question ${editQuestionIndex + 1}</h3>
        <button type="button" class="remove-question-btn" onclick="removeQuestionBlock(this)" title="Remove Question" style="background: none; border: none; font-size: 20px; cursor: pointer;">🗑️</button>
    </div>

    <input type="hidden" name="questions[${editQuestionIndex}][qid]" value="${qid}">
    <label>Question Text</label>
    <input type="text" name="questions[${editQuestionIndex}][question_text]" value="${question.question_text}" required>

    <label>Update Image (optional)</label>
    <input type="hidden" name="questions[${editQuestionIndex}][old_image_path]" value="${question.image_path || ''}">
    <input type="file" name="questions[${editQuestionIndex}][question_image]" accept="image/*">
    ${imagePreview}

    <label>Choices</label>
    <div class="choices-container">
        ${choiceItems}
    </div>
    <button type="button" class="add-icon-btn" onclick="addChoiceItem(this)" title="Add Choice">➕ Add Choice</button>
`;


            container.appendChild(questionBlock);
            editQuestionIndex++;
        });
    }

    toggleModal('editActivityModal');
}


function addEditQuestion() {
    const container = document.getElementById('edit-questions-container');

    const questionBlock = document.createElement('div');
    questionBlock.classList.add('question-block');
    questionBlock.dataset.qindex = editQuestionIndex;

    questionBlock.innerHTML = `
    <hr>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Question ${editQuestionIndex + 1}</h3>
        <button type="button" class="remove-question-btn" onclick="removeQuestionBlock(this)" title="Remove Question" style="background: none; border: none; font-size: 20px; cursor: pointer;">🗑️</button>
    </div>

    <label>Question Text</label>
    <input type="text" name="questions[${editQuestionIndex}][question_text]" placeholder="Enter your question here" required>

    <label>Add Image for Question (optional)</label>
    <input type="file" name="questions[${editQuestionIndex}][question_image]" accept="image/*">

    <label>Choices</label>
    <div class="choices-container">
        <div class="choice-item">
            <input type="text" name="questions[${editQuestionIndex}][items][0][text]" placeholder="Choice text (e.g. 'Dog')" required>
            <button type="button" class="remove-item-btn" onclick="removeChoiceItem(this)">🗑️</button>
        </div>
    </div>
    <button type="button" class="add-icon-btn" onclick="addChoiceItem(this)" title="Add Choice">➕ Add Choice</button>
`;


    container.appendChild(questionBlock);
    editQuestionIndex++;
}

function removePhraseItem(button) {
    const itemDiv = button.closest('.phrase-item');
    if (itemDiv) itemDiv.remove();
}
</script>





<style>
    input[type="file"] {
    margin-top: 5px;
    margin-bottom: 10px;
}

.question-block {
  border: 1px solid #ccc;
  padding: 15px;
  margin-bottom: 20px;
  border-radius: 5px;
  background: #f9f9f9;
}
 .choice-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}

.add-icon-btn {
    margin-top: 10px;
    background: none;
    border: none;
    font-size: 1em;
    color: #007bff;
    cursor: pointer;
}

.add-icon-btn:hover {
    text-decoration: underline;
}

.remove-item-btn {
    background: none;
    border: none;
    font-size: 1.2em;
    color: red;
    cursor: pointer;
}

.form-footer {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}

.add-question-btn {
    margin-left: 10px;
    padding: 6px 12px;
    background-color: #28a745;
    border: none;
    color: white;
    cursor: pointer;
    border-radius: 4px;
}

.add-question-btn:hover {
    background-color: #218838;
}
    .phrase-items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 16px;
    padding: 15px;
    margin-top: 15px;
    border-top: 1px solid #ccc;
    background: #f9f9f9;
    border-radius: 6px;
}

.phrase-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 10px;
    text-align: center;
}

.phrase-image img {
    max-width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 4px;
    margin-top: 6px;
}

.no-image {
    width: 100%;
    height: 100px;
    background: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    font-size: 14px;
    margin-top: 6px;
}

</style>
