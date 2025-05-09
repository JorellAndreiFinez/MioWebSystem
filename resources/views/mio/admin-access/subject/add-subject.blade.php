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
                <!-- Subject Information -->
                <div class="section-header">Subject Details</div>
                <div class="section-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject ID <span style="color: red">*</span></label>
                            <input type="text" name="subject_id" id="subjectID" placeholder="Enter Subject ID" required />
                        </div>
                        <div class="form-group">
                            <label>Subject Code <span style="color: red">*</span></label>
                            <input type="text" name="code" placeholder="Enter Subject Code" required />
                        </div>
                        <div class="form-group">
                            <label>Subject Title <span style="color: red">*</span></label>
                            <input type="text" name="title" placeholder="Enter Subject Title" required />
                        </div>
                    </div>

                    <div class="form-row">
                    <div class="form-group wide">
                        <label>Teacher ID <small>(If the admin is also a teacher)</small> </label>
                        <select name="teacher_id" id="teacherID">
                                <option value="" selected>Select a Teacher</option>
                                @foreach($teachers as $teacher)
                                <option value="{{ $teacher['teacherid'] }}">{{ $teacher['name'] }}</option>
                                @endforeach
                            </select>
                    </div>

                        <div class="form-group">
                            <label>Section ID <span style="color: red">*</span></label>

                            <select name="section_id" id="sectionID">
                                @foreach ($sections as $section)
                                <option value="">Select a Section</option>
                                    <option value="{{ $section['sectionid'] }}">
                                        {{ $section['name'] }} ({{ $section['status'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Module Section -->
                <div class="section-header">Modules</div>
                    <div class="section-content" id="module-section">
                        <!-- Initial Module Block (Module 1) -->
                        <div class="form-row module-row" data-index="0">
                        <div class="form-group">
                            <label>Module Title <span style="color: red">*</span></label>
                            <input type="text" name="modules[0][title]" placeholder="e.g. Module 1: Introduction" required />
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="modules[0][description]" placeholder="Optional module description"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Upload File</label>
                            <input type="file" name="modules[0][file]" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip" />
                        </div>
                    </div>

                        <!-- Add Module Button -->
                        <button type="button" class="btn add-btn" onclick="addModuleField()">+ Add Module</button>
                    </div>


        </div>
</form>
</div>

</section>


<!-- ADD MODULE SECTION -->
<script>
let moduleCount = 1; // Start from 1 since Module 0 is already added

function addModuleField() {
    const section = document.getElementById("module-section");

    // Create module input row
    const moduleRow = document.createElement("div");
    moduleRow.className = "form-row module-row";
    moduleRow.dataset.index = moduleCount;

    moduleRow.innerHTML = `
        <div class="form-group">
            <label>Module Title <span style="color: red">*</span></label>
            <input type="text" name="modules[${moduleCount}][title]" placeholder="e.g. Module ${moduleCount + 1}" required />
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="modules[${moduleCount}][description]" placeholder="Optional module description"></textarea>
        </div>
    `;

    // Create separate remove button row
    const removeRow = document.createElement("div");
    removeRow.className = "form-row remove-row";
    removeRow.dataset.index = moduleCount;

    removeRow.innerHTML = `
        <div class="form-group" style="align-self: end;">
            <button type="button" class="btn cancel-btn" onclick="removeModuleField(this)">Remove</button>
        </div>
    `;

    // Insert before the "Add Module" button
    section.insertBefore(moduleRow, section.querySelector(".add-btn"));
    section.insertBefore(removeRow, section.querySelector(".add-btn"));

    moduleCount++;
}

function removeModuleField(button) {
    const removeRow = button.closest('.remove-row');
    const index = removeRow.dataset.index;

    const inputRow = document.querySelector(`.module-row[data-index="${index}"]`);
    if (inputRow) inputRow.remove();
    if (removeRow) removeRow.remove();

    moduleCount--;
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




