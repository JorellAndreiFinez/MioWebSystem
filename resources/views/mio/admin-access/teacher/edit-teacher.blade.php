<section class="home-section">
<div class="text">Edit Teacher</div>
<div class="teacher-container">
 <form action="#" method="POST" enctype="multipart/form-data">
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
         <!-- Personal Information Section -->
         <div class="section-header">Teacher Category</div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group">
                            <label><input type="radio" name="category" value="new"> New</label>
                            </div>
                            <div class="form-group teacher-category">
                            <label><input type="radio" name="category" value="full-time" checked> Full-time</label>
                            </div>

                            <div class="form-group teacher-category">
                            <label><input type="radio" name="category" value="part-time"> Part-time</label>
                            </div>

                            <div class="form-group teacher-category">
                            <label><input type="radio" name="category" value="intern"> Intern</label>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information Section -->
                    <div class="section-header">Personal Information</div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name <span style="color: red; font-weight:700">*</span> </label>
                                <input type="text" value="Jorell Andrei"  />
                            </div>
                            <div class="form-group">
                                <label>Last Name <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" value="Finez"  />
                            </div>
                            <div class="form-group">
                                <label>Gender <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" value="Female"  />
                            </div>
                            <div class="form-group">
                                <label>Age <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" value="17"  />
                            </div>
                            <div class="form-group">
                                <label>Birthday <span style="color: red; font-weight:700">*</span></label>
                                <input type="date" placeholder="MM/DD/YYYY"  />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Street Name, Building, House No.  <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" value="13 Blk Lot 8, Camella Homes, Valenzuela City"  />
                            </div>
                            <div class="form-group wide">
                                <label>Barangay <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" value="Brgy. Lapuk"  />
                            </div>

                            <div class="form-group wide">
                            <label for="region">Region *</label>
                            <select id="region" name="region" required>
                                <option value="" disabled selected>Select a Region</option>
                                <option value="NCR">National Capital Region (NCR)</option>
                                <option value="CAR">Cordillera Administrative Region (CAR)</option>
                                <option value="Region I">Ilocos Region (Region I)</option>
                                <option value="Region II">Cagayan Valley (Region II)</option>
                                <option value="Region III">Central Luzon (Region III)</option>
                                <option value="Region IV-A">CALABARZON (Region IV-A)</option>
                                <option value="MIMAROPA">MIMAROPA Region</option>
                                <option value="Region V">Bicol Region (Region V)</option>
                                <option value="Region VI">Western Visayas (Region VI)</option>
                                <option value="Region VII">Central Visayas (Region VII)</option>
                                <option value="Region VIII">Eastern Visayas (Region VIII)</option>
                                <option value="Region IX">Zamboanga Peninsula (Region IX)</option>
                                <option value="Region X">Northern Mindanao (Region X)</option>
                                <option value="Region XI">Davao Region (Region XI)</option>
                                <option value="Region XII">SOCCSKSARGEN (Region XII)</option>
                                <option value="Region XIII">Caraga (Region XIII)</option>
                                <option value="BARMM">Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)</option>
                            </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Province  <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" value="13 Blk Lot 8, Camella Homes, Valenzuela City"  />
                            </div>
                            <div class="form-group wide">
                                <label>City <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" value="Brgy. Lapuk"  />
                            </div>
                            <div class="form-group wide">
                                <label>Zip Code <span style="color: red; font-weight:700">*</span></label>
                                <input type="number"minlength="4" maxlength="4"  value="3333"  />
                            </div>

                        </div>

                        <div class="form-row">
                        <div class="form-group">
                                <label>Contact Number <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" value="09053622382"  />
                            </div>
                            <div class="form-group wide">
                                <label>Emergency Contact Number  <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" value="09053622382"  />
                            </div>
                        </div>
                    </div>

                    <!-- Academic Information Section -->
                    <div class="section-header">Academic Information

                    </div>
                    <div class="section-content">
                        <div class="form-row">
                            <div class="form-group wide">
                                <label>Previous School Attended <span style="color: red; font-weight:700">*</span></label>
                                <input type="text" value="Blah blah High school"  />
                            </div>
                            <div class="form-group">
                                <label>Grade Level <span style="color: red; font-weight:700">*</span></label>
                                <input type="number" value="10"  />
                            </div>

                        </div>

                    </div>

                   <!-- Schedule Section -->
                <!-- Schedule Section -->
                <div class="section-header">Schedule</div>
                <div class="section-content" id="schedule-section">
                <!-- Container for all form rows -->
                <div class="form-row" id="schedule-container">
                <div class="form-group">
                    <label>Schedule ID<span style="color: red; font-weight:700">*</span></label>
                    <input type="text" name="schedule[]" placeholder="Schedule ID" />
                    </div>
                </div>
                <button type="button" onclick="addScheduleField()" class="add-btn">Add More</button>
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
