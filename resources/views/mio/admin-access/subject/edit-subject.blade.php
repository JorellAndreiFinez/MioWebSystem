<section class="home-section">
<div class="text">Edit Subject</div>
<div class="teacher-container">
<form action="{{ route('mio.UpdateSubject', ['grade' => $grade, 'subjectId' => $subject_id]) }}"
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
                <!-- Subject Information -->
                <div class="section-header">Subject Details</div>
                <div class="section-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject ID <span style="color: red">*</span></label>
                            <input type="text" name="subject_id" id="subjectID" value="{{ $editdata['subject_id'] }}" placeholder="Enter Subject ID" required />
                        </div>
                        <div class="form-group">
                            <label>Subject Code <span style="color: red">*</span></label>
                            <input type="text" name="code" value="{{ $editdata['code'] }}" placeholder="Enter Subject Code" required />
                        </div>
                        <div class="form-group">
                            <label>Subject Title <span style="color: red">*</span></label>
                            <input type="text" name="title" value="{{ $editdata['title'] }}" placeholder="Enter Subject Title" required />
                        </div>
                    </div>

                    <div class="form-row">
                    <div class="form-group wide">
                        <label>Teacher ID</label>
                        <select name="teacher_id" id="teacherID">
                                <<option value="" selected>Select a Teacher</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher['teacherid'] }}"
                                        {{ ($editdata['teacher_id'] ?? '') == $teacher['teacherid'] ? 'selected' : '' }}>
                                        {{ $teacher['name'] }}
                                    </option>
                                    </option>
                                    </option>
                                @endforeach
                            </select>
                    </div>

                        <div class="form-group">
                            <label>Section ID <span style="color: red">*</span></label>

                            <select name="section_id" id="sectionID">
                                <option value="">Select a Section</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section['sectionid'] }}"
                                        {{ ($editdata['section_id'] ?? '') == $section['sectionid'] ? 'selected' : '' }}>
                                        {{ $section['name'] }} ({{ $section['status'] }})
                                        @if(isset($section['date_created']))
                                            - {{ \Carbon\Carbon::parse($section['date_created'])->format('M d, Y') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Module Section -->
                <div class="section-header">Modules</div>
                <div class="section-content" id="module-section">
                    @php $moduleIndex = 0; @endphp

                    @foreach ($editdata['modules'] ?? [] as $module)
                        <div class="form-row module-row" data-index="{{ $moduleIndex }}">
                            <div class="form-group">
                                <label>Module Title <span style="color: red">*</span></label>
                                <input type="text" name="modules[{{ $moduleIndex }}][title]" value="{{ $module['title'] ?? '' }}" placeholder="e.g. Module {{ $moduleIndex + 1 }}" required />
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="modules[{{ $moduleIndex }}][description]" placeholder="Optional module description">{{ $module['description'] ?? '' }}</textarea>
                            </div>
                        </div>

                        @if ($moduleIndex > 0)
                        <div class="form-row remove-row" data-index="{{ $moduleIndex }}">
                            <div class="form-group" style="align-self: end;">
                                <button type="button" class="btn cancel-btn" onclick="removeModuleField(this)">Remove</button>
                            </div>
                        </div>
                        @endif

                        @php $moduleIndex++; @endphp
                        @endforeach


                    <!-- Add Module Button -->
                    <button type="button" class="btn add-btn" onclick="addModuleField()">+ Add Module</button>
                </div>
            </div>

 </form>
</div>

</section>


<!-- ADD MODULE SECTION -->
<script>
let moduleCount = {{ count($editdata['modules'] ?? []) }};


function addModuleField() {
    const section = document.getElementById("module-section");

    // Create module input row
    const moduleRow = document.createElement("div");
    moduleRow.className = "form-row module-row";
    moduleRow.dataset.index = moduleCount;

    moduleRow.innerHTML = `
        <div class="form-group">
            <label>Module Title <span style="color: red">*</span></label>
            <input type="text" name="modules[${moduleCount}][title]" placeholder="e.g. Module ${moduleCount + 1}" required />
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="modules[${moduleCount}][description]" placeholder="Optional module description"></textarea>
        </div>
    `;

    // Create separate remove button row
    const removeRow = document.createElement("div");
    removeRow.className = "form-row remove-row";
    removeRow.dataset.index = moduleCount;

    removeRow.innerHTML = `
        <div class="form-group" style="align-self: end;">
            <button type="button" class="btn cancel-btn" onclick="removeModuleField(this)">Remove</button>
        </div>
    `;

    // Insert before the "Add Module" button
    section.insertBefore(moduleRow, section.querySelector(".add-btn"));
    section.insertBefore(removeRow, section.querySelector(".add-btn"));

    moduleCount++;
}

function removeModuleField(button) {
    const removeRow = button.closest('.remove-row');
    const index = removeRow.dataset.index;

    const inputRow = document.querySelector(`.module-row[data-index="${index}"]`);
    if (inputRow) inputRow.remove();
    if (removeRow) removeRow.remove();

    moduleCount--;
}
</script>


<!-- TEACHER ID -->
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

<!-- TEACHER ID -->
<script>
document.getElementById('sectionID').addEventListener('blur', function () {
    const teacherID = this.value.trim();

    fetch(`/mio/admin/get-section/${sectionID}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Section not found');
            }
            return response.json();
        })
        .then(data => {
            const fullName = (data.section_name || '');
            document.getElementById('teacherNameDisplay').value = fullName.trim() || 'No name available';
        })
        .catch(error => {
            document.getElementById('teacherNameDisplay').value = 'Not found';
            console.error(error);
        });
});
</script>
