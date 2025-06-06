<section class="home-section">
    <div class="text">Physical Evaluation</div>

    <div class="evaluation-section" style="padding: 2rem; max-width: 800px; margin: auto;">
        <h3>Speech & Auditory Test</h3>
        <p class="mb-4">Identifying words and phrases.</p>

        <!-- Speech Test (5 Items) -->
        <form id="speech-auditory-form" action="{{ route('assessment.speechace.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
                 <div class="card mb-5">
            <h4>Speech Test</h4>
            <p>Please speak the word or phrase shown. Your voice will be recorded.</p>

            @for ($i = 1; $i <= count($phrases); $i++)
            <div class="speech-item">
                <label>
                    <strong>Item {{ $i }}:</strong>
                     <span id="speech-phrase-{{ $i }}">
                        {{ $phrases[$i - 1]['text'] ?? $phrases[$i - 1] ?? '' }}
                    </span>
                </label>
                 @if (!empty($phrases[$i - 1]['image_url']))
                    <div class="speech-image mb-2">
                        <img src="{{ $phrases[$i - 1]['image_url'] }}" alt="Image for {{ $phrases[$i - 1]['text']}}" style="max-width: 150px; height: auto;">
                    </div>
                @endif
                <div class="speech-controls">
                    <div class="buttons">
                        <button type="button" id="start-btn-{{ $i }}" onclick="startRecording({{ $i }})" class="btn btn-sm btn-success">Start</button>
                        <button type="button" id="stop-btn-{{ $i }}" onclick="stopRecording({{ $i }})" class="btn btn-sm btn-danger" disabled>Stop</button>
                    </div>
                    <div id="waveform-{{ $i }}" class="waveform" style="width: 100%; height: 80px; margin-top: 10px;"></div>
                    <button type="button" id="play-pause-{{ $i }}" class="btn btn-sm btn-primary">Play/Pause</button>
                </div>
            </div>
        @endfor

        </div>

            <div class="card mb-5">
                <h4>Auditory Test</h4>
                <p>Listen to the audio and type what you hear.</p>

                @for ($i = 1; $i <= count($auditoryAnswers); $i++)
                <div class="mb-3">
                    <label><strong>Audio {{ $i }}:</strong></label>
                    <div>
                        <button type="button" onclick="playAuditory({{ $i }})" class="btn btn-sm btn-info">🔊 Play Audio</button>
                        <input type="text" name="auditory_inputs[]" class="form-control mt-2" placeholder="Type what you heard...">

                        <!-- NEW: Volume level for SRT -->
                        <input type="hidden" name="auditory_volume_levels[]" id="volume-level-{{ $i }}" value="1.0">
                        <div class="mt-2">
                            <label for="volume-slider-{{ $i }}" class="form-label">Volume Level</label>
                            <input type="range" id="volume-slider-{{ $i }}" min="0.2" max="1.0" step="0.2" value="1.0"
                                onchange="updateVolumeLevel({{ $i }}, this.value)">
                            <span id="volume-display-{{ $i }}">1.0</span>
                        </div>
                    </div>
                </div>
                @endfor


            </div>
            <div style="text-align: right; margin-top: 2rem;">
        <button type="submit" id="submit-speechace" class="btn btn-primary">Next</button>
        </div>
        </form>



    </div>
</section>

<!-- Replace with actual SpeechAce widget or API integration -->
<script src="https://api.speechace.co/api/scoring/feedback/latest/js/speechace.min.js"></script>
<script>
const recordings = {}; // store audio blobs per item
let mediaRecorder;
let audioChunks = [];

 const speechCount = {{ count($phrases) }};

function startRecording(id) {
    const startBtn = document.querySelector(`#start-btn-${id}`);
    const stopBtn = document.querySelector(`#stop-btn-${id}`);

    // Disable the button to prevent multiple clicks
    startBtn.disabled = true;
    startBtn.textContent = 'Recording...';
    stopBtn.disabled = false;

    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.ondataavailable = event => {
                audioChunks.push(event.data);
            };

            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                recordings[id] = audioBlob;

                const waveformContainer = document.getElementById(`waveform-${id}`);
                const playPauseBtn = document.getElementById(`play-pause-${id}`);

                // Clear previous wavesurfer if exists
                if (waveformContainer.wavesurfer) {
                    waveformContainer.wavesurfer.destroy();
                }

                // Initialize Wavesurfer
                const wavesurfer = WaveSurfer.create({
                    container: waveformContainer,
                    waveColor: '#4CAF50',
                    progressColor: '#2E7D32',
                    cursorColor: '#333',
                    height: 80,
                    responsive: true,
                });

                waveformContainer.wavesurfer = wavesurfer;

                // Load audio blob
                wavesurfer.loadBlob(audioBlob);

                // Play/pause toggle
                playPauseBtn.onclick = () => {
                    wavesurfer.playPause();
                };

                // Show waveform and play button
                waveformContainer.style.display = 'block';
                playPauseBtn.style.display = 'inline-block';

                // Reset buttons
                const startBtn = document.querySelector(`#start-btn-${id}`);
                const stopBtn = document.querySelector(`#stop-btn-${id}`);

                startBtn.disabled = false;
                startBtn.textContent = 'Start Recording';
                stopBtn.disabled = true;
                };


            mediaRecorder.start();
        })
        .catch(error => {
            alert('Microphone access denied or not supported.');
            console.error(error);

            // Re-enable button on error
            startBtn.disabled = false;
            startBtn.textContent = 'Start Recording';
            stopBtn.disabled = true;
        });
}

function stopRecording(id) {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
    }
}

document.getElementById('speech-auditory-form').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!confirm("Are you sure your answers are final? You won't be able to go back.")) {
        return;
    }

    const submitBtn = document.getElementById('submit-speechace');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';

    const formData = new FormData(this); // 'this' is the form element in submit handler


    // Append speech texts and audio blobs
    for (let i = 1; i <= speechCount; i++) {
        const audioBlob = recordings[i];
        const text = document.getElementById('speech-phrase-' + i).textContent;

        if (!audioBlob) {
            alert(`Please record phrase #${i} first.`);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Next';
            return;
        }

        formData.append(`texts[]`, text);
        formData.append(`user_audio_files[]`, audioBlob, `recording_${i}.wav`);
    }


    // Append auditory answers
    document.querySelectorAll('input[name="auditory_inputs[]"]').forEach(input => {
        formData.append('auditory_inputs[]', input.value.trim());
    });


     // ✅ Append replay counts and response times — THIS WAS MISSING BEFORE
    Object.keys(auditoryReplayCounts).forEach(index => {
        formData.append('auditory_replay_counts[]', auditoryReplayCounts[index]);
    });

    Object.keys(auditoryResponseTimes).forEach(index => {
        formData.append('auditory_response_times[]', auditoryResponseTimes[index]);
    });

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        }
    })
    .then(res => res.json())
    .then(data => {
        data.forEach((item, idx) => {
            alert(`Pronunciation score for phrase #${idx + 1}: ` + (item.text_score?.speechace_score?.pronunciation ?? 'N/A'));
        });

        let redirectUrl = data.redirect_url || '/enrollment/reading-test';
        if (redirectUrl.endsWith('?')) {
            redirectUrl = redirectUrl.slice(0, -1);
        }

        window.location.href = redirectUrl;
    })
    .catch(err => {
        console.error('Error:', err);
        window.location.href = '/enrollment/reading-test';
    });
});

</script>

<script src="https://code.responsivevoice.org/responsivevoice.js"></script>
<script>
let auditoryStartTimes = {};
let auditoryReplayCounts = {};
let auditoryResponseTimes = {};

    
function updateVolumeLevel(index, value) {
    document.getElementById('volume-level-' + index).value = value;
    document.getElementById('volume-display-' + index).innerText = value;
}

 const auditoryTexts = {!! json_encode(array_map(function($ans) {
        return is_array($ans) ? $ans[0] : $ans;
    }, $auditoryAnswers)) !!};

function playAuditory(index) {
    const zeroIndex = index; // adjust for 1-based input id vs zero-based arrays
    const text = auditoryTexts[zeroIndex];
    const volume = parseFloat(document.getElementById('volume-level-' + index).value || '1.0');

    if (!auditoryStartTimes[index]) {
        auditoryStartTimes[index] = Date.now();
    }

    auditoryReplayCounts[index] = (auditoryReplayCounts[index] || 0) + 1;

    responsiveVoice.speak(text, "US English Female", {
        rate: 0.9,
        volume: volume
    });
}


document.querySelectorAll('input[name="auditory_inputs[]"]').forEach((input, idx) => {
    input.addEventListener('input', () => {
        // Record only first time the user interacts
        if (!auditoryResponseTimes[idx + 1]) {
            const reaction = Date.now() - (auditoryStartTimes[idx + 1] || Date.now());
            auditoryResponseTimes[idx + 1] = Math.round(reaction / 1000); // in seconds
        }
    });
});


</script>