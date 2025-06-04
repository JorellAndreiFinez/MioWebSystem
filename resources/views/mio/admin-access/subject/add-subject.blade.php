<section class="home-section">
<div class="text">Add New Subject</div>
<div class="teacher-container">

<form action="{{ route('mio.StoreSubject', ['grade' => $grade]) }}" method="POST" enctype="multipart/form-data">

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
        <!-- Subject Details -->
        <div class="section-header">Subject Details</div>
        <div class="section-content">
            <div class="form-row">
                <div class="form-group">
                    <label>Subject ID <span style="color: red">*</span></label>
                    <input type="text" name="subject_id" id="subjectID" placeholder="Enter Subject ID" required  />
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
                    <label>Subject Type <span style="color: red">*</span></label>
                    <select name="subjectType" required>
                        <option value="">Select Subject Type</option>
                        <option value="academics">Academics</option>
                        <option value="specialized">Specialized</option>
                    </select>
                </div>

                <div class="form-group" id="specializedTypeGroup" style="display: none;">
                <label>Specialized Type <span style="color: red">*</span></label>
                <select name="specialized_type">
                        <option value="">Select Specialized Type</option>
                        <option value="speech">Speech</option>
                        <option value="auditory">Auditory</option>
                        <option value="language">Language</option>

                    </select>
            </div>


            </div>

            <div class="form-row">
                <div class="form-group wide">
                    <label>Teacher ID</label>
                    <select name="teacher_id" id="teacherID">
                        <option value="">Select a Teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher['teacherid'] }}">{{ $teacher['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Section ID <span style="color: red">*</span></label>
                    <select name="section_id" id="sectionID">
                        <option value="">Select a Section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section['sectionid'] }}">
                                {{ $section['name'] }} ({{ $section['status'] }})
                            </option>
                        @endforeach
                    </select>
                </div>


            </div>
        </div>

        <!-- Modules Section -->
        <div class="section-header">Modules</div>
        <div class="section-content" id="module-section">
            <!-- Initial Module Block -->
            <div class="form-row module-row" data-index="0">
                <div class="form-group">
                    <label>Module Title <span style="color: red">*</span></label>
                    <input type="text" name="modules[0][title]" placeholder="e.g. Module 1: Introduction" required />
                </div>
            </div>

            <div class="form-row module-row" data-index="0">
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="modules[0][description]" placeholder="Optional module description"></textarea>
                </div>
            </div>

            <!-- Display here the preview of the file -->
            <!-- Preview Area -->
            <div class="form-row">
                <div class="form-group wide">
                    <label>File Previews</label>
                    <div id="file-preview-0" class="file-preview-area" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>

                </div>
            </div>

            <!-- Upload + Link -->
            <div class="form-row">
                <div class="form-group custom-file-upload">
                    <label>Upload Files</label>
                    <label for="file-upload-0" class="file-label">
                        <span class="upload-icon">📁</span> Choose Files
                    </label>
                    <input
                        type="file"
                        name="modules[0][files][]"
                        id="file-upload-0"
                        class="file-input"
                        multiple
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.heic,.heif"
                    />
                    <span class="file-name" id="file-name-0">No file chosen</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Or Provide External Link</label>
                    <input
                        type="url"
                        name="modules[0][external_link]"
                        placeholder="https://example.com/file.pdf"
                    />
                </div>
            </div>


            <!-- Add Module Button -->
            <button type="button" class="btn add-btn" onclick="addModuleField()">+ Add Module</button>
        </div>

        <!-- Announcement Section -->
        <div class="section-header">Announcement
            <button type="button" onclick="addAnnouncement()" class="btn primary-btn" style="color: white; font-weight: 800; font-size: 1.5rem;"> + </button>

        </div>
        <div class="section-content" id="announcement-section">
        <div class="announcement-block" data-index="0">
            <div class="form-row">
                <div class="form-group wide">
                    <label>Announcement Title</label>
                    <input type="text" name="announcements[0][title]" placeholder="Enter Announcement Title" value="Welcome Students!" required />
                </div>

                <div class="form-group">
                    <label>Publish Date</label>
                    <input type="date" name="announcements[0][date]" id="publishDate" required />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group wide">
                    <label>Announcement Description</label>
                    <textarea name="announcements[0][description]" placeholder="Enter details about the announcement..." required></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group wide">
                    <label>Attached Files (Optional)</label>
                    <div id="announcement-preview-0" class="file-preview-area" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group custom-file-upload">
                    <label>Upload Files</label>
                    <label for="announcement-file-upload-0" class="file-label">
                        <span class="upload-icon">📁</span> Choose Files
                    </label>
                    <input
                        type="file"
                        name="announcements[0][files][]"
                        id="announcement-file-upload-0"
                        class="file-input"
                        multiple
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.heic,.heif"
                    />
                    <span class="file-name" id="announcement-file-name-0">No file chosen</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Or External Link (Image/Video)</label>
                    <input type="url" name="announcements[0][link]" placeholder="https://example.com/image.jpg or video.mp4" />
                </div>
            </div>
        </div>
    </div>

    </div>
</form>
</div>
</section>

<!-- -------- SCRIPTS -------- -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('announcement-file-upload-0');
    const preview = document.getElementById('announcement-preview-0');
    const fileNameSpan = document.getElementById('announcement-file-name-0');

    announcementFileDataMap[0] = [];

    fileInput.addEventListener('change', () => {
        const newFiles = Array.from(fileInput.files);
        announcementFileDataMap[0] = newFiles;
        renderAnnouncementPreviews(preview, newFiles, 0, fileNameSpan, fileInput);
    });
});
</script>

<!-- ANNOUNCEMENT -->
<script>
    let announcementCount = 0;
    const announcementFileDataMap = {};

    function addAnnouncement() {
        announcementCount++; // Increment first to ensure index consistency
        const section = document.getElementById('announcement-section');

        const wrapper = document.createElement('div');
        wrapper.className = 'announcement-block';
        wrapper.dataset.index = announcementCount;

        wrapper.innerHTML = `
            <div class="form-row">
                <div class="form-group wide">
                    <label>Announcement Title</label>
                    <input type="text" name="announcements[${announcementCount}][title]" placeholder="Enter Announcement Title" required />
                </div>
                <div class="form-group">
                    <label>Publish Date</label>
                    <input type="date" name="announcements[${announcementCount}][date]" required />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group wide">
                    <label>Announcement Description</label>
                    <textarea name="announcements[${announcementCount}][description]" placeholder="Enter details about the announcement..." required></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group wide">
                    <label>Attached Files (Optional)</label>
                    <div id="announcement-preview-${announcementCount}" class="file-preview-area" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group custom-file-upload">
                    <label>Upload Files</label>
                    <label for="announcement-file-upload-${announcementCount}" class="file-label">
                        <span class="upload-icon">📁</span> Choose Files
                    </label>
                    <input
                        type="file"
                        name="announcements[${announcementCount}][files][]"
                        id="announcement-file-upload-${announcementCount}"
                        class="file-input"
                        multiple
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.heic,.heif"
                    />
                    <span class="file-name" id="announcement-file-name-${announcementCount}">No file chosen</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Or External Link (Image/Video)</label>
                    <input type="url" name="announcements[${announcementCount}][link]" placeholder="https://example.com/image.jpg or video.mp4" />
                </div>
            </div>

            <div class="form-row">
                <button type="button" class="btn cancel-btn" onclick="this.closest('.announcement-block').remove()">Remove</button>
            </div>
        `;

        section.appendChild(wrapper);

        // Now setup file input event
        const fileInput = wrapper.querySelector(`#announcement-file-upload-${announcementCount}`);
        const preview = wrapper.querySelector(`#announcement-preview-${announcementCount}`);
        const fileNameSpan = wrapper.querySelector(`#announcement-file-name-${announcementCount}`);

        announcementFileDataMap[announcementCount] = [];

        fileInput.addEventListener('change', () => {
        const newFiles = Array.from(fileInput.files);
        announcementFileDataMap[announcementCount] = newFiles; // 🔥 store properly
        renderAnnouncementPreviews(preview, newFiles, announcementCount, fileNameSpan, fileInput);
    });


    }


    function renderAnnouncementPreviews(container, fileList, index, fileNameSpan, inputEl) {
        container.innerHTML = '';
        container.style.display = 'flex';

        fileList.forEach((file, i) => {
            const fileBox = document.createElement('div');
            fileBox.style.width = '80px';
            fileBox.style.textAlign = 'center';
            fileBox.style.position = 'relative';

            const icon = document.createElement('div');
            icon.innerHTML = getFileIcon(file.name);
            icon.style.fontSize = '32px';
            icon.style.marginBottom = '5px';

            const label = document.createElement('div');
            label.textContent = shortenName(file.name, 10);
            label.style.fontSize = '12px';

            const removeBtn = document.createElement('span');
            removeBtn.textContent = '×';
            removeBtn.style = 'position: absolute; top: -5px; right: 2px; cursor: pointer; color: red; font-weight: bold; background: white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; font-size: 12px;';
            removeBtn.onclick = () => {
                const updatedFiles = announcementFileDataMap[index].filter((_, j) => j !== i);
                announcementFileDataMap[index] = updatedFiles;
                renderAnnouncementPreviews(container, updatedFiles, index, fileNameSpan, inputEl);
                updateFileInput(inputEl, updatedFiles);
            };


            fileBox.appendChild(removeBtn);
            fileBox.appendChild(icon);
            fileBox.appendChild(label);
            container.appendChild(fileBox);
        });

        fileNameSpan.textContent = fileList.length ? `${fileList.length} file(s) selected` : 'No file chosen';
    }

    function updateFileInput(inputEl, files) {
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        inputEl.files = dataTransfer.files;
    }


    function shortenName(name, maxLen) {
        if (name.length <= maxLen) return name;
        const ext = name.substring(name.lastIndexOf('.'));
        return name.substring(0, maxLen) + '...' + ext;
    }

    function getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const icons = {
            pdf: '📄', doc: '📝', docx: '📝', ppt: '📊', pptx: '📊',
            mp4: '🎬', zip: '🗜️', jpg: '🖼️', jpeg: '🖼️', png: '🖼️',
            gif: '🖼️', bmp: '🖼️', svg: '🖼️', webp: '🖼️', heic: '🖼️', heif: '🖼️',
        };
        return icons[ext] || '📁';
    }
</script>

<!-- ADD MODULE SECTION -->
<script>
let moduleCount = 1;
const fileDataMap = {}; // Needed globally to track each module's selected files

function addModuleField() {
    const section = document.getElementById("module-section");

    const wrapper = document.createElement("div");
    wrapper.className = "module-block";
    wrapper.dataset.index = moduleCount;

    wrapper.innerHTML = `
        <div class="form-row module-row" data-index="${moduleCount}">
            <div class="form-group">
                <label>Module Title <span style="color: red">*</span></label>
                <input type="text" name="modules[${moduleCount}][title]" placeholder="e.g. Module ${moduleCount + 1}" required />
            </div>
        </div>

        <div class="form-row module-row" data-index="${moduleCount}">
            <div class="form-group">
                <label>Description</label>
                <textarea name="modules[${moduleCount}][description]" placeholder="Optional module description"></textarea>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group wide">
                <label>File Previews</label>
                <div id="file-preview-${moduleCount}" class="file-preview-area" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group custom-file-upload">
                <label>Upload Files</label>
                <label for="file-upload-${moduleCount}" class="file-label">
                    <span class="upload-icon">📁</span> Choose Files
                </label>
                <input
                    type="file"
                    name="modules[${moduleCount}][files][]"
                    id="file-upload-${moduleCount}"
                    class="file-input"
                    multiple
                    accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.heic,.heif"
                />
                <span class="file-name" id="file-name-${moduleCount}">No file chosen</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Or Provide External Link</label>
                <input
                    type="url"
                    name="modules[${moduleCount}][external_link]"
                    placeholder="https://example.com/file.pdf"
                />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <button type="button" class="btn cancel-btn" onclick="removeModuleField(this)">Remove</button>
            </div>
        </div>
    `;

    section.insertBefore(wrapper, section.querySelector(".add-btn"));

    // Setup preview for the new file input
    const index = moduleCount;
    const input = wrapper.querySelector(`#file-upload-${index}`);
    const previewArea = wrapper.querySelector(`#file-preview-${index}`);
    const fileNameSpan = wrapper.querySelector(`#file-name-${index}`);

    fileDataMap[index] = [];

    input.addEventListener('change', function () {
        const files = Array.from(input.files);
        fileDataMap[index] = fileDataMap[index].concat(files);
        renderFilePreviews(previewArea, fileDataMap[index], index, fileNameSpan);
    });

    moduleCount++;
}

// Utility functions (reuse from existing script)
function renderFilePreviews(container, fileList, inputIndex, fileNameSpan) {
    container.innerHTML = '';
    container.style.display = 'flex';
    container.style.flexWrap = 'wrap';
    container.style.gap = '10px';

    fileList.forEach((file, i) => {
        const fileBox = document.createElement('div');
        fileBox.style.width = '80px';
        fileBox.style.textAlign = 'center';
        fileBox.style.position = 'relative';

        const icon = document.createElement('div');
        icon.innerHTML = getFileIcon(file.name);
        icon.style.fontSize = '32px';
        icon.style.marginBottom = '5px';

        const fileLabel = document.createElement('div');
        fileLabel.textContent = shortenName(file.name, 10);
        fileLabel.style.fontSize = '12px';
        fileLabel.style.wordBreak = 'break-word';

        const removeBtn = document.createElement('span');
        removeBtn.textContent = '×';
        removeBtn.style.position = 'absolute';
        removeBtn.style.top = '-5px';
        removeBtn.style.right = '2px';
        removeBtn.style.cursor = 'pointer';
        removeBtn.style.color = 'red';
        removeBtn.style.fontWeight = 'bold';
        removeBtn.style.background = 'white';
        removeBtn.style.borderRadius = '50%';
        removeBtn.style.width = '16px';
        removeBtn.style.height = '16px';
        removeBtn.style.display = 'flex';
        removeBtn.style.alignItems = 'center';
        removeBtn.style.justifyContent = 'center';
        removeBtn.style.fontSize = '12px';
        removeBtn.onclick = () => {
            fileDataMap[inputIndex].splice(i, 1);
            renderFilePreviews(container, fileDataMap[inputIndex], inputIndex, fileNameSpan);
        };

        fileBox.appendChild(removeBtn);
        fileBox.appendChild(icon);
        fileBox.appendChild(fileLabel);
        container.appendChild(fileBox);
    });

    fileNameSpan.textContent = fileList.length > 0
        ? `${fileList.length} file(s) selected`
        : 'No file chosen';

    const dataTransfer = new DataTransfer();
    fileDataMap[inputIndex].forEach(file => dataTransfer.items.add(file));
    document.querySelector(`#file-upload-${inputIndex}`).files = dataTransfer.files;
}

function shortenName(name, maxLen) {
    if (name.length <= maxLen) return name;
    const ext = name.substring(name.lastIndexOf('.'));
    return name.substring(0, maxLen) + '...' + ext;
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const icons = {
        pdf: '📄', doc: '📝', docx: '📝', ppt: '📊', pptx: '📊',
        mp4: '🎬', zip: '🗜️', jpg: '🖼️', jpeg: '🖼️', png: '🖼️',
        gif: '🖼️', bmp: '🖼️', svg: '🖼️', webp: '🖼️', heic: '🖼️', heif: '🖼️',
    };
    return icons[ext] || '📁';
}

function removeModuleField(button) {
    const moduleBlock = button.closest('.module-block');
    moduleBlock.remove();
}
</script>

<!-- FILE DISPLAY NAME -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInputs = document.querySelectorAll('.file-input');
        const fileDataMap = {}; // Store file lists per input index

        fileInputs.forEach((input, index) => {
            fileDataMap[index] = [];

            input.addEventListener('change', function () {
                const previewArea = document.getElementById(`file-preview-${index}`);
                const fileNameSpan = document.getElementById(`file-name-${index}`);
                const files = Array.from(input.files);

                // Add selected files to the stored array
                fileDataMap[index] = fileDataMap[index].concat(files);
                renderFilePreviews(previewArea, fileDataMap[index], index, fileNameSpan);
            });
        });

        function renderFilePreviews(container, fileList, inputIndex, fileNameSpan) {
            container.innerHTML = '';
            container.style.display = 'flex';
            container.style.flexWrap = 'wrap';
            container.style.gap = '10px';

            fileList.forEach((file, i) => {
                const fileBox = document.createElement('div');
                fileBox.style.width = '80px';
                fileBox.style.textAlign = 'center';
                fileBox.style.position = 'relative';

                const icon = document.createElement('div');
                icon.innerHTML = getFileIcon(file.name);
                icon.style.fontSize = '32px';
                icon.style.marginBottom = '5px';

                const fileLabel = document.createElement('div');
                fileLabel.textContent = shortenName(file.name, 10);
                fileLabel.style.fontSize = '12px';
                fileLabel.style.wordBreak = 'break-word';

                const removeBtn = document.createElement('span');
                removeBtn.textContent = '×';
                removeBtn.style.position = 'absolute';
                removeBtn.style.top = '-5px';
                removeBtn.style.right = '2px';
                removeBtn.style.cursor = 'pointer';
                removeBtn.style.color = 'red';
                removeBtn.style.fontWeight = 'bold';
                removeBtn.style.background = 'white';
                removeBtn.style.borderRadius = '50%';
                removeBtn.style.width = '16px';
                removeBtn.style.height = '16px';
                removeBtn.style.display = 'flex';
                removeBtn.style.alignItems = 'center';
                removeBtn.style.justifyContent = 'center';
                removeBtn.style.fontSize = '12px';
                removeBtn.onclick = () => {
                    fileDataMap[inputIndex].splice(i, 1);
                    renderFilePreviews(container, fileDataMap[inputIndex], inputIndex, fileNameSpan);
                };

                fileBox.appendChild(removeBtn);
                fileBox.appendChild(icon);
                fileBox.appendChild(fileLabel);
                container.appendChild(fileBox);
            });

            fileNameSpan.textContent = fileList.length > 0
                ? `${fileList.length} file(s) selected`
                : 'No file chosen';

            // Update the input files
            const dataTransfer = new DataTransfer();
            fileDataMap[inputIndex].forEach(file => dataTransfer.items.add(file));
            document.querySelectorAll('.file-input')[inputIndex].files = dataTransfer.files;
        }

        // Helper: Shorten filename
        function shortenName(name, maxLen) {
            if (name.length <= maxLen) return name;
            const ext = name.substring(name.lastIndexOf('.'));
            return name.substring(0, maxLen) + '...' + ext;
        }

        // Helper: File type icon
        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const icons = {
                pdf: '📄',
                doc: '📝',
                docx: '📝',
                ppt: '📊',
                pptx: '📊',
                mp4: '🎬',
                zip: '🗜️',
                jpg: '🖼️',
                jpeg: '🖼️',
                png: '🖼️',
                gif: '🖼️',
                bmp: '🖼️',
                svg: '🖼️',
                webp: '🖼️',
                heic: '🖼️',
                heif: '🖼️',
            };
            return icons[ext] || '📁';
        }

    });
</script>



<!-- SPECIALIZED TYPE INPUT -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const subjectTypeSelect = document.querySelector('select[name="subjectType"]');
        const specializedGroup = document.getElementById('specializedTypeGroup');
        const specializedSelect = document.querySelector('select[name="specialized_type"]');

        subjectTypeSelect.addEventListener('change', function () {
            if (this.value === 'specialized') {
                specializedGroup.style.display = 'block';
                specializedSelect.setAttribute('required', 'required');
            } else {
                specializedGroup.style.display = 'none';
                specializedSelect.removeAttribute('required');
                specializedSelect.value = ''; // Clear value when hidden
            }
        });

        // Trigger change once to ensure proper state on page load (esp. on validation error refresh)
        subjectTypeSelect.dispatchEvent(new Event('change'));
    });
</script>



<!-- GENERATE SUBJECT ID -->
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
const studentID = `SU${currentYear}${String(currentWeek).padStart(2, '0')}${String(currentDay)}${lastThreeDigits}`;

// Set the value in the input field
document.getElementById('subjectID').value = studentID;
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

    document.addEventListener('DOMContentLoaded', function () {
        const publishDateInput = document.getElementById('publishDate');
        const today = new Date().toISOString().split('T')[0];
        publishDateInput.value = today;
        publishDateInput.min = today;
    });
</script>

<!-- SECTION ID -->
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

<!-- FOR TESTING -->

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Subject details
    document.querySelector('input[name="code"]').value = 'ENG101';
    document.querySelector('input[name="title"]').value = 'English Basics';

    // Select "Academics" as Subject Type
    const subjectTypeSelect = document.querySelector('select[name="subjectType"]');
    subjectTypeSelect.value = 'academics';
    subjectTypeSelect.dispatchEvent(new Event('change'));

    // Select first teacher (if available)
    const teacherSelect = document.querySelector('#teacherID');
    if (teacherSelect.options.length > 1) {
        teacherSelect.selectedIndex = 4;
    }

    // Select first section (if available)
    const sectionSelect = document.querySelector('#sectionID');
    if (sectionSelect.options.length > 1) {
        sectionSelect.selectedIndex = 1;
    }

    // Module
    document.querySelector('input[name="modules[0][title]"]').value = 'Module 1: Grammar Basics';
    document.querySelector('textarea[name="modules[0][description]"]').value = 'An introduction to English grammar.';

    // Announcement
    document.querySelector('input[name="announcement[title]"]').value = 'Welcome to English 101!';
    document.querySelector('input[name="announcement[date]"]').value = new Date().toISOString().split('T')[0]; // today
    document.querySelector('textarea[name="announcement[description]"]').value = 'Please read the first module before next class.';

    console.log('Dummy data filled!');
});
</script>




