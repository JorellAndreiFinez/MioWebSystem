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
                            <form action="{{ route('mio.subject-teacher.speech-pronunciation.delete', ['subjectId' => request()->route('subjectId'), 'difficulty' => $difficulty, 'activityId' => $activityId]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this activity?');" style="margin: 0;">
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
                                    <div class="phrase-card" style="border: 1px solid #ddd; padding: 10px; margin: 10px 0; width: 100%; max-width: 300px;">
                                        <div class="phrase-text" style="font-weight: bold;">{{ $item['text'] ?? 'No text' }}</div>
                                        <div class="phrase-image" style="margin-top: 8px;">
                                            @if(!empty($item['image_path']))
                                                <img src="{{ $item['image_path'] }}" alt="Phrase Image" style="width: 100%; height: auto; max-height: 200px; object-fit: contain; border: 1px solid #ccc;">
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
            <p>No pronunciation activities found.</p>
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
            <h2>Add Pronunciation Activity</h2>
            <form action="{{ route('mio.subject-teacher.speech-pronunciation.add', ['subjectId' => request()->route('subjectId')]) }}" method="POST" enctype="multipart/form-data">
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
                    <label>Pronunciation Items</label>
                    <div class="phrase-item">
                        <input type="text" name="items[0][text]" placeholder="Enter phrase text" required>
                        <input type="file" name="items[0][image]" accept="image/*">
                        <button type="button" class="remove-item-btn" onclick="removePhraseItem(this)">🗑️</button>
                    </div>
                </div>

                <button type="button" onclick="addPhraseItem()">+ Add Pronunciation</button>

                <button type="submit">Save Activity</button>
            </form>
        </div>
    </div>


    <!-- Edit Activity Modal -->
    <div id="editActivityModal" class="modal assignment-modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editActivityModal').style.display='none'">&times;</span>

            <h2>Edit Pronunciation Activity</h2>
            <form id="editActivityForm" method="POST" action="{{ route('mio.subject-teacher.speech-pronunciation.edit', ['subjectId' => request()->route('subjectId')]) }}" enctype="multipart/form-data">
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

                <label>Phrase Items</label>
                <div id="edit-phrase-items-container"></div>

                <button type="button" onclick="addEditPhraseItem()">+ Add Pronunciation</button>
                <button type="submit">Update Activity</button>
            </form>
        </div>
    </div>


</section>

<script>
let phraseIndex = 1;

function addPhraseItem() {
    const container = document.getElementById('phrase-items-container');

    const itemDiv = document.createElement('div');
    itemDiv.classList.add('phrase-item');

    itemDiv.innerHTML = `
        <input type="text" name="items[${phraseIndex}][text]" placeholder="Enter phrase text" required>
        <input type="file" name="items[${phraseIndex}][image]" accept="image/*">
        <button type="button" class="remove-item-btn" onclick="removePhraseItem(this)">🗑️</button>
    `;

    container.appendChild(itemDiv);
    phraseIndex++;
}

function removePhraseItem(button) {
    const itemDiv = button.closest('.phrase-item');
    if (itemDiv) itemDiv.remove();
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

let editPhraseIndex = 0;

function openEditModal(activity, activityId, difficulty) {
    const modal = document.getElementById('editActivityModal');

    // Basic fields
    document.getElementById('editActivityId').value = activityId;
    document.getElementById('editActivityTitle').value = activity.activity_title || '';
    document.getElementById('editActivityDifficultySelect').value = difficulty;

    // Phrase container reset
    const container = document.getElementById('edit-phrase-items-container');
    container.innerHTML = '';
    editPhraseIndex = 0;

    // Render phrase items
    if (activity.items) {
        Object.entries(activity.items).forEach(([phraseId, item]) => {
            const itemDiv = document.createElement('div');
            itemDiv.classList.add('phrase-item');
            itemDiv.style.marginBottom = '10px';

            let imagePreview = '';
            if (item.image_path) {
                imagePreview = `
                    <div style="margin-top:5px;">
                        <small>Current Image:</small><br>
                        <img src="${item.image_path}" alt="Preview" style="max-width: 100px; max-height: 80px; border:1px solid #ccc; margin-top: 5px;">
                    </div>
                `;
            }

            itemDiv.innerHTML = `
                <input type="hidden" name="items[${editPhraseIndex}][speechID]" value="${phraseId}">
                <input type="text" name="items[${editPhraseIndex}][text]" value="${item.text || ''}" required>
                <input type="hidden" name="items[${editPhraseIndex}][old_image_path]" value="${item.image_path || ''}">
                <input type="file" name="items[${editPhraseIndex}][image]" accept="image/*">
                ${imagePreview}
                <button type="button" class="remove-item-btn" onclick="removePhraseItem(this)">🗑️</button>
            `;


            container.appendChild(itemDiv);
            editPhraseIndex++;
        });
    }

    modal.style.display = 'block';
}

function addEditPhraseItem() {
    const container = document.getElementById('edit-phrase-items-container');

    const itemDiv = document.createElement('div');
    itemDiv.classList.add('phrase-item');
    itemDiv.style.marginBottom = '10px';

    itemDiv.innerHTML = `
        <input type="text" name="items[${editPhraseIndex}][text]" placeholder="Enter phrase text" required>
        <input type="file" name="items[${editPhraseIndex}][image]" accept="image/*">
        <button type="button" class="remove-item-btn" onclick="removePhraseItem(this)">🗑️</button>
    `;

    container.appendChild(itemDiv);
    editPhraseIndex++;
}

function removePhraseItem(button) {
    const itemDiv = button.closest('.phrase-item');
    if (itemDiv) itemDiv.remove();
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
