<section class="home-section">
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
            <div class="assignment-card">
                <div class="activity-info">
                    <h3>{{ $assignment['title'] ?? 'Untitled Activity' }}</h3>
                </div>

                <div class="details">
                    <div>
                        <span>Deadline</span>
                        <strong>{{ \Carbon\Carbon::parse($assignment['deadline'])->format('F j, Y') }}</strong>
                    </div>
                    <div>
                        <span>Availability</span>
                        <strong>
                           {{ \Carbon\Carbon::parse($assignment['deadline'] . ' ' . ($assignment['availability']['start'] ?? '00:00'))->format('F j, Y g:i A') }}
-
                            {{ \Carbon\Carbon::parse($assignment['deadline'] . ' ' . ($assignment['availability']['end'] ?? '00:00'))->format('g:i A') }}

                        </strong>
                    </div>
                    <div>
                        <span>Points</span>
                        <strong>{{ $assignment['points']['earned'] ?? '0' }} / {{ $assignment['points']['total'] ?? '0' }}</strong>

                    </div>
                    <div>
                        <span>Attempts</span>
                        <strong>{{ $assignment['attempts'] ?? '1' }}</strong>
                    </div>
                </div>
            </div>

         <div class="submission-review-container">
                <button id="openReviewModal" class="btn-review-submissions">
                    <i class="fas fa-eye"></i> Review Submissions
                </button>
            </div>

    </main>

    <!-- Edit Assignment Modal -->
   <div id="addAssignmentModal" class="modal assignment-modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Edit Assignment</h2>
        <form action="{{ route('mio.subject-teacher.addAssignment', ['subjectId' => request()->route('subjectId')]) }}" method="POST">
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

            <label for="deadline">Deadline</label>
            <input type="date" name="deadline" id="deadline"  required>

            <label for="availability_start">Availability - Start Time</label>
            <input type="time" name="availability_start" id="availability_start" required>

            <label for="availability_end">Availability - End Time</label>
            <input type="time" name="availability_end" id="availability_end" required>

            <label for="points_earned">Points</label>
            <div style="display: flex; align-items: center; gap: 5px;">
                <input type="number" name="points_earned" id="points_earned" min="0" required style="width: 60px;" class="no-spinner">
                <span>/</span>
                <input type="number" name="points_total" id="points_total" min="1" required style="width: 60px;" class="no-spinner">
            </div>

            <label for="attempts">Attempts</label>
            <input type="number" name="attempts" min="1" required class="no-spinner">

            <button type="submit">Save Assignment</button>
        </form>
    </div>
    </div>

    <!-- Teacher Review Modal -->
   <div id="reviewModal" class="modal" style="display: none;">
    <div class="modal-content modal-styled">
        <span class="close" id="closeReviewModal">&times;</span>
        <h2>Review Assignment: {{ $assignment['title'] }}</h2>
        <div class="review-content">
        <h4>Student's Work Review</h4>

        <!-- Student Selector -->
        <label for="studentSelector">Select Student</label>
        <select id="studentSelector" name="student_id" style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%;">
            @if (!empty($assignment['people']))
                @foreach ($assignment['people'] as $studentId => $student)
                    <option value="{{ $studentId }}">
                        {{ $student['name'] ?? ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '') }}
                    </option>
                @endforeach
            @else
                <option disabled selected>No students assigned</option>
            @endif
        </select>

        <!-- Submissions Preview Area -->
        <div id="studentWorkPreview" style="margin-top: 20px;">
            @if($submission)
                <!-- Display the student's submission -->
                <p>{{ $submission->content }}</p>
            @else
                @empty($submission)
                    <!-- Placeholder for empty submission -->
                    <p>No submission yet.</p>
                @endempty
            @endif
        </div>


        <!-- Feedback Textarea -->
        <label for="feedback">Feedback</label>
        <textarea name="feedback" rows="4" placeholder="Enter your feedback" required></textarea>
    </div>

    </div>
</div>


</section>

<!-- MODAL -->
<script>

     function getTodayDateString() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    window.onload = function () {
        const today = getTodayDateString();
        document.getElementById("deadline").min = today;
        document.getElementById("availability_date").min = today;
    };


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

            <input type="file" name="attachments[${index}][file]" accept="image/*" style="display: none;" />
            <button type="button" class="upload-btn">Choose Image</button>

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
});
</script>


<script>
    // Modal functionality
    const reviewModal = document.getElementById("reviewModal");
    const openReviewModalBtn = document.getElementById("openReviewModal");
    const closeReviewModalBtn = document.getElementById("closeReviewModal");


    openReviewModalBtn.onclick = function () {
        reviewModal.style.display = "block";
    };

    closeReviewModalBtn.onclick = function () {
        reviewModal.style.display = "none";
    };

    window.onclick = function (event) {
        if (event.target === reviewModal) {
            reviewModal.style.display = "none";
        }
    };
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const studentSelector = document.getElementById('studentSelector');
    const prevStudentBtn = document.getElementById('prevStudent');
    const nextStudentBtn = document.getElementById('nextStudent');
    const studentWorkPreview = document.getElementById('studentWorkPreview');

    let students = [];
    let currentIndex = 0;

    // Load students from dropdown into array
    for (let i = 0; i < studentSelector.options.length; i++) {
        const option = studentSelector.options[i];
        students.push({
            id: option.value,
            name: option.textContent,
            work: option.getAttribute('data-work'),
        });
    }

    // Display selected student's work
    function updatePreview(index) {
        if (index >= 0 && index < students.length) {
            currentIndex = index;
            studentSelector.selectedIndex = index;
            const selectedStudent = students[index];

            // You can customize how the preview is shown based on the type of submission
            if (selectedStudent.work) {
                const fileExtension = selectedStudent.work.split('.').pop().toLowerCase();

                if (['png', 'jpg', 'jpeg', 'gif'].includes(fileExtension)) {
                    studentWorkPreview.innerHTML = `<img src="${selectedStudent.work}" alt="Submission Image" class="w-full max-h-96 object-contain rounded border">`;
                } else if (['pdf'].includes(fileExtension)) {
                    studentWorkPreview.innerHTML = `<iframe src="${selectedStudent.work}" class="w-full h-96 rounded border" frameborder="0"></iframe>`;
                } else {
                    studentWorkPreview.innerHTML = `<a href="${selectedStudent.work}" target="_blank" class="text-blue-600 underline">View Submission</a>`;
                }
            } else {
                studentWorkPreview.innerHTML = `<p class="text-gray-500 italic">No submission from this student.</p>`;
            }
        }
    }

    // Dropdown change
    studentSelector.addEventListener('change', function () {
        const selectedId = this.value;
        const newIndex = students.findIndex(student => student.id === selectedId);
        if (newIndex !== -1) updatePreview(newIndex);
    });

    // Navigation buttons
    prevStudentBtn.addEventListener('click', function () {
        if (currentIndex > 0) updatePreview(currentIndex - 1);
    });

    nextStudentBtn.addEventListener('click', function () {
        if (currentIndex < students.length - 1) updatePreview(currentIndex + 1);
    });

    // Initialize on first load
    updatePreview(0);
});

</script>
