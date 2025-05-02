<section class="home-section">
  <div class="text">Add New Section</div>

  <div class="teacher-container">
    @include('mio.dashboard.status-message')

    <form action="{{ route('mio.AddSection') }}" method="POST">
      @csrf

      <!-- HEADER CONTROLS -->
      <div class="table-header">
        <div class="search-container" style="background: transparent;"></div>
        <div class="button-group">
          <button type="button" class="btn cancel-btn"><a href="{{ url()->previous() }}">Cancel</a></button>
          <button type="submit" class="btn add-btn">
            <span class="icon">+</span> Add Section
          </button>
        </div>
      </div>

      <div class="form-container">
        <!-- Section Information -->
        <div class="section-header">Section Information</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-group">
              <label>Section ID <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="sectionid" id="sectionID" required />
            </div>
            <div class="form-group">
              <label>Section Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="section_name" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Status <span style="color: red; font-weight:700">*</span></label>
              <select name="status" required>
                <option value="" disabled selected>Select Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="form-group">
              <label>Section Status <span style="color: red; font-weight:700">*</span></label>
              <select name="section_status" required>
                <option value="" disabled selected>Select Section Status</option>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
              </select>
            </div>

            <div class="form-group">
                <label>Max Number of Students <span style="color: red; font-weight:700">*</span></label>
                <input type="number" name="max_students" min="1" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group" style="flex: 1;">
                <label>Assign Teacher <span style="color: red; font-weight:700">*</span></label>
                <select name="teacherid" required>
                    <option value="" disabled selected>Select a Teacher</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher['teacherid'] }}">{{ $teacher['name'] }}</option>
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
const studentID = `SE${currentYear}${String(currentWeek).padStart(2, '0')}${String(currentDay)}${lastThreeDigits}`;

// Set the value in the input field
document.getElementById('sectionID').value = studentID;
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

