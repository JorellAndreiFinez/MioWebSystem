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
                            <form action="{{ route('mio.subject-teacher.language-homonym.delete', ['subjectId' => request()->route('subjectId'), 'difficulty' => $difficulty, 'activityId' => $activityId]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this activity?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="take-quiz-btn" style="background-color: #e74c3c;" onclick="event.stopPropagation();">🗑️ Delete</button>
                            </form>
                        </div>

                        {{-- Hidden Phrase Grid (toggle on click) --}}
                        <div class="phrase-items-grid" id="activity-{{ $activityId }}" style="display: none;">
                            @if(isset($activity['items']) && is_array($activity['items']))
                                @foreach($activity['items'] as $item)
                                    {{-- Sentence 1 --}}
                                    <div class="phrase-card">
                                        <div class="phrase-text" style="font-weight: bold;">
                                            {{ $item['sentence_1'] ?? 'No sentence' }}
                                        </div>

                                        {{-- Audio 1 --}}
                                        @if(!empty($item['audio_url_1']))
                                            <div class="phrase-audio" style="margin-top: 10px;">
                                                <audio controls style="width: 100%;">
                                                    <source src="{{ $item['audio_url_1'] }}" type="audio/mp3">
                                                    Your browser does not support the audio element.
                                                </audio>
                                            </div>
                                        @endif

                                        {{-- Distractors --}}
                                        @if(!empty($item['distractors']) && is_array($item['distractors']))
                                            <div class="phrase-distractors" style="margin-top: 10px;">
                                                <strong>Distractors:</strong>
                                                <ul>
                                                    @foreach($item['distractors'] as $distractor)
                                                        | {{ $distractor }}
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Sentence 2 --}}
                                    <div class="phrase-card">
                                        <div class="phrase-text" style="font-weight: bold;">
                                            {{ $item['sentence_2'] ?? 'No sentence' }}
                                        </div>

                                        {{-- Audio 2 --}}
                                        @if(!empty($item['audio_url_2']))
                                            <div class="phrase-audio" style="margin-top: 10px;">
                                                <audio controls style="width: 100%;">
                                                    <source src="{{ $item['audio_url_2'] }}" type="audio/mp3">
                                                    Your browser does not support the audio element.
                                                </audio>
                                            </div>
                                        @endif

                                        {{-- Distractors (again if needed, or omit) --}}
                                        {{-- <div class="phrase-distractors" style="margin-top: 10px;">... </div> --}}
                                    </div>
                                @endforeach
                            @else
                                <p>No matching items found for this activity.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endforeach
        @else
            <p>No matching activities found.</p>
        @endif

        <!-- Add New Activity Button -->
        <div class="assignment-card">
            <div class="add-assignment-container">
                <a href="#" class="add-assignment-btn" onclick="toggleModal('addActivityModal')">+ Add Activity</a>
            </div>
        </div>
    </main>


    <!-- Add Activity Modal -->
    <div id="addActivityModal" class="modal assignment-modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="toggleModal('addActivityModal')">&times;</span>
        <h2>Add Homonyms Activity</h2>

        <form action="{{ route('mio.subject-teacher.language-homonym.add', ['subjectId' => request()->route('subjectId')]) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <label for="activity_title">Activity Title</label>
            <input type="text" name="activity_title" required>

            <label for="difficulty">Difficulty</label>
            <select name="difficulty" required>
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
            </select>

            <!-- Phrase Items Section -->
            <div id="phrase-items-container">
                <label>Homonyms Items</label>

                <div class="phrase-item" data-index="0">
                    <!-- Sentence Container -->
                    <div class="sentences-container" id="sentences-0"></div>
                    <button type="button" onclick="addSentence(0)">+ Add Sentence</button>

                    <hr>

                    <!-- Choices -->
                    <div id="choices-container-0">
                        <label>Distractors</label>
                        <div class="choice-input" style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                            <input type="text" name="items[0][choices][]" placeholder="Enter choice">
                            <button type="button" onclick="removeChoice(this)">🗑️</button>
                        </div>
                    </div>
                    <button type="button" onclick="addChoice(0)">+ Add Choice</button>
                </div>
            </div>
            <hr>
            <button type="submit">Save Activity</button>
        </form>
    </div>
</div>




    <!-- Edit Activity Modal -->
    <div id="editActivityModal" class="modal assignment-modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editActivityModal').style.display='none'">&times;</span>

            <h2>Edit Phrase Activity</h2>
            <form id="editActivityForm" method="POST" action="{{ route('mio.subject-teacher.language-homonym.edit', ['subjectId' => request()->route('subjectId')]) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" name="activity_id" id="editActivityId">

                <label for="editActivityTitle">Activity Title</label>
                <input type="text" name="activity_title" id="editActivityTitle" required>

                <label for="editActivityDifficulty">Difficulty</label>
                <select name="difficulty" id="editActivityDifficultySelect" required>
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                </select>

                <hr>

                <!-- Phrase Items Section -->
                <div id="edit-phrase-items-container">
                    <label>Matching Items</label>
                    <!-- Items will be dynamically inserted via JS -->
                </div>

                <button type="submit">Update Activity</button>
            </form>
        </div>
    </div>



</section>

    <script src="https://unpkg.com/wavesurfer.js"></script>

<!-- ADD/REMOVE -->

<script>
let phraseIndex = 1;
let sentenceCounters = { 0: 0 };
addSentence(0);
function addPhraseItem() {
    const container = document.getElementById('phrase-items-container');

    sentenceCounters[phraseIndex] = 0;

    const html = `
        <br><hr>
        <div class="phrase-item" data-index="${phraseIndex}">
            <div class="sentences-container" id="sentences-${phraseIndex}"></div>
            <button type="button" onclick="addSentence(${phraseIndex})">+ Add Sentence</button>

            <hr>

            <div id="choices-container-${phraseIndex}">
                <label>Distractors</label>
                <div class="choice-input" style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                    <input type="text" name="items[${phraseIndex}][choices][]" placeholder="Enter choice">

                    <button type="button" onclick="removeChoice(this)">🗑️</button>
                </div>
            </div>
            <button type="button" onclick="addChoice(${phraseIndex})">+ Add Choice</button>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    addSentence(phraseIndex); // Add 1 default sentence
    phraseIndex++;
}

function addSentence(index) {
    const container = document.getElementById(`sentences-${index}`);
    const sentenceIndex = sentenceCounters[index]++;

    const html = `
        <div class="sentence-item" data-sentence-index="${sentenceIndex}" style="border: 1px dashed #ccc; padding: 10px; margin-bottom: 10px;">
            <label>Sentence</label>
            <input type="text" name="items[${index}][sentences][${sentenceIndex}][sentence]" placeholder="Enter sentence" required>

            <label>Correct Answer</label>
            <input type="text" name="items[${index}][sentences][${sentenceIndex}][answer]" placeholder="Correct answer" required>

            <label>Audio</label>
            <input type="file" name="items[${index}][sentences][${sentenceIndex}][audio]" accept="audio/*">

            <label>Image (Optional)</label>
            <input type="file" name="items[${index}][sentences][${sentenceIndex}][image]" accept="image/*">

            <div style="margin-top: 5px;">
                <button type="button" onclick="removeSentence(this)">🗑️ Remove Sentence</button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
}

function removeSentence(button) {
    const div = button.closest('.sentence-item');
    if (div) div.remove();
}

function addChoice(index) {
    const container = document.getElementById(`choices-container-${index}`);
    const choiceCount = container.querySelectorAll('.choice-input').length;

    const html = `
        <div class="choice-input" style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
            <input type="text" name="items[${index}][choices][]" placeholder="Enter choice">
            <button type="button" onclick="removeChoice(this)">🗑️</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeChoice(button) {
    const div = button.closest('.choice-input');
    if (div) div.remove();
}

function removePhraseItem(button) {
    const itemDiv = button.closest('.phrase-item');
    if (!itemDiv) return;
    const index = itemDiv.getAttribute('data-index');
    delete sentenceCounters[index];
    itemDiv.remove();
}
</script>


<!-- EDIT -->
<script>
    let editPhraseIndex = 0;

    function addEditPhraseItem(item = {}, indexOverride = null) {
        const container = document.getElementById('edit-phrase-items-container');
        const index = indexOverride !== null ? indexOverride : editPhraseIndex++;
        sentenceCounters[index] = 0;
console.log('Adding sentence to index:', index);
        console.log('Current sentence counter:', sentenceCounters[index]);
        const sentences = item.sentences || {};
        const choices = item.choices || [];

        let sentenceHTML = '';
        let sentenceCounter = 0;

        for (const sentenceId in sentences) {
            const sentence = sentences[sentenceId];

            sentenceHTML += `
                <div class="sentence-item" data-sentence-index="${sentenceCounter}" style="border: 1px dashed #ccc; padding: 10px; margin-bottom: 10px;">
                    <label>Sentence</label>
                    <input type="text" name="items[${index}][sentences][${sentenceCounter}][sentence]" placeholder="Enter sentence" value="${sentence.sentence}" required>

                    <label>Correct Answer</label>
                    <input type="text" name="items[${index}][sentences][${sentenceCounter}][answer]" placeholder="Correct answer" value="${sentence.answer}" required>

                    <label>Audio</label>
                    <input type="file" name="items[${index}][sentences][${sentenceCounter}][audio]" accept="audio/*">
                    ${sentence.audio_url ? `
                        <br><small>Current Audio:</small><br>
                        <audio controls style="width: 100%;">
                            <source src="${sentence.audio_url}" type="audio/mpeg">
                        </audio>` : ''}

                    <label>Image (Optional)</label>
                    <input type="file" name="items[${index}][sentences][${sentenceCounter}][image]" accept="image/*">

                    <div style="margin-top: 5px;">
                        <button type="button" onclick="removeSentence(this)">🗑️ Remove Sentence</button>
                    </div>
                </div>
            `;

            sentenceCounter++;
        }

        sentenceCounters[index] = sentenceCounter;

        // If no sentence found, insert 1 default sentence via JS
        if (sentenceCounter === 0) {
            setTimeout(() => addSentence(index), 0); // delay ensures container is in DOM
        }

        // Choices and distractors
        let choiceHTML = '';
        for (const choice of choices) {
            choiceHTML += `
                <div class="choice-input" style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                    <input type="text" name="items[${index}][choices][]" value="${choice}" placeholder="Enter choice">
                    <button type="button" onclick="removeChoice(this)">🗑️</button>
                </div>
            `;
        }

        const itemHTML = `
            <br><hr>
            <div class="phrase-item" data-index="${index}">
                <div class="sentences-container" id="sentences-${index}">
                    ${sentenceHTML}
                </div>
                <button type="button" onclick="addSentence(${index})">+ Add Sentence</button>

                <hr>

                <div id="choices-container-${index}">
                    <label>Distractors</label>
                    ${choiceHTML}
                </div>
                <button type="button" onclick="addChoice(${index})">+ Add Choice</button>

            </div>
        `;

        container.insertAdjacentHTML('beforeend', itemHTML);
    }


    function addEditDistractor(index) {
        const container = document.getElementById(`edit-distractors-container-${index}`);
        if (!container) return;

        const inputHTML = `
            <div class="distractor-input" style="display: flex; align-items: center; gap: 5px; margin-top: 5px;">
                <input type="text" name="items[${index}][distractors][]" placeholder="Enter distractor">
                <button type="button" onclick="removeDistractor(this)">🗑️</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', inputHTML);
    }

    function removeDistractor(button) {
        const distractorDiv = button.closest('.distractor-input');
        if (distractorDiv) distractorDiv.remove();
    }



function previewEditAudio(input, index) {
    const container = document.getElementById(`audio-preview-${index}`);

    if (!input.files || input.files.length === 0) return;
    const file = input.files[0];
    const reader = new FileReader();

    reader.onload = function (e) {
        // Clear any existing waveform preview
        if (waveSurfers[index]) {
            waveSurfers[index].destroy();
            delete waveSurfers[index];
        }

        container.innerHTML = `
            <div id="waveform-${index}" style="margin-bottom: 5px;"></div>
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
            const playBtn = document.getElementById(`play-btn-${index}`);
            if (playBtn) playBtn.innerText = '▶️ Play';
        });
    };

    reader.readAsDataURL(file);
}


function populateEditItems(itemsObj = {}) {
    const container = document.getElementById('edit-phrase-items-container');
    container.innerHTML = '';
    editPhraseIndex = 0;

    Object.values(itemsObj).forEach((item, idx) => {
        addEditPhraseItem(item, idx);
        editPhraseIndex = idx + 1;
    });
}





function handleEditButtonClick(element) {
    const modal = document.getElementById('editActivityModal');
    const activityData = JSON.parse(element.getAttribute('data-activity'));
    const activityId = element.getAttribute('data-activity-id');
    const difficulty = element.getAttribute('data-difficulty');

    // Show modal
    modal.style.display = 'block';

    // Fill basic fields
    document.getElementById('editActivityId').value = activityId;
    document.getElementById('editActivityTitle').value = activityData.activity_title || '';
    document.getElementById('editActivityDifficultySelect').value = difficulty;

    // Populate items
    const items = activityData.items || {};
    populateEditItems(items);
}


function previewImage(input, index) {
    const container = document.getElementById(`image-preview-${index}`);
    if (!input.files || input.files.length === 0) return;

    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function (e) {
        container.innerHTML = `<img src="${e.target.result}" style="max-height: 100px;">`;
    };
    reader.readAsDataURL(file);
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

<script>
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


    function toggleActivityDetails(id) {
        const section = document.getElementById(`activity-${id}`);
        section.style.display = section.style.display === 'none' ? 'grid' : 'none';
    }

</script>


<style>
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
