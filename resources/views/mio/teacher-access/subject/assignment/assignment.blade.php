<section class="home-section">
<main class="main-banner">
            <div class="welcome-banner">
            <div class="banner">
            <div class="content">
            <h5>Available assignments</h5>

            </div>

            </div>
            </div>
        </main>

    <main class="main-assignment-content">
        @foreach ($assignments as $assignment)
            <div class="assignment-card">
                <div class="activity-info">
                    <h3>{{ $assignment['title'] ?? 'Untitled Activity' }}</h3>
                </div>

                <div class="details">
                    <div>
                        <span>Publish at</span>
                        <strong>
                            {{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($assignment['published_at'])->format('Y-m-d') . ' ' . ($assignment['availability']['start'] ?? '00:00'))->format('F j, Y g:i A') }}
                        </strong>
                    </div>
                    <div>
                        <span>Deadline</span>
                        <strong>
                            @if (!empty($assignment['deadline']))
                                {{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($assignment['deadline'])->format('Y-m-d') . ' ' . ($assignment['availability']['end'] ?? '00:00'))->format('F j, Y g:i A') }}
                            @else
                                No Due Date
                            @endif
                        </strong>
                    </div>

                        <div>
                            <span>Points</span>
                            <strong>{{ $assignment['total'] ?? '0' }}</strong>
                        </div>
                    <div>
                        <span>Attempt/s</span>
                        <strong>{{ $assignment['attempts'] ?? '1' }}</strong>
                    </div>
                </div>

               <a href="{{ route('mio.subject-teacher.assignment-body', ['subjectId' => $subjectId, 'assignmentId' => $assignment['id']]) }}" class="take-quiz-btn">View Assignment</a>

                <!-- Trash Icon Button -->
            <form action="{{ route('mio.subject-teacher.deleteAssignment', ['subjectId' => $subjectId, 'assignmentId' => $assignment['id']]) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="delete-btn" style="background: none; border: none; cursor: pointer;">
                    <i class="fas fa-trash-alt" style="color: red; font-size: 20px;"></i>  <!-- Trash icon -->
                </button>
            </form>

            </div>
        @endforeach

         <div class="assignment-card">
             <div class="add-assignment-container">
                <a href="#" class="add-assignment-btn" id="openModal">+ Add Assignment</a>
            </div>
        </div>

    </main>

    <!-- Add Assignment Modal -->
   <div id="addAssignmentModal" class="modal assignment-modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Add Assignment</h2>
        <form action="{{ route('mio.subject-teacher.addAssignment', ['subjectId' => request()->route('subjectId')]) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <label for="title">Title</label>
            <input type="text" placeholder="Assignment Title" name="title" value="Test 1" required>

            <label for="description">Description</label>
            <textarea name="description"  placeholder="Write a brief description..." rows="3"  style="width: 100%; height: 120px; resize: none;" >Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nesciunt voluptatum necessitatibus maiores sit sint corrupti quod accusantium tempora iure excepturi, facilis et veniam temporibus adipisci unde laboriosam neque commodi incidunt.</textarea>

            <label>Attachments</label>
            <div id="attachment-container">
                <!-- Dynamic attachment inputs will appear here -->
            </div>
            <button type="button" id="add-attachment-btn" style="margin-top: 10px;">+ Add File or Link</button>

            <label for="publish_date">Publish Date</label>
            <input type="date" name="publish_date" id="publish_date" required>


            <label for="availability_start">Open at - Start Time</label>
            <input type="time" name="availability_start" id="availability_start" required>

             <label for="deadline">Deadline [Blank - No Due Date]</label>
            <input type="date" name="deadline" id="deadline" >

            <label for="availability_end">Deadline - End Time</label>
            <input type="time" name="availability_end" id="availability_end">

            <label for="points_total">Total Points</label>
            <input type="number" name="points_total" id="points_total" min="1" required class="no-spinner">


            <label for="attempts">Attempts</label>
            <input type="number" name="attempts" min="1" required class="no-spinner">

            <button type="submit">Save Assignment</button>
        </form>
    </div>
</div>

</section>

<!-- MODAL -->
<script>

    document.getElementById("openModal").onclick = function () {
    document.getElementById("addAssignmentModal").style.display = "block";
    };

    document.getElementById("closeModal").onclick = function () {
        document.getElementById("addAssignmentModal").style.display = "none";
    };

    window.onclick = function (event) {
        if (event.target.classList.contains("modal")) {
            document.getElementById("addAssignmentModal").style.display = "none";
        }
    };


</script>

<script>
    document.getElementById('media-dropzone').addEventListener('click', () => {
        document.getElementById('media_file').click();
    });

    document.getElementById('media_file').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview_image').src = e.target.result;
                document.getElementById('preview_image').style.display = 'block';
                document.getElementById('media_link').value = ''; // Clear URL if image is selected
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('media_link').addEventListener('input', function () {
        if (this.value.trim() !== '') {
            document.getElementById('media_file').value = ''; // Clear file if URL is typed
            document.getElementById('preview_image').style.display = 'none';
        }
    });

    // Optional: drag & drop support
    const dropzone = document.getElementById('media-dropzone');
    dropzone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropzone.style.borderColor = '#3498db';
    });
    dropzone.addEventListener('dragleave', function () {
        dropzone.style.borderColor = '#ccc';
    });
    dropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropzone.style.borderColor = '#ccc';
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview_image').src = e.target.result;
                document.getElementById('preview_image').style.display = 'block';
                document.getElementById('media_link').value = ''; // Clear URL if image is dropped
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<script>
let attachmentIndex = 0;

document.getElementById('add-attachment-btn').addEventListener('click', function () {
    const container = document.getElementById('attachment-container');
    const index = attachmentIndex++;

    const wrapper = document.createElement('div');
    wrapper.classList.add('attachment-wrapper');
    wrapper.style.marginBottom = '15px';

    wrapper.innerHTML = `
        <div style="border: 2px dashed #ccc; padding: 10px; border-radius: 5px; text-align: center; position: relative;">
            <input type="url" name="attachments[${index}][link]" placeholder="Paste a media URL (optional)" style="width: 100%; margin-bottom: 10px;" />

           <input type="file" name="attachments[${index}][file]" accept=".jpg,.jpeg,.png,.gif,.mp4,.mov,.avi,.mp3,.wav,.webm,.pdf" style="display: none;" />
            <button type="button" class="upload-btn">Choose File</button>
            <span class="file-name" style="display: inline-block; margin-top: 5px; color: #555;"></span>

            <img src="" alt="Preview" class="preview" style="max-width: 100%; margin-top: 10px; display: none;" />

            <button type="button" class="remove-attachment" style="position: absolute; top: 5px; right: 5px;">🗑️</button>
        </div>
    `;

    container.appendChild(wrapper);

    const fileInput = wrapper.querySelector('input[type="file"]');
    const urlInput = wrapper.querySelector('input[type="url"]');
    const previewImg = wrapper.querySelector('.preview');

    wrapper.querySelector('.upload-btn').addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                urlInput.value = '';
            };
            reader.readAsDataURL(file);
        }
    });

    urlInput.addEventListener('input', () => {
        if (urlInput.value.trim()) {
            fileInput.value = '';
            previewImg.style.display = 'none';
        }
    });

    wrapper.querySelector('.remove-attachment').addEventListener('click', () => {
        wrapper.remove();
    });

    fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        const fileNameSpan = document.createElement('span');
        fileNameSpan.textContent = file.name;
        fileNameSpan.style.display = 'inline-block';
        fileNameSpan.style.marginTop = '10px';
        fileNameSpan.style.fontWeight = 'bold';

        // Remove old name if exists
        const oldName = wrapper.querySelector('.file-name');
        if (oldName) oldName.remove();

        fileNameSpan.classList.add('file-name');
        wrapper.appendChild(fileNameSpan);

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                urlInput.value = '';
            };
            reader.readAsDataURL(file);
        } else {
            previewImg.style.display = 'none';
        }
    }
});

});


</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const today = new Date().toISOString().split('T')[0];
        const publishDateInput = document.getElementById('publish_date');
        const deadlineInput = document.getElementById('deadline');

        if (publishDateInput) {
            publishDateInput.setAttribute('min', today);
            publishDateInput.value = today;
        }

        if (deadlineInput) {
            deadlineInput.setAttribute('min', today);
        }
    });
</script>


