<section class="home-section">
  <div class="text">Add New Department</div>

  <div class="teacher-container">
    @include('mio.dashboard.status-message')

    <form action="{{ route('mio.StoreDepartment') }}" method="POST">
      @csrf

      <!-- HEADER CONTROLS -->
      <div class="table-header">
        <div class="search-container" style="background: transparent;"></div>
        <div class="button-group">
          <button type="button" class="btn cancel-btn"><a href="{{ url()->previous() }}">Cancel</a></button>
          <button type="submit" class="btn add-btn">
            <span class="icon">+</span> Add Department
          </button>
        </div>
      </div>

      <div class="form-container">
        <!-- Department Information -->
        <div class="section-header">Department Information</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-group">
              <label>Department ID <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="departmentid" id="departmentID" required />
            </div>
            <div class="form-group">
              <label>Department Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="department_name" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
                <label>Department Code <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="department_code" required />
            </div>

            <div class="form-group">
                <label>Department Type <span style="color: red; font-weight:700">*</span></label>
                <select name="department_type" required>
                    <option value="" disabled selected>Select Type</option>
                    <option value="academic" >Academic</option>
                    <option value="admin_support">Administrative and Support</option>
                </select>
            </div>

          </div>

          <div class="form-row">
            <div class="form-group" style="flex: 1;">
                <label>Description</label>
                <textarea
                name="description"
                rows="3"
                placeholder="Describe the department's purpose or scope..."
                style="resize: none; height: 100px;"
                ></textarea>
            </div>
            </div>


            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label>Head Teacher <span style="color: red; font-weight:700">*</span></label>
                    <select name="teacherid">
                        <option value="" disabled selected>Select a Teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher['teacherid'] }}">
                                {{ $teacher['name'] }}
                                ({{ $teacher['departmentname'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>


        </div>
      </div>
    </form>
  </div>
</section>

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
const studentID = `DE${currentYear}${String(currentWeek).padStart(2, '0')}${String(currentDay)}${lastThreeDigits}`;

// Set the value in the input field
document.getElementById('departmentID').value = studentID;
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

