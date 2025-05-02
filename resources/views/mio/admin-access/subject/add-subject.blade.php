<section class="home-section">
<div class="text">Add New Subject</div>
<div class="teacher-container">

 <form action="{{ route('mio.StoreSubject', ['grade' => $grade]) }}" method="POST">
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
                            <input type="text" name="subject_id" placeholder="Enter Subject ID" required />
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
                        <div class="form-group">
                            <label>Teacher ID <span style="color: red">*</span></label>
                            <input type="text" name="teacher_id" placeholder="Enter Teacher ID" required />
                        </div>
                        <div class="form-group">
                            <label>Section ID <span style="color: red">*</span></label>
                            <input type="text" name="section_id" placeholder="Enter Section ID" required />
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
