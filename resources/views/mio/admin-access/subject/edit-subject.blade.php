@php
function shortenName($name, $maxLen) {
    if (strlen($name) <= $maxLen) return $name;
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    return substr($name, 0, $maxLen) . '...' . $ext;
}

function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'pdf' => '📄', 'doc' => '📝', 'docx' => '📝', 'ppt' => '📊', 'pptx' => '📊',
        'mp4' => '🎬', 'zip' => '🗜️', 'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️',
        'gif' => '🖼️', 'bmp' => '🖼️', 'svg' => '🖼️', 'webp' => '🖼️', 'heic' => '🖼️', 'heif' => '🖼️',
    ];
    return $icons[$ext] ?? '📁';
}
@endphp


<section class="home-section">
<div class="text">Edit Subject</div>
<div class="teacher-container">
<pre>{{ json_encode($sectionSchedules, JSON_PRETTY_PRINT) }}</pre>

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

                       <div class="form-group">
                        <label>Subject Type <span style="color: red">*</span></label>
                        <select name="subjectType" required>
                            <option value="">Select Subject Type</option>
                            <option value="academics" {{ ($editdata['subjectType'] ?? '') == 'academics' ? 'selected' : '' }}>Academics</option>
                            <option value="specialized" {{ ($editdata['subjectType'] ?? '') == 'specialized' ? 'selected' : '' }}>Specialized</option>
                        </select>
                    </div>
                    </div>

                    <hr>

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

                <hr>
                <!-- timetable preview of that section -->
             <div class="form-row">
                <div class="form-group wide">
                    <label>Timetable Preview</label>
                    <div id="timetable-preview" style="overflow-x: auto;">
                        <em>Select a section to preview its timetable.</em>
                    </div>
                </div>
            </div>
                <!-- schedule information -->
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <input type="checkbox"  name="sameTimeToggle"  id="sameTimeToggle" checked />
                            Use same time for all selected days
                        </label>
                    </div>
                </div>

                <div id="common-time" class="form-row">
                    <div class="form-group">
                        <label>Start Time <span style="color: red">*</span></label>
                        <input type="time" name="common_start_time" />
                    </div>

                    <div class="form-group">
                        <label>End Time <span style="color: red">*</span></label>
                        <input type="time" name="common_end_time" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label>Occurrences (Days of Week) <span style="color: red; font-weight:700">*</span></label>
                        <select name="occurrences[]" id="occurrences" multiple required style="height: 170px;">
                            @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                                <option value="{{ $day }}"
                                    {{ in_array($day, $scheduleOccurrences ?? []) ? 'selected' : '' }}>
                                    {{ $day }}
                                </option>
                            @endforeach
                        </select>

                        <small>Hold Ctrl (or Cmd) to select multiple days.</small>
                    </div>
                </div>

                <!-- Individual day time inputs will appear here -->
                <div id="individual-times"></div>
                </div>

                <!-- TIME TABLE -->

    <script>
        const sectionSchedules = @json($sectionSchedules);
         const existingSchedule = @json($editdata['schedule'] ?? []);

        // Define time slots
        const timeSlots = [
            '06:00', '07:00',
            '07:00', '08:00',
            '08:00', '09:00',
            '09:00', '10:00',
            '10:00', '11:00',
            '11:00', '12:00',
            '12:00', '13:00',
            '13:00', '14:00',
            '14:00', '15:00',
            '15:00', '16:00',
            '16:00', '17:00',
            '17:00', '18:00',
            '18:00', '19:00'
        ];

        function parseTime(timeStr) {
            const [hours, minutes] = timeStr.split(':').map(Number);
            const date = new Date();
            date.setHours(hours, minutes, 0, 0);
            return date;
        }


        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        function timeInRange(start, end, check) {
            return check >= start && check < end;
        }

        function rangesOverlap(startA, endA, startB, endB) {
            const aStart = parseTime(startA);
            const aEnd = parseTime(endA);
            const bStart = parseTime(startB);
            const bEnd = parseTime(endB);

            return aStart < bEnd && aEnd > bStart;
        }



        document.getElementById('sectionID').addEventListener('change', function () {
            const sectionId = this.value;
             console.log('Selected section:', sectionId);
            console.log('Available keys in sectionSchedules:', Object.keys(sectionSchedules));
            const container = document.getElementById('timetable-preview');
            container.innerHTML = '';

            if (!sectionSchedules[sectionId] || sectionSchedules[sectionId].length === 0) {
                container.innerHTML = '<em>No existing schedule for this section.</em>';
                return;
            }

            const table = document.createElement('table');
            table.style.width = '100%';
            table.style.borderCollapse = 'collapse';
            table.style.textAlign = 'center';

            // Header Row
            const headerRow = document.createElement('tr');
            const emptyTh = document.createElement('th');
            emptyTh.textContent = 'Time';
            emptyTh.style.padding = '10px';
            emptyTh.style.border = '1px solid #ccc';
            emptyTh.style.backgroundColor = '#f0f0f0';
            headerRow.appendChild(emptyTh);

            days.forEach(day => {
                const th = document.createElement('th');
                th.textContent = day;
                th.style.padding = '10px';
                th.style.border = '1px solid #ccc';
                th.style.backgroundColor = '#f0f0f0';
                headerRow.appendChild(th);
            });

            table.appendChild(headerRow);

            // Row for each time slot
            for (let i = 0; i < timeSlots.length; i += 2) {
                const row = document.createElement('tr');

                const timeRange = `${timeSlots[i]} - ${timeSlots[i + 1]}`;
                const timeCell = document.createElement('td');
                timeCell.textContent = timeRange;
                timeCell.style.border = '1px solid #ccc';
                timeCell.style.padding = '10px';
                timeCell.style.fontWeight = 'bold';
                row.appendChild(timeCell);

                days.forEach(day => {
                    const cell = document.createElement('td');
                    cell.style.border = '1px solid #ccc';
                    cell.style.padding = '8px';
                    cell.style.minWidth = '120px';
                    cell.style.verticalAlign = 'top';

                    const slotStart = timeSlots[i];
                    const slotEnd = timeSlots[i + 1];

                    const subjects = sectionSchedules[sectionId].filter(subject => {
                        if (!subject.start_time || !subject.end_time || !subject.occurrences) return false;

                        const inDay = subject.occurrences.includes(day);
                        const overlaps = rangesOverlap(subject.start_time, subject.end_time, slotStart, slotEnd);
                        return inDay && overlaps;
                    });



                    if (subjects.length > 0) {
                        subjects.forEach(subject => {
                            const div = document.createElement('div');
                            div.textContent = subject.title;
                            div.style.backgroundColor = '#b3d9ff';
                            div.style.borderRadius = '4px';
                            div.style.padding = '4px';
                            div.style.marginBottom = '4px';
                            div.style.fontSize = '0.9em';
                            cell.appendChild(div);
                        });
                    } else {
                        cell.innerHTML = '<span style="color: #aaa;">—</span>';
                    }

                    row.appendChild(cell);
                });

                table.appendChild(row);
            }

            container.appendChild(table);
        });
    </script>

   <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sameTimeToggle = document.getElementById('sameTimeToggle');
            const occurrencesSelect = document.getElementById('occurrences');
            const commonStartTimeInput = document.querySelector('input[name="common_start_time"]');
            const commonEndTimeInput = document.querySelector('input[name="common_end_time"]');
            const individualTimesContainer = document.getElementById('individual-times');
            const sectionSelect = document.getElementById('sectionID');

            function updateTimeFields() {
                const selectedDays = Array.from(occurrencesSelect.selectedOptions).map(opt => opt.value);
                individualTimesContainer.innerHTML = '';

                selectedDays.forEach(day => {
                    const row = document.createElement('div');
                    row.classList.add('form-row');

                    row.innerHTML = `
                        <div class="form-group">
                            <label>${day} Start</label>
                            <input type="time" name="day_times[${day}][start]" />
                        </div>
                        <div class="form-group">
                            <label>${day} End</label>
                            <input type="time" name="day_times[${day}][end]" />
                        </div>
                    `;
                    individualTimesContainer.appendChild(row);
                });
            }

            sameTimeToggle.addEventListener('change', () => {
                document.getElementById('common-time').style.display = sameTimeToggle.checked ? 'flex' : 'none';
                if (!sameTimeToggle.checked) {
                    updateTimeFields();
                } else {
                    individualTimesContainer.innerHTML = '';
                }
            });

            occurrencesSelect.addEventListener('change', () => {
                if (!sameTimeToggle.checked) {
                    updateTimeFields();
                }
            });

            // ----- Preload existing schedule (edit mode)
            if (existingSchedule?.occurrence) {
                const days = Object.keys(existingSchedule.occurrence);
                const allTimes = Object.values(existingSchedule.occurrence).map(o => JSON.stringify(o));
                const allSame = new Set(allTimes).size === 1;

                Array.from(occurrencesSelect.options).forEach(option => {
                    option.selected = days.includes(option.value);
                });

                // trigger change event
                occurrencesSelect.dispatchEvent(new Event('change'));

                if (allSame) {
                    sameTimeToggle.checked = true;
                    document.getElementById('common-time').style.display = 'flex';
                    const time = existingSchedule.occurrence[days[0]];
                    commonStartTimeInput.value = time.start;
                    commonEndTimeInput.value = time.end;
                } else {
                    sameTimeToggle.checked = false;
                    document.getElementById('common-time').style.display = 'none';
                    updateTimeFields();
                    // Wait until inputs are rendered before setting values
                    setTimeout(() => {
                        days.forEach(day => {
                            const time = existingSchedule.occurrence[day];
                            const startInput = document.querySelector(`input[name="day_times[${day}][start]"]`);
                            const endInput = document.querySelector(`input[name="day_times[${day}][end]"]`);
                            if (startInput) startInput.value = time.start;
                            if (endInput) endInput.value = time.end;
                        });
                    }, 10);
                }

                // Auto-trigger timetable render
                if (sectionSelect.value) {
                    sectionSelect.dispatchEvent(new Event('change'));
                }
            }
        });
        </script>














                 <!-- Modules Section -->
                <div class="section-header">Modules</div>
                <div class="section-content" id="module-section">
                     @php $index = 0; @endphp
                        @foreach($modules as $key => $module)
                            <div class="form-row module-row" data-index="{{ $index }}">
                                <div class="form-group">
                                    <label>Module Title <span style="color: red">*</span></label>
                                    <input type="text" name="modules[{{ $index }}][title]" value="{{ $module['title'] ?? '' }}" required />
                                </div>
                            </div>

                            <div class="form-row module-row" data-index="{{ $index }}">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="modules[{{ $index }}][description]">{{ $module['description'] ?? '' }}</textarea>
                                </div>
                            </div>

                            <!-- File Previews -->
                            <div class="form-row">
                                <div class="form-group wide">
                                    <label>File Previews</label>
                                    <div id="file-preview-{{ $index }}" class="file-preview-area" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>

                                    <hr width="100%">

                                    @if(isset($module['files']) && is_array($module['files']))
                                        <div class="existing-files" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:10px;" id="existing-files-{{ $index }}">
                                            @foreach($module['files'] as $file)
                                                <div class="file-box" style="width:80px;text-align:center;position:relative;" data-file-path="{{ $file['path'] }}">
                                                    <a href="{{ $file['url'] }}" target="_blank" style="text-decoration:none;">
                                                        <div style="font-size:32px;margin-bottom:5px;">{!! getFileIcon($file['name']) !!}</div>
                                                        <div style="font-size:12px;word-break:break-word;">{{ shortenName($file['name'], 10) }}</div>
                                                    </a>
                                                    <span class="remove-saved-file"
                                                        style="position:absolute;top:-5px;right:2px;cursor:pointer;color:red;font-weight:bold;background:white;border-radius:50%;width:16px;height:16px;display:flex;align-items:center;justify-content:center;font-size:12px"
                                                        onclick="removeExistingFile(this, {{ $index }})">×</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="modules[{{ $index }}][removed_files]" id="removed-files-{{ $index }}" value="">
                                    @endif

                                </div>
                            </div>


                            <!-- Upload Files -->
                            <div class="form-row">
                                <div class="form-group custom-file-upload">
                                    <label>Upload Files</label>
                                    <label for="file-upload-{{ $index }}" class="file-label">
                                        <span class="upload-icon">📁</span> Choose Files
                                    </label>
                                    <input
                                        type="file"
                                        name="modules[{{ $index }}][files][]"
                                        id="file-upload-{{ $index }}"
                                        class="file-input"
                                        multiple
                                        accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.heic,.heif"
                                    />
                                    <span class="file-name" id="file-name-{{ $index }}">No file chosen</span>
                                </div>
                            </div>

                            <!-- External Link -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Or Provide External Link</label>
                                    <input
                                        type="url"
                                        name="modules[{{ $index }}][external_link]"
                                        value="{{ $module['external_link'] ?? '' }}"
                                    />
                                </div>
                            </div>

                            @php $index++; @endphp
                        @endforeach


                    <!-- Add Module Button -->
                    <button type="button" class="btn add-btn" onclick="addModuleField()">+ Add Module</button>
                </div>


                <!-- Announcement Section -->
            <div class="section-header">Announcement
                <button type="button" onclick="addAnnouncement()" class="btn primary-btn" style="color: white; font-weight: 800; font-size: 1.5rem;"> + </button>

            </div>
            <div class="section-content" id="announcement-section">
            @php $aIndex = 0; @endphp
            @foreach($announcements as $key => $announcement)
                <div class="announcement-block" data-index="{{ $aIndex }}">
                    <div class="form-row">
                        <div class="form-group wide">
                            <label>Announcement Title</label>
                            <input type="text" name="announcements[{{ $aIndex }}][title]" value="{{ $announcement['title'] }}" required />
                        </div>

                        <div class="form-group">
                            <label>Publish Date</label>
                            <input type="date" name="announcements[{{ $aIndex }}][date]" value="{{ $announcement['date_posted'] }}" required />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group wide">
                            <label>Announcement Description</label>
                            <textarea name="announcements[{{ $aIndex }}][description]" required>{{ $announcement['description'] }}</textarea>
                        </div>
                    </div>

                    <!-- File Previews (Saved) -->
                    <div class="form-row">
                        <div class="form-group wide">
                            <label>Attached Files (Optional)</label>
                            <div id="announcement-preview-{{ $aIndex }}" class="file-preview-area" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>

                            <hr>


                            @if(isset($announcement['files']) && is_array($announcement['files']))
                                <div class="existing-files" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:10px;" id="existing-announcement-files-{{ $aIndex }}">
                                    @foreach($announcement['files'] as $file)
                                        <div class="file-box" style="width:80px;text-align:center;position:relative;" data-file-path="{{ $file['path'] }}">
                                            <a href="{{ $file['url'] }}" target="_blank" style="text-decoration:none;">
                                                <div style="font-size:32px;margin-bottom:5px;">{!! getFileIcon($file['name']) !!}</div>
                                                <div style="font-size:12px;word-break:break-word;">{{ shortenName($file['name'], 10) }}</div>
                                            </a>
                                            <span class="remove-saved-file"
                                                style="position:absolute;top:-5px;right:2px;cursor:pointer;color:red;font-weight:bold;background:white;border-radius:50%;width:16px;height:16px;display:flex;align-items:center;justify-content:center;font-size:12px"
                                                onclick="removeExistingFile(this, {{ $aIndex }}, 'announcement')">×</span>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="announcements[{{ $aIndex }}][removed_files]" id="removed-announcement-files-{{ $aIndex }}" value="">
                            @endif
                        </div>
                    </div>

                    <!-- Upload Input -->
                    <div class="form-row">
                        <div class="form-group custom-file-upload">
                            <label>Upload Files</label>
                            <label for="announcement-file-upload-{{ $aIndex }}" class="file-label">
                                <span class="upload-icon">📁</span> Choose Files
                            </label>
                            <input
                                type="file"
                                name="announcements[{{ $aIndex }}][files][]"
                                id="announcement-file-upload-{{ $aIndex }}"
                                class="file-input"
                                multiple
                                accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.heic,.heif"
                            />
                            <span class="file-name" id="announcement-file-name-{{ $aIndex }}">No file chosen</span>
                        </div>
                    </div>

                    <!-- Link -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Or External Link (Image/Video)</label>
                            <input type="url" name="announcements[{{ $aIndex }}][link]" value="{{ $announcement['link'] }}" />
                        </div>
                    </div>
                </div>

                @php $aIndex++; @endphp
            @endforeach

            </div>
 </form>
</div>

</section>

<!-- SCRIPTS -->

        <!-- ADD MODULE SECTION -->
        <!-- FILE DISPLAY NAME -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let moduleCount = {{ count($editdata['modules'] ?? []) }};
                const fileDataMap = {}; // Shared file map for all modules

                // ==================== MAIN: ADD MODULE FIELD ====================
                function addModuleField() {
                    const section = document.getElementById("module-section");

                    const wrapper = document.createElement("div");
                    wrapper.className = "module-block";
                    wrapper.dataset.index = moduleCount;

                    wrapper.innerHTML = `
                        <div class="form-row module-row">
                            <div class="form-group">
                                <label>Module Title <span style="color: red">*</span></label>
                                <input type="text" name="modules[${moduleCount}][title]" placeholder="e.g. Module ${moduleCount + 1}" required />
                            </div>
                        </div>
                        <div class="form-row module-row">
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
                                <input type="file" name="modules[${moduleCount}][files][]" id="file-upload-${moduleCount}" class="file-input" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.heic,.heif" />
                                <span class="file-name" id="file-name-${moduleCount}">No file chosen</span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Or Provide External Link</label>
                                <input type="url" name="modules[${moduleCount}][external_link]" placeholder="https://example.com/file.pdf" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <button type="button" class="btn cancel-btn" onclick="removeModuleField(this)">Remove</button>
                            </div>
                        </div>
                    `;

                    section.insertBefore(wrapper, section.querySelector(".add-btn"));

                    const index = moduleCount;
                    const input = wrapper.querySelector(`#file-upload-${index}`);
                    const previewArea = wrapper.querySelector(`#file-preview-${index}`);
                    const fileNameSpan = wrapper.querySelector(`#file-name-${index}`);
                    fileDataMap[index] = [];

                    input.addEventListener('change', function () {
                        const files = Array.from(input.files).map(f => ({ file: f, name: f.name, url: null, isSaved: false }));
                        fileDataMap[index] = fileDataMap[index].concat(files);
                        renderFilePreviews(previewArea, fileDataMap[index], index, fileNameSpan);
                    });

                    moduleCount++;
                }

                // ==================== INIT FOR EXISTING INPUTS ====================
                document.querySelectorAll('.file-input').forEach((input, index) => {
                    const previewArea = document.getElementById(`file-preview-${index}`);
                    const fileNameSpan = document.getElementById(`file-name-${index}`);
                    fileDataMap[index] = [];

                    input.addEventListener('change', function () {
                        const files = Array.from(input.files).map(f => ({ file: f, name: f.name, url: null, isSaved: false }));
                        fileDataMap[index] = fileDataMap[index].concat(files);
                        renderFilePreviews(previewArea, fileDataMap[index], index, fileNameSpan);
                    });
                });

                // ==================== UTILITY: RENDER PREVIEWS ====================
                function renderFilePreviews(container, fileList, inputIndex, fileNameSpan) {
                    container.innerHTML = '';
                    container.style.display = 'flex';

                    fileList.forEach((file, i) => {
                        const fileBox = document.createElement('div');
                        fileBox.style = 'width:80px;text-align:center;position:relative';

                        const icon = document.createElement('div');
                        icon.innerHTML = getFileIcon(file.name);
                        icon.style = 'font-size:32px;margin-bottom:5px';

                        const fileLabel = document.createElement('div');
                        fileLabel.textContent = shortenName(file.name, 10);
                        fileLabel.style = 'font-size:12px;word-break:break-word';

                        const removeBtn = document.createElement('span');
                        removeBtn.textContent = '×';
                        removeBtn.style = 'position:absolute;top:-5px;right:2px;cursor:pointer;color:red;font-weight:bold;background:white;border-radius:50%;width:16px;height:16px;display:flex;align-items:center;justify-content:center;font-size:12px';
                        removeBtn.onclick = () => {
                            fileDataMap[inputIndex].splice(i, 1);
                            renderFilePreviews(container, fileDataMap[inputIndex], inputIndex, fileNameSpan);
                        };

                        if (file.isSaved && file.url) {
                            const link = document.createElement('a');
                            link.href = file.url;
                            link.target = '_blank';
                            link.append(icon, fileLabel);
                            fileBox.append(link);
                        } else {
                            fileBox.append(icon, fileLabel);
                        }

                        fileBox.appendChild(removeBtn);
                        container.appendChild(fileBox);
                    });

                    fileNameSpan.textContent = fileList.length > 0
                        ? `${fileList.length} file(s) selected`
                        : 'No file chosen';

                    const dataTransfer = new DataTransfer();
                    fileDataMap[inputIndex].forEach(file => {
                        if (!file.isSaved && file.file) dataTransfer.items.add(file.file);
                    });

                    const fileInput = document.querySelector(`#file-upload-${inputIndex}`);
                    if (fileInput) fileInput.files = dataTransfer.files;
                }

                // ==================== UTILITY: SHORTEN NAME ====================
                function shortenName(name, maxLen) {
                    if (name.length <= maxLen) return name;
                    const ext = name.substring(name.lastIndexOf('.'));
                    return name.substring(0, maxLen) + '...' + ext;
                }

                // ==================== UTILITY: FILE ICON ====================
                function getFileIcon(filename) {
                    const ext = filename.split('.').pop().toLowerCase();
                    const icons = {
                        pdf: '📄', doc: '📝', docx: '📝', ppt: '📊', pptx: '📊',
                        mp4: '🎬', zip: '🗜️', jpg: '🖼️', jpeg: '🖼️', png: '🖼️',
                        gif: '🖼️', bmp: '🖼️', svg: '🖼️', webp: '🖼️', heic: '🖼️', heif: '🖼️',
                    };
                    return icons[ext] || '📁';
                }

                // ==================== REMOVE MODULE BLOCK ====================
                window.removeModuleField = function(button) {
                    const moduleBlock = button.closest('.module-block');
                    moduleBlock.remove();
                };

                // Expose to global for add button
                window.addModuleField = addModuleField;
            });
        </script>

        <script>
            function removeExistingFile(button, index) {
                const fileBox = button.closest('.file-box');
                const filePath = fileBox.getAttribute('data-file-path');
                const hiddenInput = document.getElementById(`removed-files-${index}`);

                if (filePath) {
                    const removedList = hiddenInput.value ? hiddenInput.value.split(',') : [];
                    if (!removedList.includes(filePath)) {
                        removedList.push(filePath);
                        hiddenInput.value = removedList.join(',');
                    }
                }

                fileBox.remove();
            }
        </script>


 <!-- ANNOUNCEMENT -->
            <script>
            document.addEventListener('DOMContentLoaded', () => {
                const fileInput = document.getElementById('announcement-file-upload-0');
                const preview = document.getElementById('announcement-preview-0');
                const fileNameSpan = document.getElementById('announcement-file-name-0');

                announcementFileDataMap[0] = [];

                fileInput.addEventListener('change', () => {
                    const newFiles = Array.from(fileInput.files);
                    announcementFileDataMap[0] = (announcementFileDataMap[0] || []).concat(newFiles);
                    renderAnnouncementPreviews(preview, announcementFileDataMap[0], 0, fileNameSpan, fileInput);

                });
            });
            </script>

           
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

            <script>
                let announcementCount = {{ count($announcements) }};
                const announcementFileDataMap = {};

                @foreach($announcements as $i => $announcement)
                    announcementFileDataMap[{{ $i }}] = [];

                    document.addEventListener('DOMContentLoaded', () => {
                        const fileInput = document.getElementById('announcement-file-upload-{{ $i }}');
                        const preview = document.getElementById('announcement-preview-{{ $i }}');
                        const fileNameSpan = document.getElementById('announcement-file-name-{{ $i }}');

                        fileInput.addEventListener('change', () => {
                            const files = Array.from(fileInput.files);
                            announcementFileDataMap[{{ $i }}] = files;
                            renderAnnouncementPreviews(preview, files, {{ $i }}, fileNameSpan, fileInput);
                        });
                    });
                @endforeach
            </script>

            <script>
            function removeExistingFile(el, index, type = 'module') {
                const box = el.closest('.file-box');
                const path = box.getAttribute('data-file-path');
                box.remove();

                const inputId = type === 'module' ? `removed-files-${index}` : `removed-announcement-files-${index}`;
                const input = document.getElementById(inputId);
                const paths = input.value ? input.value.split(',') : [];
                paths.push(path);
                input.value = paths.join(',');
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

