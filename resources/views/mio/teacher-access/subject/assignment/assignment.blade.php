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
                <h3>Activity 1</h3>
            </div>

            <div class="details">
                <div>
                    <span>Deadline</span>
                    <strong>January 10, 2025</strong>
                </div>
                <div>
                    <span>Availability</span>
                    <strong>January 10, 2025 9:00 AM - 10:00 AM</strong>
                </div>
                <div>
                    <span>Points</span>
                    <strong>10</strong>
                </div>
                <div>
                    <span>Attempts</span>
                    <strong>1</strong>
                </div>
            </div>

            <a href="#" class="take-quiz-btn">View Assignment</a>
        </div>

        <div class="assignment-card">
             <div class="add-assignment-container">
                <a href="#" class="add-assignment-btn" id="openModal">+ Add Assignment</a>

            </div>
        </div>

    </main>

    <!-- Add Assignment Modal -->
   <div id="addAssignmentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Add Assignment</h2>
        <form action="{{ route('mio.subject-teacher.addAssignment', ['subjectId' => request()->route('subjectId')]) }}" method="POST">
            @csrf

            <label for="title">Title</label>
            <input type="text" placeholder="Assignment Title" name="title" required>

            <label for="description">Description</label>
            <textarea name="description"  placeholder="Write a brief description..." rows="3"  style="width: 100%; height: 120px; resize: none;"></textarea>

            <label for="media_input">Media (URL or Image Upload)</label>
            <div id="media-dropzone" style="border: 2px dashed #ccc; padding: 15px; border-radius: 5px; text-align: center; cursor: pointer;">
                <p id="media-dropzone-text">Paste a link or drag & drop an image here</p>
                <input type="url" name="media_link" id="media_link" placeholder="https://example.com" style="width: 100%; margin-top: 10px;" />
                <input type="file" name="media_file" id="media_file" accept="image/*" style="display: none;" />
                <img id="preview_image" src="" alt="Image Preview" style="max-width: 100%; margin-top: 10px; display: none;" />
            </div>


            <label for="deadline">Deadline</label>
            <input type="date" name="deadline" id="deadline" required>

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

</section>

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

