<section class="home-section">
<div class="text">Edit Department</div>
<div class="teacher-container">
 <form action="{{ route('mio.UpdateDepartment', ['id' => $editdata['departmentid']]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <!-- HEADER CONTROLS -->
    <div class="table-header">
            <div class="search-container" style="background: transparent;">
            </div>

            <div class="button-group">
            <button type="button" class="btn cancel-btn"><a href="{{ url()->previous() }}">Cancel</a>
            </button>
            <button class="btn add-btn">
                <span class="icon">✔</span> Save Changes
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
              <input type="text" name="departmentid" id="departmentID" value="{{ $editdata['departmentid'] }}" required />
            </div>
            <div class="form-group">
              <label>Department Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="department_name" value="{{ $editdata['department_name'] }}" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
                <label>Department Code <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="department_code" value="{{ $editdata['department_code'] }}" required />
            </div>

            <div class="form-group">
                <label>Department Type <span style="color: red; font-weight:700">*</span></label>
                <select name="department_type" required>
                    <option value="" disabled selected>Select Type</option>
                    <option value="academic" {{ $editdata['department_type'] == 'academic' ? 'selected' : '' }}>Academic</option>
                    <option value="admin_support" {{ $editdata['department_type'] == 'admin_support' ? 'selected' : '' }}>Administrative and Support</option>
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
                >{{ $editdata['description'] }}</textarea>
            </div>
            </div>


          <div class="form-row">
          <div class="form-group" style="flex: 1;">
            <label>Head Teacher <span style="color: red; font-weight:700">*</span></label>
            <select name="teacherid" required>
                <option value="" disabled {{ empty($editdata['teacherid']) ? 'selected' : '' }}>Select a Teacher</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher['teacherid'] }}"
                        {{ (isset($editdata['teacherid']) && $editdata['teacherid'] === $teacher['teacherid']) ? 'selected' : '' }}>
                        {{ $teacher['name'] }}
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
    let scheduleCount = 0;

function addScheduleField() {
  const section = document.getElementById("schedule-section");
  const inputsPerRow = 4;

  // Find all current rows
  let currentRows = section.getElementsByClassName("form-row");

  // Check the last row
  let lastRow = currentRows[currentRows.length - 1];

  // If no row exists or last row has 4 children, create a new row
  if (!lastRow || lastRow.children.length >= inputsPerRow) {
    lastRow = document.createElement("div");
    lastRow.className = "form-row";
    section.insertBefore(lastRow, section.querySelector(".add-btn")); // insert before Add button
  }

  // Create the form-group
  const formGroup = document.createElement("div");
  formGroup.className = "form-group";
  formGroup.style.flex = "1"; // Responsive width

  // Create label and input
  const label = document.createElement("label");
  label.innerHTML = `Schedule ID <span style="color: red; font-weight:700">*</span>`;

  const input = document.createElement("input");
  input.type = "text";
  input.name = "schedule[]";
  input.placeholder = "Schedule ID";

  // Append label and input to formGroup
  formGroup.appendChild(label);
  formGroup.appendChild(input);

  // Append formGroup to the lastRow
  lastRow.appendChild(formGroup);
}

</script>

<script>
function fetchTeacherName(teacherID) {
    if (!teacherID) {
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
}

// On blur
document.getElementById('teacherID').addEventListener('blur', function () {
    fetchTeacherName(this.value.trim());
});

// Auto-fetch on page load
window.addEventListener('DOMContentLoaded', function () {
    const teacherID = document.getElementById('teacherID').value.trim();
    fetchTeacherName(teacherID);
});
</script>

