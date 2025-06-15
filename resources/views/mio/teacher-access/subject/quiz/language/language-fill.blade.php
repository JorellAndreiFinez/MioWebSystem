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

                        @php
                            $mappedActivity = [
                                'activity_title' => $activity['activity_title'] ?? 'Untitled',
                                'items' => collect($activity['items'] ?? [])->map(function ($item, $itemId) {
                                    return [
                                        'qid' => $itemId,
                                        'question_text' => $item['sentence'] ?? '',
                                        'audio_path' => $item['audio_path'] ?? '',
                                        'filename' => $item['filename'] ?? '',
                                        'image_url' => null,
                                        'items' => collect($item['distractors'] ?? [])->map(function ($text) {
                                            return ['text' => $text];
                                        })->values()
                                    ];
                                })->values()
                            ];
                        @endphp


                        <div class="activity-actions" style="margin-top: 10px; display: flex; gap: 10px;">
                            <!-- Edit Button -->
                            <button
                                onclick="handleEditButtonClick(this)"
                                data-activity-id="{{ $activityId }}"
                                data-difficulty="{{ $difficulty }}"
                                data-activity='@json($mappedActivity)'>
                                ✏️ Edit
                            </button>




                            <!-- Delete Button -->
                            <form action="{{ route('mio.subject-teacher.language-fill.delete', ['subjectId' => request()->route('subjectId'), 'difficulty' => $difficulty, 'activityId' => $activityId]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this activity?');" style="margin: 0;">
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
                                <p>No fillitems found for this activity.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endforeach
        @else
            <p>No fill activities found.</p>
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

                <label>Add Image (optional)</label>
                <input type="file" name="questions[0][question_image]" accept="image/*">

                <label>Upload Audio<span style="color:red">*</span></label>
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
let choiceIndex = 1;

function addChoiceItem(button) {
    const questionBlock = button.closest('.question-block');
    const choicesContainer = questionBlock.querySelector('.choices-container');
    const qIndex = questionBlock.dataset.qindex;

    const currentChoicesCount = choicesContainer.querySelectorAll('.choice-item').length;

    const itemDiv = document.createElement('div');
    itemDiv.classList.add('choice-item');

    itemDiv.innerHTML = `
        <input type="text" name="items[${qIndex}][distractors][${currentChoicesCount}]" placeholder="Choice text (e.g. 'Dog')" required>
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

    const newQuestion = document.createElement('div');
    newQuestion.classList.add('question-block');
    newQuestion.dataset.qindex = questionIndex;

    newQuestion.innerHTML = `
    <hr>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <button type="button" class="remove-question-btn" onclick="removeQuestionBlock(this)" title="Remove Sentence" style="background: none; border: none; font-size: 20px; cursor: pointer;">🗑️</button>
    </div>

    <label>Sentence</label>
    <input type="text" name="questions[${questionIndex}][question_text]" placeholder="Enter your question here" required>

    <label>Add Image (optional)</label>
    <input type="file" name="questions[${questionIndex}][question_image]" accept="image/*">

    <label>Upload Audio<span style="color:red">*</span></label>
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
        activity.items.forEach((question, qIdx) => {
            const questionBlock = document.createElement('div');
            questionBlock.classList.add('question-block');
            questionBlock.dataset.qindex = editQuestionIndex;

            const choiceItems = question.items.map((choice, cIdx) => `
                <div class="choice-item">
                    <input type="text" name="questions[${editQuestionIndex}][items][${cIdx}][text]" value="${choice.text}" required>
                    <button type="button" class="remove-item-btn" onclick="removeChoiceItem(this)">🗑️</button>
                </div>
            `).join('');

            questionBlock.innerHTML = `
                <button type="button" class="remove-question-btn" onclick="removeQuestionBlock(this)" style="position: absolute; top: -10px; right: -10px; background: none; border: none; font-size: 20px; cursor: pointer;">🗑️</button>

                <hr>

                <label>Sentence</label>
                <input type="text" name="questions[${editQuestionIndex}][question_text]" value="${question.question_text}" required>
                

                <label>Add Image (optional)</label>
                <input type="file" name="questions[${editQuestionIndex}][question_image]" accept="image/*">

                <label>Upload Audio<span style="color:red">*</span></label>
                <input type="hidden" name="questions[${editQuestionIndex}][old_audio_path]" value="${question.audio_path || ''}">
                <input type="hidden" name="questions[${editQuestionIndex}][old_audio_filename]" value="${question.audio_filename || ''}">
                <input type="hidden" name="questions[${editQuestionIndex}][old_audio_id]" value="${question.audio_id || ''}">
                <input type="text" name="questions[${editQuestionIndex}][old_audio_url]" value="${question.audio_url || ''}">

                <input type="hidden" name="questions[${editQuestionIndex}][old_image_path]" value="${question.image_path || ''}">
                <input type="hidden" name="questions[${editQuestionIndex}][old_image_filename]" value="${question.image_filename || ''}">
                <input type="hidden" name="questions[${editQuestionIndex}][old_image_id]" value="${question.image_id || ''}">
                <input type="hidden" name="questions[${editQuestionIndex}][old_image_url]" value="${question.image_url || ''}">

                <audio controls style="margin-top: 10px;" id="fallback-audio-${editQuestionIndex}">
                    <source src="${question.audio_url || ''}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>

                <div id="waveform-preview-question-${editQuestionIndex}" style="margin-top: 10px;"></div>

                <hr>

                <label>Distractors</label>
                <div class="choices-container">
                    ${choiceItems}
                </div>
                <button type="button" class="add-icon-btn" onclick="addChoiceItem(this)" title="Add Distractor">➕ Add Distractor</button>
            `;

            container.appendChild(questionBlock);

            const audioPreviewURL = question.audio_url || question.audio_path;
            if (audioPreviewURL) {
                setTimeout(() => {
                    previewExistingAudio(audioPreviewURL, editQuestionIndex);
                }, 100);
            }


            editQuestionIndex++;
        });
    }

    toggleModal('editActivityModal');
}

function previewExistingAudio(audioUrl, index) {
    const container = document.getElementById(`waveform-preview-question-${index}`);
    if (!container || !audioUrl) return;

    // Clear existing content inside the container
    container.innerHTML = '';

    // Create a native <audio> element
    const audioElement = document.createElement('audio');
    audioElement.controls = true;
    audioElement.style.marginTop = '10px';

    const source = document.createElement('source');
    source.src = audioUrl;
    source.type = 'audio/mpeg';

    audioElement.appendChild(source);
    container.appendChild(audioElement);
}




function addEditQuestion() {
    const container = document.getElementById('edit-questions-container');

    const questionBlock = document.createElement('div');
    questionBlock.classList.add('question-block');
    questionBlock.dataset.qindex = editQuestionIndex;

    questionBlock.innerHTML = `
    <hr>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <button type="button" class="remove-question-btn" onclick="removeQuestionBlock(this)" title="Remove Sentence" style="background: none; border: none; font-size: 20px; cursor: pointer;">🗑️</button>
    </div>

    <label>Sentence</label>
    <input type="text" name="questions[${editQuestionIndex}][question_text]" placeholder="Enter your question here" required>

    <label>Add Image (optional)</label>
    <input type="file" name="questions[${editQuestionIndex}][question_image]" accept="image/*">

    <label>Upload Audio<span style="color:red">*</span></label>
    <input type="file" name="questions[${editQuestionIndex}][question_audio]" accept="audio/*" required onchange="previewQuestionAudio(this, ${editQuestionIndex})">
    <div id="waveform-preview-question-${editQuestionIndex}" style="margin-top: 10px;"></div>

    <hr>

    <label>Distractors</label>
    <div class="choices-container">
        <div class="choice-item">
            <input type="text" name="questions[${editQuestionIndex}][items][0][text]" placeholder="Choice text (e.g. 'Dog')" required>
            <button type="button" class="remove-item-btn" onclick="removeChoiceItem(this)">🗑️</button>
        </div>
    </div>
    <button type="button" class="add-icon-btn" onclick="addChoiceItem(this)" title="Add Distractor">➕ Add Distractor</button>
    `;

    container.appendChild(questionBlock);
    editQuestionIndex++;
}

function removePhraseItem(button) {
    const itemDiv = button.closest('.phrase-item');
    if (itemDiv) itemDiv.remove();
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
