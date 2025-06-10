<section class="home-section">
  <div class="text">Add New Schedule</div>

  <div class="teacher-container">
    @include('mio.dashboard.status-message')

    <form action="{{ route('mio.StoreSchedule') }}" method="POST">
      @csrf

      <!-- HEADER CONTROLS -->
      <div class="table-header">
        <div class="search-container" style="background: transparent;"></div>
        <div class="button-group">
          <button type="button" class="btn cancel-btn"><a href="{{ route("mio.ViewSchedule") }}">Cancel</a></button>
          <button type="submit" class="btn add-btn">
            <span class="icon">+</span> Add Schedule
          </button>
        </div>
      </div>

      <div class="form-container">
        <!-- Schedule Information -->
        <div class="section-header">Schedule Information</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-group">
              <label>Schedule ID <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="scheduleid" id="scheduleID" required />
            </div>
            <div class="form-group">
              <label>Schedule Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="schedule_name" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Schedule Code <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="schedule_code" required />
            </div>

            <div class="form-group">
              <label>Schedule Type <span style="color: red; font-weight:700">*</span></label>
              <select name="schedule_type" required>
                <option value="" disabled selected>Select Type</option>
                <option value="academic">Academics</option>
                <option value="specialized">Specialized</option>
                <option value="admin_support">Administrative and Support</option>
              </select>
            </div>

            <div class="form-group">
              <label>Status <span style="color: red; font-weight:700">*</span></label>
              <select name="status" required>
                <option value="" disabled selected>Select Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group" style="flex: 1;">
              <label>Description</label>
              <textarea name="description" rows="3" placeholder="Describe the schedule's purpose or scope..." style="resize: none; height: 100px;"></textarea>
            </div>
          </div>

          <hr>

          <div class="form-row">

            <div class="form-group">
            <label>Start Time <span style="color: red; font-weight:700">*</span></label>
            <input type="time" name="start_time" required />
            </div>

            <div class="form-group">
            <label>End Time <span style="color: red; font-weight:700">*</span></label>
            <input type="time" name="end_time" required />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex: 1;">
            <label>Occurrences (Days of Week) <span style="color: red; font-weight:700">*</span></label>
            <select name="occurrences[]" multiple required style="height: 170px;">
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
            </select>
            <small>Hold Ctrl (or Cmd) to select multiple days.</small>
            </div>

        </div>
        </div>

        <!-- Teacher Assignment -->
         <div class="section-header">
            Assign to Teacher
         </div>
         <div class="section-content">
             <!-- Teacher Assignment -->
          <div class="form-row">
            <div class="form-group" style="flex: 1;">
              <label>Head Teacher <span style="color: red; font-weight:700">*</span></label>
              <select name="teacherid" required id="select-teacher" class="schedule-selector">
                <option value="" selected>Select a Teacher</option>
                @foreach($teachers as $teacher)
                  <option value="{{ $teacher['teacherid'] }}">
                    {{ $teacher['name'] }} ({{ $teacher['schedulename'] ?? 'No Schedule' }})
                  </option>
                @endforeach
              </select>
            </div>
          </div>
         </div>

        <!-- Section Assignment -->
          <div class="section-header">Assign to Section</div>
          <div class="section-content">
            <div class="form-row">
            <div class="form-group" style="flex: 1;">
              <label>Select Section</label>
              <select name="section_id" id="select-section" class="schedule-selector">
                <option value="" selected>Select Section</option>
                @foreach($sections as $section)
                  <option value="{{ $section['id'] }}">{{ $section['name'] }} ({{ $section['level'] }})</option>
                @endforeach
              </select>
              <small>Optional: Assign this schedule to all students under a section.</small>
            </div>
          </div>
          </div>

          <!-- One-on-One Therapy Students -->
          <div class="section-header">Assign to One-on-One Students</div>
          <div class="section-content">
            <div class="form-row">
            <div class="form-group" style="flex: 1;">
              <label>Select Students</label>
              <select name="student_ids[]" id="select-students" class="schedule-selector" multiple >
                @foreach($students as $student)
                  <option value="{{ $student['id'] }}">
                    {{ $student['name'] }} ({{ $student['grade'] ?? 'N/A' }})
                  </option>
                @endforeach
              </select>
              <small>Hold Ctrl (or Cmd) to select multiple students for 1-on-1 therapy.</small>
            </div>
          </div>
          </div>

      </div>
    </form>
  </div>
</section>


<!-- SCRIPTS -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    const teacherSelect = document.getElementById('select-teacher');
    const sectionSelect = document.getElementById('select-section');
    const studentSelect = document.getElementById('select-students');

    // When section is selected, just reset student
    sectionSelect.addEventListener('change', () => {
        if (sectionSelect.value !== "") {
            Array.from(studentSelect.options).forEach(option => option.selected = false);
        }
    });

    studentSelect.addEventListener('change', () => {
        const hasSelection = Array.from(studentSelect.options).some(opt => opt.selected);
        if (hasSelection) {
            sectionSelect.selectedIndex = 0;
        }
    });

});
</script>





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
const studentID = `SD${currentYear}${String(currentWeek).padStart(2, '0')}${String(currentDay)}${lastThreeDigits}`;

// Set the value in the input field
document.getElementById('scheduleID').value = studentID;
</script>

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

<!-- FOR TESTING -->

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Second set of dummy values (for section-based schedule)
    const dummyScheduleName = 'Afternoon Occupational Therapy';
    const dummyScheduleCode = 'AOT-3025';
    const dummyScheduleType = 'academic';
    const dummyStatus = 'active';
    const dummyDescription = 'Afternoon schedule assigned to a specific class section focusing on occupational therapy exercises.';

    // Fill text inputs
    document.querySelector('input[name="schedule_name"]').value = dummyScheduleName;
    document.querySelector('input[name="schedule_code"]').value = dummyScheduleCode;

    // Select options
    document.querySelector('select[name="schedule_type"]').value = dummyScheduleType;
    document.querySelector('select[name="status"]').value = dummyStatus;

    // Description textarea
    document.querySelector('textarea[name="description"]').value = dummyDescription;

    // Select the second teacher if available
    const teacherSelect = document.getElementById('select-teacher');
    if (teacherSelect.options.length > 2) {
        teacherSelect.selectedIndex = 2;
    } else if (teacherSelect.options.length > 1) {
        teacherSelect.selectedIndex = 1;
    }

    // Select the second section if available
    const sectionSelect = document.getElementById('select-section');
    if (sectionSelect.options.length > 2) {
        sectionSelect.selectedIndex = 2;
    } else if (sectionSelect.options.length > 1) {
        sectionSelect.selectedIndex = 1;
    }

    // Deselect all students (section-only schedule)
    const studentSelect = document.getElementById('select-students');
    for (let i = 0; i < studentSelect.options.length; i++) {
        studentSelect.options[i].selected = false;
    }
});
</script>

