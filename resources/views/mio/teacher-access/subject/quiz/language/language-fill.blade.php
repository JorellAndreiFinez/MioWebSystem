<section class="home-section">
    <div class="text">
        Quiz
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
        @if (!empty($fill))
            @foreach ($fill as $difficulty => $activities)
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
                            <form action="{{ route('mio.subject-teacher.auditory-matching.delete', ['subjectId' => request()->route('subjectId'), 'difficulty' => $difficulty, 'activityId' => $activityId]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this activity?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="take-quiz-btn" style="background-color: #e74c3c;" onclick="event.stopPropagation();">🗑️ Delete</button>
                            </form>
                        </div>

                        {{-- Hidden Item Grid (toggle on click) --}}
                        <div class="phrase-items-grid" id="activity-{{ $activityId }}" style="display: none;">
                            @if(isset($activity['items']) && is_array($activity['items']))
                                @foreach($activity['items'] as $item)
                                    <div class="phrase-card">
                                        {{-- Sentence --}}
                                        <div class="phrase-text" style="font-weight: bold;">
                                            {{ $item['sentence'] ?? 'No sentence' }}
                                        </div>

                                        {{-- Image --}}
                                        <div class="phrase-image" style="margin-top: 8px;">
                                            @if(!empty($item['image_url']))
                                                <img src="{{ $item['image_url'] }}" alt="Item Image" style="width: 100%; height: auto; max-height: 200px; object-fit: contain; border: 1px solid #ccc;">
                                            @else
                                                <div class="no-image">No image</div>
                                            @endif
                                        </div>

                                        {{-- Audio --}}
                                        @if(!empty($item['audio_url']))
                                            <div class="phrase-audio" style="margin-top: 10px;">
                                                <audio controls style="width: 100%;">
                                                    <source src="{{ $item['audio_url'] }}" type="audio/mp3">
                                                    Your browser does not support the audio element.
                                                </audio>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p>No fill-in-the-blank items found for this activity.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endforeach
        @else
            <p>No fill-in-the-blank activities found.</p>
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
            <h2>Add Fill Activity</h2>
            <form action="{{ route('mio.subject-teacher.language-fill.add', ['subjectId' => request()->route('subjectId')]) }}" method="POST" enctype="multipart/form-data">
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
                <div class="question-block" data-qindex="0" style="position: relative;">
                <button type="button" class="remove-question-btn" onclick="removeQuestionBlock(this)" style="position: absolute; top: -10px; right: -10px; background: none; border: none; font-size: 20px; cursor: pointer;">🗑️</button>

                <hr>

                <label>Sentence</label>
                <input type="text" name="questions[0][question_text]" placeholder="Enter your question here" required>

                <label>Add Image for Question (optional)</label>
                <input type="file" name="questions[0][question_image]" accept="image/*">

                <label>Upload Audio for Question <span style="color:red">*</span></label>
                <input type="file" name="questions[0][question_audio]" accept="audio/*" required onchange="previewQuestionAudio(this, 0)">
                <div id="waveform-preview-question-0" style="margin-top: 10px;"></div>

                <hr>

                <label>Distractors</label>
                <div class="choices-container">
                    <div class="choice-item">
                    <input type="text" name="questions[0][items][0][text]" placeholder="Choice text (e.g. 'Dog')" required>
                    <button type="button" class="remove-item-btn" onclick="removeChoiceItem(this)">🗑️</button>
                    </div>
                </div>
                <button type="button" class="add-icon-btn" onclick="addChoiceItem(this)" title="Add Distractor">➕ Add Distractor</button>
                </div>
            </div>

            <button type="button" onclick="addNewQuestion()" class="add-question-btn" style="margin-top: 20px;">+ Add Sentence</button>

            <div class="form-footer" style="margin-top: 30px;">
                <button type="submit">Save Activity</button>
            </div>
            </form>
        </div>
        </div>




    <!-- Edit Activity Modal -->
    <div id="editActivityModal" class="modal assignment-modal" style="display: none; margin-top: 2rem;">
    <div class="modal-content">
        <span class="close" onclick="toggleModal('editActivityModal')">&times;</span>
        <h2>Edit Fill Activity</h2>
        <form id="editActivityForm" method="POST" action="{{ route('mio.subject-teacher.language-fill.edit', ['subjectId' => request()->route('subjectId')]) }}" enctype="multipart/form-data">
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

        <button type="button" onclick="addEditQuestion()" class="add-question-btn" style="margin-top: 20px;">+ Add Sentence</button>

        <div class="form-footer" style="margin-top: 30px;">
            <button type="submit">Update Activity</button>
        </div>
        </form>
    </div>
    </div>




</section>

<script>
let questionIndex = 1;
const waveSurfers = {};

function addNewQuestion() {
  const container = document.getElementById('questions-container');

  const newQuestion = document.createElement('div');
  newQuestion.classList.add('question-block');
  newQuestion.dataset.qindex = questionIndex;
  newQuestion.style.position = 'relative';

  newQuestion.innerHTML = `
    <button type="button" class="remove-question-btn" onclick="removeQuestionBlock(this)" style="position: absolute; top: -10px; right: -10px; background: none; border: none; font-size: 20px; cursor: pointer;">🗑️</button>

    <hr>

    <label>Sentence</label>
    <input type="text" name="questions[${questionIndex}][question_text]" placeholder="Enter your question here" required>

    <label>Add Image for Question (optional)</label>
    <input type="file" name="questions[${questionIndex}][question_image]" accept="image/*">

    <label>Upload Audio for Question <span style="color:red">*</span></label>
    <input type="file" name="questions[${questionIndex}][question_audio]" accept="audio/*" required onchange="previewQuestionAudio(this, ${questionIndex})">
    <div id="waveform-preview-question-${questionIndex}" style="margin-top: 10px;"></div>

    <hr>

    <label>Distractors</label>
    <div class="choices-container">
      <div class="choice-item">
        <input type="text" name="questions[${questionIndex}][items][0][text]" placeholder="Choice text (e.g. 'Dog')" required>
        <button type="button" class="remove-item-btn" onclick="removeChoiceItem(this)">🗑️</button>
      </div>
    </div>
    <button type="button" class="add-icon-btn" onclick="addChoiceItem(this)" title="Add Distractor">➕ Add Distractor</button>
  `;

  container.appendChild(newQuestion);
  questionIndex++;
}

function removeQuestionBlock(button) {
  const questionBlock = button.closest('.question-block');
  if (questionBlock) questionBlock.remove();
}

function previewQuestionAudio(input, index) {
  const containerId = `waveform-preview-question-${index}`;
  const container = document.getElementById(containerId);

  if (!input.files || input.files.length === 0) return;

  const file = input.files[0];
  const reader = new FileReader();

  reader.onload = function (e) {
    container.innerHTML = `
      <div id="waveform-${index}"></div>
      <button type="button" onclick="togglePlayback(${index})" id="play-btn-${index}">▶️ Play</button>
    `;

    const wavesurfer = WaveSurfer.create({
      container: `#waveform-${index}`,
      waveColor: '#d9dcff',
      progressColor: '#4353ff',
      height: 60,
      backend: 'MediaElement',
      mediaControls: false,
    });

    waveSurfers[index] = wavesurfer;
    wavesurfer.load(e.target.result);

    wavesurfer.on('finish', () => {
      const btn = document.getElementById(`play-btn-${index}`);
      if (btn) btn.innerText = '▶️ Play';
    });
  };

  reader.readAsDataURL(file);
}

function togglePlayback(index) {
  const wavesurfer = waveSurfers[index];
  const btn = document.getElementById(`play-btn-${index}`);
  if (!wavesurfer) return;

  if (wavesurfer.isPlaying()) {
    wavesurfer.pause();
    btn.innerText = '▶️ Play';
  } else {
    wavesurfer.play();
    btn.innerText = '⏸️ Pause';
  }
}
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
    function handleEditButtonClick(el) {
        const activity = JSON.parse(el.getAttribute('data-activity'));
        const activityId = el.getAttribute('data-activity-id');
        const difficulty = el.getAttribute('data-difficulty');

        const items = [];
        for (const itemId in activity.items) {
            const item = activity.items[itemId];
            items.push({
                phrase_id: itemId, // ✅ PRESERVE CORRECT ID
                question_text: item.sentence || '',
                question_image_url: item.image_url || '',
                old_image_url: item.image_url || '',
                old_image_path: item.image_path || '',
                audio_url: item.audio_url || '',
                old_audio_url: item.audio_url || '',
                old_audio_path: item.audio_path || '',
                old_filename: item.filename || '',
                items: (item.distractors || []).map(text => ({ text }))
            });
        }


        openEditActivityModal(activityId, activity.activity_title, difficulty, items);
    }

    function openEditActivityModal(activityId, activityTitle, difficulty, questions) {
    document.getElementById('editActivityId').value = activityId;
    document.getElementById('editActivityTitle').value = activityTitle;
    document.getElementById('editActivityDifficultySelect').value = difficulty;

    const container = document.getElementById('edit-questions-container');
    container.innerHTML = ''; // clear existing

    let qIndex = 0;
    for (const question of questions) {
        addEditQuestionBlock(qIndex, question);
        qIndex++;
    }

    questionIndex = qIndex; // update global index so new additions don’t overlap

    toggleModal('editActivityModal');
}
function generateUniquePhraseId() {
    const now = new Date();
    const timestamp = now.getTime();
    const random = Math.floor(Math.random() * 10000);
    return 'PH' + timestamp + random;
}

function addEditQuestionBlock(qIndex, data = {}) {
    const container = document.getElementById('edit-questions-container');

    const block = document.createElement('div');
    block.classList.add('question-block');
    block.dataset.qindex = qIndex;
    block.style.position = 'relative';

    block.innerHTML = `
    <input type="hidden" name="questions[${qIndex}][phrase_id]" value="${data.phrase_id || generateUniquePhraseId()}">
    <input type="hidden" name="questions[${qIndex}][old_audio_path]" value="${data.old_audio_path || ''}">
    <input type="hidden" name="questions[${qIndex}][old_audio_url]" value="${data.old_audio_url || ''}">
    <input type="hidden" name="questions[${qIndex}][old_filename]" value="${data.old_filename || ''}">
    <input type="hidden" name="questions[${qIndex}][old_image_path]" value="${data.old_image_path || ''}">
    <input type="hidden" name="questions[${qIndex}][old_image_url]" value="${data.old_image_url || ''}">

        <button type="button" class="remove-question-btn" onclick="removeQuestionBlock(this)" style="position: absolute; top: -10px; right: -10px; background: none; border: none; font-size: 20px; cursor: pointer;">🗑️</button>

        <hr>
        <input type="hidden" name="questions[${qIndex}][phrase_id]" value="${data.phrase_id || generateUniquePhraseId()}">


        <label>Sentence</label>
        <input type="text" name="questions[${qIndex}][question_text]" placeholder="Enter your question here" value="${data.question_text || ''}" required>

        <label>Add Image for Question (optional)</label>
        <input type="file" name="questions[${qIndex}][question_image]" accept="image/*">
        ${data.question_image_url ? `<p>📷 Existing Image: <a href="${data.question_image_url}" target="_blank">View</a></p>` : ''}

        <label>Upload Audio for Question <span style="color:red">*</span></label>
        <input type="file" name="questions[${qIndex}][question_audio]" accept="audio/*" onchange="previewQuestionAudio(this, ${qIndex})">
        <div id="waveform-preview-question-${qIndex}" style="margin-top: 10px;"></div>
        ${data.audio_url ? `
        <audio controls style="margin-top: 10px;">
            <source src="${data.audio_url}" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        ` : ''}

        <hr>

        <label>Distractors</label>
        <div class="choices-container">
            ${(data.items || []).map((item, i) => `
                <div class="choice-item">
                    <input type="text" name="questions[${qIndex}][items][${i}][text]" placeholder="Choice text" value="${item.text}" required>
                    <button type="button" class="remove-item-btn" onclick="removeChoiceItem(this)">🗑️</button>
                </div>
            `).join('')}
        </div>
        <button type="button" class="add-icon-btn" onclick="addChoiceItem(this)" title="Add Distractor">➕ Add Distractor</button>
    `;

    container.appendChild(block);
}
function addEditQuestion() {
    addEditQuestionBlock(questionIndex, {});
    questionIndex++;
}
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
    }
}
</script>


<script src="https://unpkg.com/wavesurfer.js"></script>
<script>
  const questionWaveSurfers = {};

  function previewQuestionAudio(input, index) {
    const containerId = `waveform-preview-question-${index}`;
    const container = document.getElementById(containerId);
    if (!input.files || input.files.length === 0) return;

    const file = input.files[0];
    const reader = new FileReader();

    reader.onload = function (e) {
      container.innerHTML = `
        <div id="waveform-question-${index}"></div>
        <button type="button" onclick="toggleQuestionPlayback(${index})" id="play-question-${index}">▶️ Play</button>
      `;

      const wavesurfer = WaveSurfer.create({
        container: `#waveform-question-${index}`,
        waveColor: '#cce5ff',
        progressColor: '#007bff',
        height: 60,
        backend: 'MediaElement',
      });

      questionWaveSurfers[index] = wavesurfer;

      wavesurfer.load(e.target.result);

      wavesurfer.on('finish', () => {
        const playBtn = document.getElementById(`play-question-${index}`);
        if (playBtn) playBtn.innerText = '▶️ Play';
      });
    };

    reader.readAsDataURL(file);
  }

  function toggleQuestionPlayback(index) {
    const wavesurfer = questionWaveSurfers[index];
    const btn = document.getElementById(`play-question-${index}`);
    if (!wavesurfer) return;

    if (wavesurfer.isPlaying()) {
      wavesurfer.pause();
      btn.innerText = '▶️ Play';
    } else {
      wavesurfer.play();
      btn.innerText = '⏸️ Pause';
    }
  }
</script>







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
