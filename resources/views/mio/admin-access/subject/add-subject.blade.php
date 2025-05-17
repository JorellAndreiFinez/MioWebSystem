<section class="home-section">
<div class="text">Add New Subject</div>
<div class="teacher-container">

<form action="{{ route('mio.StoreSubject', ['grade' => $grade]) }}" method="POST" enctype="multipart/form-data">

            @csrf

    <div class="table-header">
        <div class="search-container" style="background: transparent;">
        </div>

        <div class="button-group">
        <button type="button" class="btn cancel-btn"><a href="{{ url()->previous() }}">Cancel</a>
        </button>
        <button class="btn add-btn">
                <span class="icon">+</span> New Subject
        </button>
        </div>
    </div>

    <div class="form-container">
        <!-- Subject Details -->
        <div class="section-header">Subject Details</div>
        <div class="section-content">
            <div class="form-row">
                <div class="form-group">
                    <label>Subject ID <span style="color: red">*</span></label>
                    <input type="text" name="subject_id" id="subjectID" placeholder="Enter Subject ID" required  />
                </div>
                <div class="form-group">
                    <label>Subject Code <span style="color: red">*</span></label>
                    <input type="text" name="code" value="SA0001" placeholder="Enter Subject Code" required />
                </div>
                <div class="form-group">
                    <label>Subject Title <span style="color: red">*</span></label>
                    <input type="text" name="title" value="SAMPLE1" placeholder="Enter Subject Title" required />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group wide">
                    <label>Teacher ID</label>
                    <select name="teacher_id" id="teacherID">
                        <option value="">Select a Teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher['teacherid'] }}">{{ $teacher['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Section ID <span style="color: red">*</span></label>
                    <select name="section_id" id="sectionID">
                        <option value="">Select a Section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section['sectionid'] }}">
                                {{ $section['name'] }} ({{ $section['status'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Subject Type <span style="color: red">*</span></label>
                    <select name="subjectType" required>
                        <option value="">Select Subject Type</option>
                        <option value="academics">Academics</option>
                        <option value="specialized">Specialized</option>
                    </select>
                </div>

            </div>
        </div>

        <!-- Modules Section -->
        <div class="section-header">Modules</div>
        <div class="section-content" id="module-section">
            <!-- Initial Module Block -->
            <div class="form-row module-row" data-index="0">
                <div class="form-group">
                    <label>Module Title <span style="color: red">*</span></label>
                    <input type="text" name="modules[0][title]" placeholder="e.g. Module 1: Introduction" required />
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="modules[0][description]" placeholder="Optional module description"></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group custom-file-upload">
                    <label>Upload File</label>
                    <label for="file-upload-0" class="file-label">
                        <span class="upload-icon">📁</span> Choose File
                    </label>
                    <input type="file" name="modules[0][file]" id="file-upload-0" class="file-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip" required/>
                    <span class="file-name" id="file-name-0">No file chosen</span>
                </div>
            </div>

            <!-- Add Module Button -->
            <button type="button" class="btn add-btn" onclick="addModuleField()">+ Add Module</button>
        </div>

        <!-- Announcement Section -->
        <div class="section-header">Announcement</div>
        <div class="section-content">
            <div class="form-row">
                <div class="form-group wide">
                    <label>Announcement Title</label>
                    <input type="text" name="announcement[title]" placeholder="Enter Announcement Title" value="Welcome Students!"/>
                </div>

                <div class="form-group">
                    <label>Publish Date</label>
                    <input type="date" name="announcement[date]" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group wide">
                    <label>Announcement Description</label>
                    <textarea name="announcement[description]" placeholder="Enter details about the announcement..."></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Upload Image/Video (Optional)</label>
                    <input type="file" name="announcement[file]" accept="image/*,video/*" />
                </div>
                <div class="form-group">
                    <label>Or External Link (Image/Video)</label>
                    <input type="url" name="announcement[link]" placeholder="https://example.com/image.jpg or video.mp4" />
                </div>
            </div>

        </div>

    </div>
</form>
</div>
</section>



<!-- ADD MODULE SECTION -->
<script>
let moduleCount = 1;

// Function to add a new module field
function addModuleField() {
    const section = document.getElementById("module-section");

    // Wrapper for all fields in a module
    const wrapper = document.createElement("div");
    wrapper.className = "module-block";
    wrapper.dataset.index = moduleCount;

    wrapper.innerHTML = `
        <div class="form-row">
            <div class="form-group">
                <label>Module Title <span style="color: red">*</span></label>
                <input type="text" name="modules[${moduleCount}][title]" placeholder="e.g. Module ${moduleCount + 1}" required />
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="modules[${moduleCount}][description]" placeholder="Optional module description"></textarea>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group custom-file-upload">
                <label>Upload File</label>
                <label for="file-upload-${moduleCount}" class="file-label">
                    <span class="upload-icon">📁</span> Choose File
                </label>
                <input type="file" name="modules[${moduleCount}][file]" id="file-upload-${moduleCount}" class="file-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip" />
                <span class="file-name" id="file-name-${moduleCount}">No file chosen</span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <button type="button" class="btn cancel-btn" onclick="removeModuleField(this)">Remove</button>
            </div>
        </div>
    `;

    section.insertBefore(wrapper, section.querySelector(".add-btn"));

    // Add listener for file name change for the newly added file input
    const fileInput = wrapper.querySelector(`#file-upload-${moduleCount}`);
    fileInput.addEventListener('change', function () {
        const fileName = this.files.length ? this.files[0].name : 'No file chosen';
        document.getElementById(`file-name-${moduleCount}`).textContent = fileName;
    });

    moduleCount++;
}


// Function to remove a module field
function removeModuleField(button) {
    const moduleBlock = button.closest('.module-block');
    moduleBlock.remove();
}

</script>



<!-- GENERATE SUBJECT ID -->
<script>
// Get the current date
const currentDate = new Date();

// Get the current year
const currentYear = currentDate.getFullYear();

// Get the current week number (ISO-8601 standard)
const getWeekNumber = (date) => {
  const firstDayOfYear = new Date(date.getFullYear(), 0, 1);
  const days = Math.floor((date - firstDayOfYear) / (24 * 60 * 60 * 1000));
  return Math.ceil((days + 1) / 7);
};
const currentWeek = getWeekNumber(currentDate);

// Get the current day of the week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
const currentDay = currentDate.getDay();

// Randomize last 3 digits (000–999)
const randomLastThreeDigits = Math.floor(Math.random() * 1000);

// Format the last 3 digits to always be 3 digits long (e.g., 001, 087, 999)
const lastThreeDigits = String(randomLastThreeDigits).padStart(3, '0');

// Generate the student ID with the current year, week, day, and random last 3 digits
const studentID = `SU${currentYear}${String(currentWeek).padStart(2, '0')}${String(currentDay)}${lastThreeDigits}`;

// Set the value in the input field
document.getElementById('subjectID').value = studentID;
</script>

<!-- TEACHER ID -->
<script>
document.getElementById('teacherID').addEventListener('blur', function () {
    const teacherID = this.value.trim();
    if (teacherID === '') {
        document.getElementById('teacherNameDisplay').value = '';
        return;
    }

    fetch(`/mio/admin/get-teacher/${teacherID}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Teacher not found');
            }
            return response.json();
        })
        .then(data => {
            const fullName = (data.first_name || '') + ' ' + (data.last_name || '');
            document.getElementById('teacherNameDisplay').value = fullName.trim() || 'No name available';
        })
        .catch(error => {
            document.getElementById('teacherNameDisplay').value = 'Not found';
            console.error(error);
        });
});
</script>

<!-- TEACHER ID -->
<script>
document.getElementById('sectionID').addEventListener('blur', function () {
    const teacherID = this.value.trim();

    fetch(`/mio/admin/get-section/${sectionID}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Section not found');
            }
            return response.json();
        })
        .then(data => {
            const fullName = (data.section_name || '');
            document.getElementById('teacherNameDisplay').value = fullName.trim() || 'No name available';
        })
        .catch(error => {
            document.getElementById('teacherNameDisplay').value = 'Not found';
            console.error(error);
        });
});
</script>

<!-- FILE DISPLAY NAME -->
<script>
document.getElementById('file-upload-0').addEventListener('change', function () {
    const fileName = this.files.length ? this.files[0].name : 'No file chosen';
    document.getElementById('file-name-0').textContent = fileName;
});
</script>


