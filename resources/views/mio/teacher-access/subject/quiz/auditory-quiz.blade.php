@if ($subject['subjectType'] === 'specialized' && $subject['specialized_type'] === 'auditory')
<main class="main-assignment-content">

    <!-- 🟩 BINGO SECTION START -->
    <div id="bingo-container" class="mb-5">
        <h3 class="mb-4">BINGO</h3>

        <form method="POST" action="{{ route('mio.subject-teacher.save-auditory-bingo', ['subjectId' => $subject['subject_id']]) }}" enctype="multipart/form-data">
            @csrf

            @foreach (['easy', 'medium', 'hard'] as $level)
                <h4 class="mt-4 text-capitalize">{{ $level }} Level</h4>
                <div class="row bingo-gallery" id="bingo-gallery-{{ $level }}">
                    @if (!empty($bingoItems[$level]))
                        @foreach ($bingoItems[$level] as $cardId => $card)
                            @foreach ($card['items'] as $itemId => $item)
                                <!-- Add New Card Row -->
                            <!-- Add New Card Row -->
                        <div class="mb-4" id="new-bingo-{{ $level }}" style="display: none;">
                            <div class="card p-3 border">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="file" name="new_item[{{ $level }}][image]" class="form-control" accept="image/*">
                                        <small class="text-muted">Image</small>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="file" name="new_item[{{ $level }}][audio]" class="form-control" accept="audio/*">
                                        <small class="text-muted">Audio</small>
                                    </div>

                                    <div class="col-md-2">
                                        <select name="new_item[{{ $level }}][is_answer]" class="form-control">
                                            <option value="false">No</option>
                                            <option value="true">Yes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex gap-1">
                                        <button type="button" class="btn btn-success btn-sm" onclick="saveBingoItem()">Add</button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="cancelBingoAdd('{{ $level }}')">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                            @endforeach
                        @endforeach
                    @else
                        <p class="text-muted">No items found for {{ $level }} level.</p>
                    @endif
                </div>

                <!-- Add New Card Row -->
                <!-- Aesthetic Add New Card Row -->
                <div class="mb-4" id="new-bingo-{{ $level }}" style="display: none;">
                    <div class="card border shadow-sm rounded-4 p-4">
                        <div class="row g-4 align-items-center">

                            <!-- Image Upload with Preview -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Upload Image</label>
                                <input type="file" name="new_item[{{ $level }}][image]" class="form-control" accept="image/*" onchange="previewImage(event, '{{ $level }}')" required>
                                <img id="image-preview-{{ $level }}" src="#" alt="Preview" class="bingo-preview-image mt-2" style="visibility: hidden;">
                            </div>

                            <div class="card border p-4 mb-4">
                        <h5 class="mb-3">Create Audio Sample from Word</h5>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="tts-input" class="form-label">Enter Word or Phrase</label>
                                <input type="text" id="tts-input" class="form-control" placeholder="Type a word...">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100" onclick="generateAndDownloadSpeech()">Generate & Save Audio</button>
                            </div>
                        </div>
                    </div>


                            <!-- Audio Upload with Player -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Upload Audio</label>
                                <input type="file" name="new_item[{{ $level }}][audio]" class="form-control" accept="audio/*" onchange="previewAudio(event, '{{ $level }}')" required>
                                <audio id="audio-preview-{{ $level }}" class="bingo-preview-audio mt-2" controls style="visibility: hidden;"></audio>
                            </div>

                            <!-- Correct Answer Checkbox -->
                            <div class="col-md-2 d-flex flex-column justify-content-end align-items-center h-100">
                                <div class="form-check mt-auto">
                                    <input class="form-check-input" type="checkbox" name="new_item[{{ $level }}][is_answer]" value="true" id="is-answer-{{ $level }}">
                                    <label class="form-check-label fw-semibold" for="is-answer-{{ $level }}">
                                        Correct<br>Answer
                                    </label>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="col-md-2 d-flex flex-column justify-content-end align-items-start gap-2 h-100">
                                <button type="button" class="btn btn-success btn-sm w-100" onclick="saveBingoItem()">Add</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="cancelBingoAdd('{{ $level }}')">Cancel</button>
                            </div>

                        </div>
                    </div>

                </div>




                <div id="add-btn-container-{{ $level }}" class="mb-4">
                <button type="button" class="btn btn-outline-primary"
                    onclick="showBingoAdd('{{ $level }}')">
                    + Add Bingo Item to {{ ucfirst($level) }}
                </button>
            </div>


            @endforeach

            <div class="text-end">
                <button type="submit" class="btn btn-primary mt-3 px-4" id="submit-bingo-btn" style="display: none;">Submit All Changes</button>
            </div>
        </form>
    </div>
    <!-- 🟥 BINGO SECTION END -->

</main>
@endif


<script>
   function showBingoAdd(level) {
    // Hide all other add buttons and forms
    ['easy', 'medium', 'hard'].forEach(lvl => {
        if (lvl !== level) {
            const otherBtn = document.getElementById('add-btn-container-' + lvl);
            const otherForm = document.getElementById('new-bingo-' + lvl);
            if (otherBtn) otherBtn.style.display = 'none';
            if (otherForm) otherForm.style.display = 'none';
        }
    });

    // Show the form for selected level, hide its own button
    document.getElementById('new-bingo-' + level).style.display = '';
    document.getElementById('submit-bingo-btn').style.display = 'inline-block';
    document.getElementById('add-btn-container-' + level).style.display = 'none';
}

function cancelBingoAdd(level) {
    // Hide the form for current level
    document.getElementById('new-bingo-' + level).style.display = 'none';

    // Show all add buttons again
    ['easy', 'medium', 'hard'].forEach(lvl => {
        const btn = document.getElementById('add-btn-container-' + lvl);
        if (btn) btn.style.display = 'block';
    });
}

function saveBingoItem() {
    alert('New item will be added upon form submission.');
    document.getElementById('submit-bingo-btn').style.display = 'inline-block';

    // Hide all forms and show all add buttons again
    ['easy', 'medium', 'hard'].forEach(level => {
        const form = document.getElementById('new-bingo-' + level);
        const btn = document.getElementById('add-btn-container-' + level);
        if (form) form.style.display = 'none';
        if (btn) btn.style.display = 'block';
    });
}

function previewImage(event, level) {
    const input = event.target;
    const preview = document.getElementById('image-preview-' + level);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.visibility = 'visible';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewAudio(event, level) {
    const input = event.target;
    const preview = document.getElementById('audio-preview-' + level);
    if (input.files && input.files[0]) {
        const audioURL = URL.createObjectURL(input.files[0]);
        preview.src = audioURL;
        preview.style.visibility = 'visible';
    }
}


</script>

<script>
async function generateAndDownloadSpeech() {
    const text = document.getElementById('tts-input').value.trim();
    if (!text) {
        alert("Please enter a word or phrase.");
        return;
    }

    const apiKey = 'd2035897d59641a7a653f4c23b349d62';
    const apiUrl = `https://api.voicerss.org/?key=${apiKey}&hl=en-us&src=${encodeURIComponent(text)}&c=MP3&f=44khz_16bit_stereo`;

    try {
        const response = await fetch(apiUrl);
        if (!response.ok || response.headers.get("Content-Type") !== "audio/mpeg") {
            throw new Error("Failed to fetch audio or invalid response.");
        }

        const blob = await response.blob();
        const fileName = text.replace(/\s+/g, '_') + '.mp3';
        const file = new File([blob], fileName, { type: 'audio/mpeg' });

        // Assign file to appropriate level's audio input
        const levels = ['easy', 'medium', 'hard'];
        levels.forEach(level => {
            const audioInput = document.querySelector(`input[name="new_item[${level}][audio]"]`);
            if (audioInput && document.getElementById('new-bingo-' + level).style.display !== 'none') {
                const dt = new DataTransfer();
                dt.items.add(file);
                audioInput.files = dt.files;

                // Show preview
                previewAudio({ target: audioInput }, level);
            }
        });

        // Play audio
        const player = new Audio(URL.createObjectURL(blob));
        player.play();

    } catch (err) {
        console.error(err);
        alert("Failed to generate speech audio.");
    }
}

</script>










<style>
    .bingo-preview-image {
    display: block;
    width: 100%;
    max-height: 120px;
    height: 120px;
    object-fit: contain;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    background-color: #f8f9fa;
}

.bingo-preview-audio {
    display: block;
    width: 100%;
    max-height: 120px;
    height: 120px;
    object-fit: contain;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    background-color: #f8f9fa;
}

</style>
