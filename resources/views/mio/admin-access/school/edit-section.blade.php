<section class="home-section">
<div class="text">Edit Section</div>
<div class="teacher-container">
 <form action="{{ route('mio.UpdateSection', ['id' => $editdata['sectionid']]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <!-- HEADER CONTROLS -->
    <div class="table-header">
            <div class="search-container" style="background: transparent;">
            </div>

            <div class="button-group">
            <button type="button" class="btn cancel-btn"><a href="{{ route("mio.ViewSection") }}">Cancel</a>
            </button>
            <button class="btn add-btn">
                <span class="icon">✔</span> Save Changes
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
              <input type="text" name="sectionid" id="sectionID" value="{{ $editdata['sectionid'] }}" required />
            </div>
            <div class="form-group">
              <label>Section Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="section_name" value="{{ $editdata['section_name'] }}" required />
            </div>
          </div>



          <div class="form-row">
            <div class="form-group">
              <label>Status <span style="color: red; font-weight:700">*</span></label>
              <select name="status" required>
                <option value="" disabled>Select Status</option>
                <option value="active" {{ $editdata['status'] == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $editdata['status'] == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            </div>
            <div class="form-group">
              <label>Section Status <span style="color: red; font-weight:700">*</span></label>
              <select name="section_status" required>
                <option value="" disabled>Select Section Status</option>
                <option value="open" {{ $editdata['section_status'] == 'open' ? 'selected' : '' }}>Open</option>
                <option value="closed" {{ $editdata['section_status'] == 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
            </div>

            <div class="form-group">
                <label>Max Number of Students <span style="color: red; font-weight:700">*</span></label>
                <input type="number" name="max_students" min="1" max="100" value="{{ $editdata['max_students'] }}" required />
            </div>

            <div class="form-group">
                <label>Section Grade <span style="color: red; font-weight:700">*</span></label>
                <input type="number" name="section_grade" min="1" max="10" placeholder="Enter section grade" required value="{{ $editdata['section_grade'] }}" />
            </div>
          </div>

          <div class="form-row">
          <div class="form-group" style="flex: 1;">
            <label>Teacher <span style="color: red; font-weight:700">*</span></label>
            <select name="teacherid">
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

