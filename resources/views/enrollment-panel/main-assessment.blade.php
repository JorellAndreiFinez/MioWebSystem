@php
$phrases = [
    1 => 'cat',                        // Easy word
    2 => 'bicycle',                   // Medium word
    3 => 'extraordinary',             // Hard word
    4 => 'The sun is bright.',       // Easy sentence
    5 => 'Learning is a lifelong journey.', // Medium sentence
];
@endphp

@php
$auditoryAnswers = [
    1 => 'dog',                    // Easy word
    2 => 'helicopter',            // Medium word
    3 => 'unbelievable',          // Hard word
    4 => 'It is raining.',        // Easy short phrase
    5 => 'She is my friend.',     // Easy short phrase
];
@endphp

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

            @for ($i = 1; $i <= 5; $i++)
            <div class="speech-item">
                <label>
                    <strong>Item {{ $i }}:</strong>
                    <span id="speech-phrase-{{ $i }}">{{ $phrases[$i] }}</span>
                </label>
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

            <!-- Auditory Test (5 Items) -->
            <div class="card mb-5">
                <h4>Auditory Test</h4>
                <p>Listen to the audio and type what you hear.</p>

                @for ($i = 1; $i <= 5; $i++)
                    <div class="mb-3">
                        <label><strong>Audio {{ $i }}:</strong></label>
                        <div>
                           <button type="button" onclick="playAuditory({{ $i }})" class="btn btn-sm btn-info">🔊 Play Audio</button>

                           <input type="text" name="auditory_inputs[]" class="form-control mt-2" placeholder="Type what you heard...">

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

    const formData = new FormData();

    // Append speech texts and audio blobs
    for (let i = 1; i <= 5; i++) {
        const audioBlob = recordings[i];
        const text = document.getElementById('speech-phrase-' + i).textContent;

        formData.append(`texts[]`, text);
        if (audioBlob) {
            formData.append(`user_audio_files[]`, audioBlob, `recording_${i}.wav`);
        } else {
            // No recording for this item, maybe alert or handle accordingly
            alert(`Please record phrase #${i} first.`);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Next';
            return;
        }
    }

    // Append auditory answers
    document.querySelectorAll('input[name="auditory_inputs[]"]').forEach(input => {
        formData.append('auditory_inputs[]', input.value.trim());
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

        let redirectUrl = data.redirect_url || '/enrollment/assessment/reading-test';
        if (redirectUrl.endsWith('?')) {
            redirectUrl = redirectUrl.slice(0, -1);
        }

        window.location.href = redirectUrl;
    })
    .catch(err => {
        console.error('Error:', err);
        window.location.href = '/enrollment/assessment/reading-test';
    });
});




</script>

<script src="https://code.responsivevoice.org/responsivevoice.js"></script>
<script>
    const auditoryTexts = {
        1: "apple",
        2: "helicopter",
        3: "incredible",
        4: "It is raining.",
        5: "She is my friend."
    };

    function playAuditory(index) {
        const text = auditoryTexts[index];
        responsiveVoice.speak(text, "US English Female", { rate: 0.9 });
    }
</script>

