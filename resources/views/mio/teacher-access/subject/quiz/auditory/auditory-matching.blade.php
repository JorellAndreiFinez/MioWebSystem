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
                            <form action="{{ route('mio.subject-teacher.auditory-matching.delete', ['subjectId' => request()->route('subjectId'), 'difficulty' => $difficulty, 'activityId' => $activityId]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this activity?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="take-quiz-btn" style="background-color: #e74c3c;" onclick="event.stopPropagation();">🗑️ Delete</button>
                            </form>
                        </div>

                        {{-- Hidden Phrase Grid (toggle on click) --}}
                        {{-- Phrase Items --}}
                        <div class="phrase-items-grid" id="activity-{{ $activityId }}" style="display: none;">
                            @if(isset($activity['items']) && is_array($activity['items']))
                                @foreach($activity['items'] as $phraseId => $item)
                                    <div class="phrase-card">
                                        <div class="phrase-text" style="font-weight: bold;">{{ $item['text'] ?? 'No text' }}</div>

                                        {{-- Image --}}
                                        <div class="phrase-image" style="margin-top: 8px;">
                                            @if(!empty($item['image_url']))
                                                <img src="{{ $item['image_url'] }}" alt="Phrase Image" style="width: 100%; height: auto; max-height: 200px; object-fit: contain; border: 1px solid #ccc;">
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
            <h2>Add Matching Activity</h2>
            <form action="{{ route('mio.subject-teacher.auditory-matching.add', ['subjectId' => request()->route('subjectId')]) }}" method="POST" enctype="multipart/form-data">
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
                    <label>Matching Items</label>
                    <div class="phrase-item" data-index="0">
                            <input type="text" name="items[0][text]" placeholder="Enter phrase text" required>

                            <input type="file" name="items[0][image]" accept="image/*" onchange="previewImage(this, 0)">
                            <div id="image-preview-0" style="margin-top: 5px;"></div> <!-- ✅ Add this line -->

                            <hr style="margin: 10px 0; border: none; border-top: 1px dashed #ccc;">

                            <input type="file" name="items[0][audio]" accept="audio/*" onchange="previewAudio(this, 0)">
                            <div id="waveform-preview-0" style="margin-top: 10px;"></div>

                            <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                                <button type="button" class="remove-item-btn" onclick="removePhraseItem(this)">🗑️</button>
                            </div>
                        </div>

                </div>


                <button type="button" onclick="addPhraseItem()">+ Add Phrase</button>

                <button type="submit">Save Activity</button>
            </form>
        </div>
    </div>


    <!-- Edit Activity Modal -->
    <div id="editActivityModal" class="modal assignment-modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editActivityModal').style.display='none'">&times;</span>

            <h2>Edit Phrase Activity</h2>
            <form id="editActivityForm" method="POST" action="{{ route('mio.subject-teacher.auditory-matching.edit', ['subjectId' => request()->route('subjectId')]) }}" enctype="multipart/form-data">
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

                <button type="button" onclick="addEditPhraseItem()">+ Add Phrase</button>
                <button type="submit">Update Activity</button>
            </form>
        </div>
    </div>



</section>

    <script src="https://unpkg.com/wavesurfer.js"></script>

<!-- ADD/REMOVE -->

<script>
        let phraseIndex = 1;
        const waveSurfers = {};

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

        function addPhraseItem() {
            const container = document.getElementById('phrase-items-container');

            const itemHTML = `
                <div class="phrase-item" data-index="${phraseIndex}">
                    <input type="text" name="items[${phraseIndex}][text]" placeholder="Enter phrase text" required>
                    
                    <input type="file" name="items[${phraseIndex}][image]" accept="image/*" onchange="previewImage(this, ${phraseIndex})">
                    <div id="image-preview-${phraseIndex}" style="margin-top: 5px;"></div>

                    <hr style="margin: 10px 0; border: none; border-top: 1px dashed #ccc;">

                    <input type="file" name="items[${phraseIndex}][audio]" accept="audio/*" onchange="previewAudio(this, ${phraseIndex})">
                    <div id="waveform-preview-${phraseIndex}" style="margin-top: 10px;"></div>

                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                        <button type="button" class="remove-item-btn" onclick="removePhraseItem(this)">🗑️</button>
                        
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', itemHTML);
            phraseIndex++;
        }

        function removePhraseItem(button) {
            const itemDiv = button.closest('.phrase-item');
            if (itemDiv) {
                const index = itemDiv.dataset.index;
                if (waveSurfers[index]) {
                    waveSurfers[index].destroy();
                    delete waveSurfers[index];
                }
                itemDiv.remove();
            }
        }

        function previewAudio(input, index) {
            const containerId = `waveform-preview-${index}`;
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
                    container: `#waveform-${index}`, // ✅ FIXED: use the correct selector
                    waveColor: '#d9dcff',
                    progressColor: '#4353ff',
                    height: 60,
                    backend: 'MediaElement',
                    mediaControls: false,
                });

                waveSurfers[index] = wavesurfer;

                wavesurfer.load(e.target.result);

                wavesurfer.on('ready', () => {
                    console.log(`WaveSurfer ready (upload) for index ${index}`);
                });

                wavesurfer.on('error', (e) => {
                    console.error(`WaveSurfer error (upload)`, e);
                });

                wavesurfer.on('finish', () => {
                    const playBtn = document.getElementById(`play-btn-${index}`);
                    if (playBtn) playBtn.innerText = '▶️ Play';
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

        function removePhraseItem(button) {
        const itemDiv = button.closest('.phrase-item');
        if (!itemDiv) return;

        const index = itemDiv.getAttribute('data-index');
        
        // Destroy WaveSurfer instance if it exists
        if (waveSurfers[index]) {
            waveSurfers[index].destroy();
            delete waveSurfers[index];
        }

        // Remove the DOM element
        itemDiv.remove();
    }

</script>

<!-- EDIT -->
<script>
    let editPhraseIndex = 0;

function addEditPhraseItem(item = {}) {
    const container = document.getElementById('edit-phrase-items-container');
    const index = editPhraseIndex++;

    const checked = item.is_correct ? 'checked' : '';
    const textValue = item.text ?? '';
    const imageUrl = item.image_url ?? '';
    const audioUrl = item.audio_url ?? '';

    const itemHTML = `
        <div class="phrase-item" data-index="${index}">
            <input type="text" name="items[${index}][text]" placeholder="Enter phrase text" value="${textValue}" required>

            <input type="file" name="items[${index}][image]" accept="image/*" onchange="previewImage(this, ${index})">
            <div id="image-preview-${index}" style="margin-top: 5px;">
                ${imageUrl ? `
                    <small>Current Image:</small><br>
                    <img src="${imageUrl}" alt="Preview" style="max-width: 100px; max-height: 80px; border:1px solid #ccc; margin-top: 5px;">
                ` : ''}
            </div>

            <hr style="margin: 10px 0; border: none; border-top: 1px dashed #ccc;">

            <input type="file" name="items[${index}][audio]" accept="audio/*" onchange="previewEditAudio(this, ${index})">

            <div id="audio-preview-${index}" style="margin-top: 10px;">
                ${audioUrl ? `
                    <small>Current Audio:</small><br>
                    <audio controls style="width: 100%;">
                        <source src="${audioUrl}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                ` : ''}
            </div>

            <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                <button type="button" class="remove-item-btn" onclick="removePhraseItem(this)">🗑️</button>
                
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', itemHTML);
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


function populateEditItems(items = []) {
    const container = document.getElementById('edit-phrase-items-container');
    container.innerHTML = '';
    editPhraseIndex = 0;

    items.forEach((data) => {
        addEditPhraseItem({
            text: data.text ?? '',
            image_url: data.image_url ?? '',
            audio_url: data.audio_url ?? ''
        });
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
    const correctAnswers = activityData.correct_answers || [];
    const items = activityData.items || {};
    populateEditItems(items, correctAnswers);
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
