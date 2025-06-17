<section class="home-section">
    <div class="text">
    
        <div class="breadcrumb-item active">Assignments</div>

    </div>
<main class="main-banner">
            <div class="welcome-banner">
            <div class="banner">
            <div class="content">
            <h5>Assignments</h5>

            </div>

            </div>
            </div>
        </main>

    <main class="main-assignment-content">
        @if (is_array($assignments) || is_object($assignments))
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
        @else
                <p>No assignments found.</p>

        @endif

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

            <label for="submission_type">Submission Type</label>
            <select name="submission_type" id="submission_type" required>
                <option value="file">File Upload</option>
                <option value="text">Text Entry</option>
            </select>
            
            <div id="file-type-requirements" style="margin: 10px 0; padding: 12px; border: 2px dashed #ccc; border-radius: 6px; display: none;">
                <label style="font-weight: bold; margin-bottom: 8px; display: inline-block;">Allowed File Types:</label>

                <!-- Select All Emoji Button -->
                <div style="margin-bottom: 8px;">
                    <span id="select-all-filetypes" class="file-icon" title="Select All">✅</span> <small>Select All</small>
                </div>

                <!-- Emoji File Icons -->
                <div id="file-type-checkboxes" style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 5px;">
                    <div class="file-icon-wrapper" data-type="pdf"><span>📄</span><div class="file-label">PDF</div></div>
                    <div class="file-icon-wrapper" data-type="docx"><span>📝</span><div class="file-label">DOCX</div></div>
                    <div class="file-icon-wrapper" data-type="pptx"><span>📊</span><div class="file-label">PPTX</div></div>
                    <div class="file-icon-wrapper" data-type="mp3"><span>🎵</span><div class="file-label">MP3</div></div>
                    <div class="file-icon-wrapper" data-type="mp4"><span>🎞️</span><div class="file-label">MP4</div></div>
                    <div class="file-icon-wrapper" data-type="jpg"><span>🖼️</span><div class="file-label">JPG</div></div>
                    <div class="file-icon-wrapper" data-type="png"><span>🧊</span><div class="file-label">PNG</div></div>
                    <div class="file-icon-wrapper" data-type="xlsx"><span>📈</span><div class="file-label">XLSX</div></div>
                    <div class="file-icon-wrapper" data-type="txt"><span>📘</span><div class="file-label">TXT</div></div>
                    <div class="file-icon-wrapper" data-type="zip"><span>🗜️</span><div class="file-label">ZIP</div></div>
                </div>

                <!-- Hidden inputs container -->
                <div id="file-type-hidden-inputs"></div>

                <div style="margin-top: 15px;">
                    <label style="font-weight: bold;" for="max_file_size">Maximum File Size (MB):</label>
                    <input type="number" name="max_file_size" id="max_file_size" min="1" max="100" step="1" style="width: 100px; margin-left: 10px;">
                </div>
            </div>







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


<script>
document.addEventListener('DOMContentLoaded', function () {
    const submissionType = document.getElementById('submission_type');
    const attachmentContainer = document.getElementById('attachment-container');
    const addAttachmentBtn = document.getElementById('add-attachment-btn');
    const fileTypeRequirements = document.getElementById('file-type-requirements');

    function toggleFileUploadUI() {
        const isFile = submissionType.value === 'file';
        attachmentContainer.style.display = isFile ? 'block' : 'none';
        addAttachmentBtn.style.display = isFile ? 'inline-block' : 'none';
        fileTypeRequirements.style.display = isFile ? 'block' : 'none';
    }

    submissionType.addEventListener('change', toggleFileUploadUI);

    // Initialize on page load
    toggleFileUploadUI();
});

document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('select-all-filetypes');
    const fileTypeCheckboxes = document.querySelectorAll('#file-type-checkboxes input[type="checkbox"]');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            fileTypeCheckboxes.forEach(cb => cb.checked = isChecked);
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrappers = document.querySelectorAll('.file-icon-wrapper');
    const hiddenInputsContainer = document.getElementById('file-type-hidden-inputs');

    function updateHiddenInputs() {
        hiddenInputsContainer.innerHTML = ''; // Clear
        wrappers.forEach(wrap => {
            if (wrap.classList.contains('selected')) {
                const type = wrap.getAttribute('data-type');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'allowed_file_types[]';
                input.value = type;
                hiddenInputsContainer.appendChild(input);
            }
        });
    }

    wrappers.forEach(wrap => {
        wrap.addEventListener('click', () => {
            wrap.classList.toggle('selected');
            updateHiddenInputs();
        });
    });
});
</script>



<style>
    .file-icon-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        padding: 6px;
        border: 2px solid transparent;
        border-radius: 8px;
        transition: border 0.2s;
        color: white;
    }

    .file-icon-wrapper.selected {
        border-color: gold;
        background-color: #fffbe0;
    }

    .file-icon-wrapper:hover {
        background-color: #f7f7f7;
    }

    .file-icon-wrapper span {
        font-size: 28px;
    }

    .file-label {
        font-size: 12px;
        font-weight: 500;
        color: #333;
    }
</style>