<section class="home-section">
<div class="text">Create New Announcement</div>
<div class="teacher-container">
@include('mio.dashboard.status-message')
 <form  action="{{ route('mio.StoreAnnouncement') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <!-- HEADER CONTROLS -->
    <div class="table-header">
            <div class="search-container" style="background: transparent;">
            </div>

            <div class="button-group">
            <button type="button" class="btn cancel-btn"><a href="{{ url()->previous() }}">Cancel</a>
            </button>
            <button class="btn add-btn">
                <span class="icon">✔</span> Save Announcement
            </button>
            </div>

        </div>
        <div class="form-container">
           <!-- Personal Information Section -->
           <div class="section-header">Announcement Information</div>
                    <div class="section-content">
                        <!--  -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Event Title</label>
                                <input type="text" placeholder="Title..." name="announce_title" required/>
                            </div>
                            <div class="form-group">
                                <label for="people">People</label>
                                <select id="people" name="announce_people" required>
                                    <option value="all">All</option>
                                    <option value="students">Students</option>
                                    <option value="teachers">Teachers</option>
                                    <option value="parents">Parents</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                        </div>
                        <!--  -->
                        <div class="form-row">
                        <div class="form-group wide">
                            <label>Date</label>
                            <input type="date" name="announce_date" required min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"/>

                            </div>

                        </div>

                        <div class="form-row">
                        <div class="form-group wide">
                                <label>Event Description</label>
                                <textarea style="resize: none; " placeholder="Description..." name="announce_description" required></textarea>
                            </div>

                        </div>
                        <!--  -->
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
