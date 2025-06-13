<section class="home-section">
  <div class="text">Add New Schedule</div>
<!-- <pre>print_r($teachers, true) </pre> -->

  <div class="teacher-container">
    @if (session('status'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


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

           <!-- timetable preview of that section -->
             <div class="form-row">
                <div class="form-group wide">
                    <label id="timetable-label">Timetable Preview</label>

                    <div id="timetable-preview" style="overflow-x: auto;">
                        <em>Select a section to preview its timetable.</em>
                    </div>
                </div>
            </div>

            <!-- schedule information -->
            <div class="form-row">
                <div class="form-group">
                    <label>
                        <input type="checkbox"  name="sameTimeToggle"  id="sameTimeToggle" checked />
                        Use same time for all selected days
                    </label>
                </div>
            </div>

            <div id="common-time" class="form-row">
                <div class="form-group">
                    <label>Start Time <span style="color: red">*</span></label>
                    <input type="time" name="common_start_time" />
                </div>

                <div class="form-group">
                    <label>End Time <span style="color: red">*</span></label>
                    <input type="time" name="common_end_time" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label>Occurrences (Days of Week) <span style="color: red; font-weight:700">*</span></label>
                    <select name="occurrences[]" id="occurrences" multiple required style="height: 170px;">
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

            <!-- Individual day time inputs will appear here -->
            <div id="individual-times"></div>

        </div>

      </div>
    </form>
  </div>
</section>


<!-- SCRIPTS -->

<!-- TIME TABLE -->

<script>
    const sameTimeToggle = document.getElementById('sameTimeToggle');
    const occurrencesSelect = document.getElementById('occurrences');
    const individualTimes = document.getElementById('individual-times');
    const commonTimeSection = document.getElementById('common-time');

    function updateTimeFields() {
        const selectedDays = Array.from(occurrencesSelect.selectedOptions).map(opt => opt.value);
        individualTimes.innerHTML = "";

        if (!sameTimeToggle.checked && selectedDays.length > 0) {
            selectedDays.forEach(day => {
                individualTimes.innerHTML += `
                    <div class="form-row">
                        <label style="font-weight: bold; margin-top: 10px;">${day}</label>
                        <div class="form-group">
                            <label>Start Time for ${day}</label>
                            <input type="time" name="day_times[${day}][start]" />
                        </div>
                        <div class="form-group">
                            <label>End Time for ${day}</label>
                            <input type="time" name="day_times[${day}][end]" />
                        </div>
                    </div>
                `;
            });
        }
    }

    sameTimeToggle.addEventListener('change', () => {
        if (sameTimeToggle.checked) {
            commonTimeSection.style.display = 'flex';
            individualTimes.innerHTML = '';
        } else {
            commonTimeSection.style.display = 'none';
            updateTimeFields();
        }
    });

    occurrencesSelect.addEventListener('change', () => {
        if (!sameTimeToggle.checked) {
            updateTimeFields();
        }
    });

    // Initialize state
    document.addEventListener('DOMContentLoaded', () => {
        commonTimeSection.style.display = 'flex';
    });
</script>

<script>
    const teachers = @json($teachers);
    const students = @json($students);


    // Define time slots
    const timeSlots = [
        '06:00', '07:00',
        '07:00', '08:00',
        '08:00', '09:00',
        '09:00', '10:00',
        '10:00', '11:00',
        '11:00', '12:00',
        '12:00', '13:00',
        '13:00', '14:00',
        '14:00', '15:00',
        '15:00', '16:00',
        '16:00', '17:00',
        '17:00', '18:00',
        '18:00', '19:00'
    ];

    function parseTime(timeStr) {
        const [hours, minutes] = timeStr.split(':').map(Number);
        const date = new Date();
        date.setHours(hours, minutes, 0, 0);
        return date;
    }


    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    function timeInRange(start, end, check) {
        return check >= start && check < end;
    }

    function rangesOverlap(startA, endA, startB, endB) {
        const aStart = parseTime(startA);
        const aEnd = parseTime(endA);
        const bStart = parseTime(startB);
        const bEnd = parseTime(endB);

        return aStart < bEnd && aEnd > bStart;
    }



   const teacherSelect = document.getElementById('select-teacher');
    const studentSelect = document.getElementById('select-students');
    const timetableContainer = document.getElementById('timetable-preview');
    const timetableLabel = document.getElementById('timetable-label');

    function renderTimetable(title, scheduleData) {
        timetableContainer.innerHTML = '';

        if (!scheduleData || scheduleData.length === 0) {
            timetableContainer.innerHTML = `<em>No existing schedule for ${title}.</em>`;
            return;
        }

        timetableLabel.textContent = `Timetable Preview (${title})`;

        const table = document.createElement('table');
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';
        table.style.textAlign = 'center';

        const headerRow = document.createElement('tr');
        headerRow.innerHTML = `<th>Time</th>` + days.map(d => `<th>${d}</th>`).join('');
        table.appendChild(headerRow);

        for (let i = 0; i < timeSlots.length; i += 2) {
            const row = document.createElement('tr');
            const timeRange = `${timeSlots[i]} - ${timeSlots[i + 1]}`;
            row.innerHTML = `<td style="font-weight:bold; border:1px solid #ccc;">${timeRange}</td>`;

            days.forEach(day => {
                const cell = document.createElement('td');
                cell.style.border = '1px solid #ccc';
                cell.style.padding = '8px';
                cell.style.minWidth = '120px';
                cell.style.verticalAlign = 'top';

                const slotStart = timeSlots[i];
                const slotEnd = timeSlots[i + 1];

                const matched = scheduleData.filter(sched =>
                    sched.day === day &&
                    rangesOverlap(sched.start, sched.end, slotStart, slotEnd)
                );

                if (matched.length > 0) {
                    matched.forEach(sched => {
                        const div = document.createElement('div');
                        div.textContent = sched.subject || 'Scheduled';

                        // Differentiate based on a flag
                        const isManual = sched.isManual === true;

                        div.style.backgroundColor = isManual ? '#ffe0b3' : '#b3d9ff'; // orange vs blue
                        div.style.border = '1px solid ' + (isManual ? '#e69500' : '#3399ff');
                        div.style.borderRadius = '4px';
                        div.style.padding = '4px';
                        div.style.marginBottom = '4px';
                        div.style.fontSize = '0.9em';
                        cell.appendChild(div);
                    });

                } else {
                    cell.innerHTML = '<span style="color: #aaa;">—</span>';
                }

                row.appendChild(cell);
            });

            table.appendChild(row);
        }

        timetableContainer.appendChild(table);
    }

    teacherSelect.addEventListener('change', function () {
        const selectedTeacherId = this.value;
        const teacher = teachers.find(t => t.teacherid === selectedTeacherId);

        if (!teacher) {
            timetableContainer.innerHTML = '<em>No teacher selected.</em>';
            return;
        }

        let scheduleData = [];

        // Include subject schedules if available
        if (teacher.subject_schedules && teacher.subject_schedules.length > 0) {
            scheduleData = teacher.subject_schedules.map(s => ({
                subject: s.subject,
                day: s.day,
                start: s.start,
                end: s.end
            }));
        }

        // Include manual schedules as dummy entries if schedulename is available
        if (teacher.schedulename && teacher.schedulename !== "Unassigned") {
            const scheduleParts = teacher.schedulename.split('|')[0].split(',');
            scheduleParts.forEach((sched, index) => {
                if (sched.trim() !== '') {
                    scheduleData.push({
                        subject: sched.trim(),
                        day: days[index % days.length], // rotate days
                        start: '10:00',
                        end: '11:00',
                        isManual: true // <-- mark as manual schedule
                    });
                }
            });
        }

        renderTimetable(teacher.name, scheduleData);
    });


    studentSelect.addEventListener('change', function () {
        const selectedOptions = Array.from(this.selectedOptions);
        if (selectedOptions.length === 1) {
            const studentId = selectedOptions[0].value;
            const selected = students.find(s => s.id === studentId);
            if (selected) {
                renderTimetable(selected.name + " (Student)", selected.schedules);
            }
        } else if (selectedOptions.length > 1) {
            timetableContainer.innerHTML = '<em>Multiple students selected. Please select one to view their schedule.</em>';
            timetableLabel.textContent = 'Timetable Preview';
        } else {
            timetableContainer.innerHTML = '<em>Select a student to preview their schedule.</em>';
            timetableLabel.textContent = 'Timetable Preview';
        }
    });
</script>


<!-- SELECT INPUTS -->
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
    // Dummy values
    const dummyScheduleName = 'Morning OT - 1-on-1';
    const dummyScheduleCode = 'OT101-MORN';
    const dummyScheduleType = 'specialized';
    const dummyStatus = 'active';
    const dummyDescription = 'Morning one-on-one occupational therapy sessions for selected students.';
    const dummyStartTime = '09:00';
    const dummyEndTime = '10:00';

    // Fill in basic schedule info
    document.querySelector('input[name="schedule_name"]').value = dummyScheduleName;
    document.querySelector('input[name="schedule_code"]').value = dummyScheduleCode;
    document.querySelector('select[name="schedule_type"]').value = dummyScheduleType;
    document.querySelector('select[name="status"]').value = dummyStatus;
    document.querySelector('textarea[name="description"]').value = dummyDescription;

    // Select the first available teacher (or second if exists)
    const teacherSelect = document.getElementById('select-teacher');
    if (teacherSelect.options.length > 2) {
        teacherSelect.selectedIndex = 2;
    } else if (teacherSelect.options.length > 1) {
        teacherSelect.selectedIndex = 1;
    }

    // Select two students for 1-on-1
    const studentSelect = document.getElementById('select-students');
    for (let i = 0; i < studentSelect.options.length; i++) {
        studentSelect.options[i].selected = (i === 0 || i === 1); // select first two
    }

    // Select Monday and Wednesday as occurrences
    const occurrenceSelect = document.getElementById('occurrences');
    for (let i = 0; i < occurrenceSelect.options.length; i++) {
        const value = occurrenceSelect.options[i].value;
        occurrenceSelect.options[i].selected = (value === 'Monday' || value === 'Wednesday');
    }

    // Ensure "Same Time" toggle is checked
    const toggle = document.getElementById('sameTimeToggle');
    toggle.checked = true;

    // Set common time
    document.querySelector('input[name="common_start_time"]').value = dummyStartTime;
    document.querySelector('input[name="common_end_time"]').value = dummyEndTime;

    // Trigger change event to show correct fields
    const changeEvent = new Event('change');
    occurrenceSelect.dispatchEvent(changeEvent);
    toggle.dispatchEvent(changeEvent);
});
</script>


