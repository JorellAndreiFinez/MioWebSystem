
<section class="home-section">
    <div class="text">Physical Evaluation</div>

    <div class="evaluation-section" style="padding: 2rem; max-width: 800px; margin: auto;">
        <h3>Sentence Structure Test</h3>
        <p class="mb-4">Assessing the ability to construct proper sentences.</p>

        <!-- Speech Test (5 Items) -->
        <form id="speech-auditory-form" action="{{ route('assessment.speechace.submit3') }}" method="POST" enctype="multipart/form-data">
            @csrf
                 <div class="card mb-5">
            <h4>Reading Test</h4>
            <p>Please speak the sentences shown. Your voice will be recorded.</p>

           @foreach ($fillblanks as $index => $item)
            <div class="speech-item">
                <label>
                    <strong>Item {{ $index + 1 }}:</strong>
                    <span id="speech-phrase-{{ $index + 1 }}">{{ $item['text'] ?? '' }}</span>
                    <input type="hidden" name="texts[]" value="{{ $item['full_answer'] ?? '' }}">
                    <div class="choices" style="margin-left: 1rem;">
                        <ul>
                            <li>A. {{ $item['a'] ?? '' }}</li>
                            <li>B. {{ $item['b'] ?? '' }}</li>
                            <li>C. {{ $item['c'] ?? '' }}</li>
                        </ul>
                    </div>
                </label>
                <div class="speech-controls">
                    <div class="buttons">
                        <button type="button" id="start-btn-{{ $index + 1 }}" onclick="startRecording({{ $index + 1 }})" class="btn btn-sm btn-success">Start</button>
                        <button type="button" id="stop-btn-{{ $index + 1 }}" onclick="stopRecording({{ $index + 1 }})" class="btn btn-sm btn-danger" disabled>Stop</button>
                    </div>
                    <div id="waveform-{{ $index + 1 }}" class="waveform" style="width: 100%; height: 80px; margin-top: 10px;"></div>
                    <button type="button" id="play-pause-{{ $index + 1 }}" class="btn btn-sm btn-primary">Play/Pause</button>
                </div>
            </div>
            @endforeach

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

const fillblanksCount = {{ count($fillblanks) }};


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
    for (let i = 1; i <= fillblanksCount; i++) {
        const audioBlob = recordings[i];
       const text = document.getElementsByName('texts[]')[i - 1].value;


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
    })
    .catch(err => {
        console.error('Error:', err);
    });
});




</script>



