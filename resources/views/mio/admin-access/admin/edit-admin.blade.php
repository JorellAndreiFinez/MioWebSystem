<section class="home-section">
<div class="text">Edit Admin</div>
<div class="teacher-container">
@include('mio.dashboard.status-message')

<form action="{{ route('mio.UpdateAdmin', ['id' => $editdata['adminid']]) }}"
      method="post"
      enctype="multipart/form-data">
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
        <!-- Student Information Section -->
        <div class="section-header">Admin Information</div>
        <div class="section-content">
        <div class="form-row">
            <div class="form-group">
                <label>
                    <input type="radio" name="category" value="principal" {{ (isset($editdata['category']) && $editdata['category'] == 'principal') ? 'checked' : '' }} required> Principal
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="radio" name="category" value="assistant_principal" {{ (isset($editdata['category']) && $editdata['category'] == 'assistant_principal') ? 'checked' : '' }}> Assistant Principal
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="radio" name="category" value="registrar" {{ (isset($editdata['category']) && $editdata['category'] == 'registrar') ? 'checked' : '' }}> Registrar
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="radio" name="category" value="guidance_counselor" {{ (isset($editdata['category']) && $editdata['category'] == 'guidance_counselor') ? 'checked' : '' }}> Guidance Counselor
                </label>
            </div>
            <div class="form-group">
                <label><input type="radio" name="category" value="head_admin" {{ (isset($editdata['category']) && $editdata['category'] == 'head_admin') ? 'checked' : '' }}>
                Head Admin
            </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="radio" name="category" value="admin_staff" {{ (isset($editdata['category']) && $editdata['category'] == 'admin_staff') ? 'checked' : '' }}> Admin Staff
                </label>
            </div>
        </div>

            <div class="form-group wide">
                <label>Admin ID <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="adminid" id="studentID" value="{{ $editdata['adminid'] }}" required />
            </div>



            <!-- If the admin is also a teacher, put teacher id to get the teacher info and reflect in the inputs -->
            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                <select name="teacherid">
                    <option value="" disabled {{ !isset($section['teacherid']) ? 'selected' : '' }}>Select a Teacher</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher['teacherid'] }}"
                            {{ (isset($section['teacherid']) && $section['teacherid'] === $teacher['teacherid']) ? 'selected' : '' }}>
                            {{ $teacher['name'] }}
                        </option>
                    @endforeach
                </select>

                </div>

            </div>
        </div>


        <!-- Personal Information Section -->
        <div class="section-header">Personal Information</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-group">
              <label>First Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="first_name" value="{{ $editdata['fname'] }}" required />
            </div>
            <div class="form-group">
              <label>Last Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="last_name" value="{{ $editdata['lname'] }}" required />
            </div>
            <div class="form-group">
            <label>Gender <span style="color: red; font-weight:700">*</span></label>
            <select name="gender" required>
                <option value="" disabled {{ !isset($editdata['gender']) ? 'selected' : '' }}>Select Gender</option>
                <option value="Male" {{ isset($editdata['gender']) && $editdata['gender'] == 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ isset($editdata['gender']) && $editdata['gender'] == 'Female' ? 'selected' : '' }}>Female</option>
                <option value="Other" {{ isset($editdata['gender']) && $editdata['gender'] == 'Other' ? 'selected' : '' }}>Other</option>
            </select>

            </div>
            <div class="form-group">
              <label>Age <span style="color: red; font-weight:700">*</span></label>
              <input type="number" name="age" value="{{ $editdata['age'] }}" required />
            </div>
            <div class="form-group">
              <label>Birthday <span style="color: red; font-weight:700">*</span></label>
              <input type="date" name="birthday" value="{{ $editdata['bday'] }}" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group wide">
              <label>Street Name, Building, House No. <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="address" value="{{ $editdata['address'] }}" required />
            </div>
            <div class="form-group wide">
              <label>Barangay <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="barangay" value="{{ $editdata['barangay'] }}" required />
            </div>
            <div class="form-group wide">
            <label for="region">Region *</label>
            <select id="region" name="region" required>
                <option value="" disabled selected>Select a Region</option>
                <option value="NCR" @if (isset($editdata['region']) && $editdata['region'] == 'NCR') selected @endif>
                    National Capital Region (NCR)
                </option>
                <option value="CAR" @if (isset($editdata['region']) && $editdata['region'] == 'CAR') selected @endif>
                    Cordillera Administrative Region (CAR)
                </option>
                <!-- Add more options as needed -->
            </select>
        </div>

          </div>

          <div class="form-row">
            <div class="form-group wide">
              <label>Province <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="province" value="{{ $editdata['province'] }}" required />
            </div>
            <div class="form-group wide">
              <label>City <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="city" value="{{ $editdata['city'] }}" required />
            </div>
            <div class="form-group wide">
              <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
              <input type="number" name="zip_code" value="{{ $editdata['zip_code'] }}" minlength="4" maxlength="4" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="contact_number" value="{{ $editdata['contact_number'] }}" required />
            </div>
            <div class="form-group wide">
              <label>Emergency Contact Number <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="emergency_contact" value="{{ $editdata['emergency_contact'] }}" required />
            </div>
            <div class="form-group">
              <label>Email <span style="color: red; font-weight:700">*</span></label>
            <input type="text" name="email" value="{{ $editdata['email'] }}" required />
            </div>
          </div>
        </div>

        <!-- Academic Information Section -->
        <div class="section-header">Academic Information</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-group wide">
              <label>Previous School Attended <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="previous_school" value="Blah blah High school" required />
            </div>
            <div class="form-group">
              <label>Grade Level <span style="color: red; font-weight:700">*</span></label>
              <input type="number" name="grade_level" value="10" required />
            </div>
          </div>
        </div>

        <!-- Account Information Section -->
        <div class="section-header">Account Information</div>
        <div class="section-content">
            <div class="form-row">
                <div class="form-group">
                    <label>Username <span style="color: red; font-weight:700">*</span></label>
                    <input type="text" name="username" id="account_username" value="{{ $editdata['username'] ?? '' }}" required readonly/>
                </div>
                <div class="form-group">
                <label for="account_password">
                    Password
                </label>
                <div style="position: relative;">
                    <input
                        type="password"
                        name="account_password"
                        id="account_password"
                        placeholder="Enter new password if changing"
                        class="form-control"
                    />
                    <button
                        type="button"
                        onclick="togglePasswordVisibility()"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none;"
                    >
                        <i class="fa fa-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>
            <div class="form-group">
                    <label>Account Status <span style="color: red; font-weight:700">*</span></label>
                    <select name="account_status" required>
                        <option value="active" @if (isset($editdata['account_status']) && $editdata['account_status'] == 'active') selected @endif>Active</option>
                        <option value="inactive" @if (isset($editdata['account_status']) && $editdata['account_status'] == 'inactive') selected @endif>Inactive</option>
                    </select>
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
function togglePasswordVisibility() {
    const input = document.getElementById('account_password');
    const icon = document.getElementById('eye-icon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<script>
// Function to update account username and password fields
function updateAccountInfo() {
    const personalEmail = document.querySelector('input[name="email"]').value;

    document.getElementById('account_username').value = personalEmail;  // use email as username

}

// Update fields when the page loads
window.addEventListener('load', updateAccountInfo);

// Also update fields whenever email or birthday inputs are changed
document.querySelector('input[name="email"]').addEventListener('input', updateAccountInfo);
</script>

<script>
document.getElementById('teacherID').addEventListener('blur', function () {
    const teacherID = this.value.trim();
    if (teacherID === '') return;

    fetch(`/mio/admin/get-teacher/${teacherID}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Teacher not found');
            }
            return response.json();
        })
        .then(data => {
            document.querySelector('input[name="first_name"]').value = data.first_name || '';
            document.querySelector('input[name="last_name"]').value = data.last_name || '';
            document.querySelector('select[name="gender"]').value = data.gender || '';
            document.querySelector('input[name="age"]').value = data.age || '';
            document.querySelector('input[name="birthday"]').value = data.birthday || '';
            document.querySelector('input[name="address"]').value = data.address || '';
            document.querySelector('input[name="barangay"]').value = data.barangay || '';
            document.querySelector('select[name="region"]').value = data.region || '';
            document.querySelector('input[name="province"]').value = data.province || '';
            document.querySelector('input[name="city"]').value = data.city || '';
            document.querySelector('input[name="zip_code"]').value = data.zip_code || '';
            document.querySelector('input[name="contact_number"]').value = data.contact_number || '';
            document.querySelector('input[name="email"]').value = data.email || '';

            // Optional: Update username and password if needed
            updateAccountInfo();
        })
        .catch(error => {
            alert(error.message);
        });
});
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
