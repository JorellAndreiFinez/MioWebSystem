@php
    function hasRole($role) {
        return session()->has('firebase_user') && session('firebase_user.role') === $role;
    }
@endphp

<section class="home-section">
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject-teacher.show-subject', ['subjectId' => $subjectId]) }}" >
                {{ $subject['title'] }}
            </a>
        </div>

        @if(isset($announcementId))
            <div class="breadcrumb-item">
                <a href="{{ route('mio.subject-teacher.announcement', ['subjectId' => $subjectId]) }}">
                    Announcements
                </a>
            </div>
        @else
            <div class="breadcrumb-item active" style="color: #474747;
">
                Announcements
            </div>
        @endif
    </div>
    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

    <main class="main-announcement">
        <div class="announcement-banner">
            <div class="banner">

                <div class="content">
                    <!-- Edit Button (Visible only to the teacher who posted the announcement) -->
                    <div class="edit-btn-container">
                        <button type="button" class="edit-btn"
                            onclick="enableEditMode(this)"
                            data-title="{{ $announcement['title'] }}"
                            data-date="{{ $announcement['date_posted'] }}"
                            data-description="{{ $announcement['description'] }}"
                            data-link="{{ $announcement['link'] ?? '' }}"
                            data-files='@json($announcement["files"] ?? [])'>
                            ✏️
                        </button>

                        <div id="fileDeleteModal" style="display:none; position:fixed; top:30%; left:50%; transform:translate(-50%, -50%); background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.2); z-index:1000;">
                        <p>Are you sure you want to delete this file?</p>
                        <button onclick="confirmFileDelete()" class="btn confirm-btn">Yes, Delete</button>
                        <button onclick="closeFileDeleteModal()" class="btn cancel-btn">Cancel</button>
                    </div>

                         <!-- Trash/Delete button -->
                        <form method="POST" action="{{ route('mio.subject-teacher.deleteTeacherAnnouncement', ['subjectId' => $subjectId, 'announcementId' => $announcementId]) }}" onsubmit="return confirm('Are you sure you want to delete this announcement?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit">🗑️</button>
                        </form>
                    </div>

                    <!-- Edit Form (hidden initially) -->
                    <form method="POST"
                        action="{{ route('mio.subject-teacher.editAnnouncement', ['subjectId' => $subject['subject_id'], 'announcementId' => $announcementId]) }}"
                        enctype="multipart/form-data"
                        id="editAnnouncementForm"
                        style="display:none; max-width: 1000px; width: 120%; margin: 0 auto;">
                        @csrf
                        @method('PUT')

                         <div class="form-container" >
                            <div class="section-content" id="announcement-section">
                            <div class="announcement-block" data-index="0">
                                <div class="form-row">
                                    <div class="form-group wide">
                                        <label>Announcement Title</label>
                                        <input type="text" name="announcements[0][title]" placeholder="Enter Announcement Title" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Publish Date</label>
                                        <input type="date" name="announcements[0][date]" required min="{{ \Carbon\Carbon::now()->toDateString() }}" />
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
                                        <small class="file-note" style="color: darkgrey;">
                                            Accepted formats: PDF, DOC, DOCX, PPT, PPTX, MP4, ZIP, JPG, JPEG, PNG, GIF, BMP, WEBP, SVG, HEIC, HEIF. Max size: 10MB per file.
                                        </small>

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
                        <button type="submit" class="save-btn">Save Announcement</button>
                        <button type="button" onclick="cancelEditMode()" class="cancel-btn-2">Cancel</button>
                         </div>
                    </form>

                     <div id="announcementDisplay">
                        <h3>{{ $announcement['title'] ?? 'No Title' }}</h3>
                        <h6>{{ $announcement['date_posted'] ?? 'No Date' }}</h6>
                        <p>{{ $announcement['description'] ?? 'No description available.' }}</p>

                        @if (!empty($announcement['files']))
                            <div class="row mt-3">
                                @foreach ($announcement['files'] as $file)
                                    @php
                                        $fileName = $file['name'] ?? 'Unknown File';
                                        $fileUrl = $file['url'] ?? '#';

                                        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'heic', 'heif']);

                                        // File type icons (Font Awesome class based on extension)
                                        $icons = [
                                            'pdf' => 'fa-file-pdf',
                                            'doc' => 'fa-file-word',
                                            'docx' => 'fa-file-word',
                                            'ppt' => 'fa-file-powerpoint',
                                            'pptx' => 'fa-file-powerpoint',
                                            'xls' => 'fa-file-excel',
                                            'xlsx' => 'fa-file-excel',
                                            'zip' => 'fa-file-archive',
                                            'rar' => 'fa-file-archive',
                                            'mp4' => 'fa-file-video',
                                            'txt' => 'fa-file-alt',
                                        ];
                                        $fileIcon = $icons[$extension] ?? 'fa-file';
                                    @endphp

                                    <div class="col-md-3 mb-4">
                                        @if ($isImage)
                                            <a href="{{ $fileUrl }}" target="_blank">
                                                <img src="{{ $fileUrl }}" alt="{{ $fileName }}" class="img-fluid rounded shadow" style="height: 200px; object-fit: cover; width: 100%;">
                                            </a>
                                            <p class="mt-2 text-center small text-muted">{{ $fileName }}</p>
                                        @else
                                            <div class="d-flex flex-column align-items-center border rounded p-3 bg-white text-center shadow-sm h-100">
                                                <i class="fas {{ $fileIcon }} fa-3x text-primary mb-2"></i>
                                                <p class="small text-break mb-2">{{ $fileName }}</p>
                                                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary btn-sm mt-auto">Download</a>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @elseif (!empty($announcement['link']))
                            <div class="mt-3">
                                <img src="{{ $announcement['link'] }}" alt="Announcement Image" class="img-fluid rounded" style="max-height: 300px;">
                            </div>
                        @endif
                    </div>


                    <!-- Delete Button (Visible to authorized users only) -->
                    <!-- <div class="delete-btn-container">
                        <form method="POST" action="#">
                            <button type="submit" class="delete-btn">🗑 Delete Announcement</button>
                        </form>
                    </div> -->

                    <!-- Toggle Reply Button -->
                    <button class="toggle-reply-btn" onclick="toggleReply()">💬 Reply</button>

                    <div class="reply-section" id="replySection" style="display: none;">
                        <form method="POST" action="{{ route('mio.subject-teacher.storeReply', ['subjectId' => $subject['subject_id'], 'announcementId' => $announcementId]) }}">
                            @csrf
                            <textarea name="reply" placeholder="Write your reply..." required></textarea>
                            <button type="submit">Send Reply</button>
                            <button type="button" class="close-reply-btn" onclick="toggleReply()">Close</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <!-- Replies Container -->
        <div class="all-replies">
            <h4>Replies</h4>

            @if(count($announcement['replies'] ?? []) > 0)
                @foreach($announcement['replies'] as $replyId => $reply)
                <div class="reply-item">
                    <div class="reply-header">
                        <strong>{{ $reply['user_name'] }}</strong> • <small>{{ $reply['timestamp'] }}</small>
                        @if (hasRole('admin') || session('firebase_user.uid') === $reply['user_id'])
                            <form action="{{ route('mio.subject-teacher.deleteReply', ['subjectId' => $subject['subject_id'], 'announcementId' => $announcementId, 'replyId' => $replyId]) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete Reply">
                                    <i class="fas fa-trash-alt"></i> <!-- Trash icon -->
                                </button>
                            </form>
                        @endif
                    </div>
                    <p>{{ $reply['message'] }}</p>
                </div>
            @endforeach

            @else
                <p>No replies yet.</p>
            @endif
        </div>


    </main>
</section>

<!-- SCRIPTS -->

<script>
    let announcementCount = 0;
    const announcementFileDataMap = {};

    const existingFilesMap = {}; // for files already uploaded (from Firebase)

   const undoStack = {};


    // Initial setup for edit form (index 0)
    document.addEventListener('DOMContentLoaded', function () {
        const index = 0;
        const fileInput = document.getElementById(`announcement-file-upload-${index}`);
        const fileNameSpan= document.getElementById(`announcement-file-name-${index}`);

        announcementFileDataMap[index] = [];

        if (fileInput) {
            fileInput.addEventListener('change', () => {
                const newFiles = Array.from(fileInput.files);

                // ⬇️ APPEND INSTEAD OF REPLACING
                announcementFileDataMap[index] =
                    (announcementFileDataMap[index] || []).concat(newFiles);

                renderAnnouncementPreviewsCombined(index);      // <-- always refresh combined view
                updateFileInput(fileInput, announcementFileDataMap[index]);
            });
        }
    });

    // Add announcement block dynamically
    function addAnnouncement() {
        announcementCount++;
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
                    <input type="file" name="announcements[${announcementCount}][files][]" id="announcement-file-upload-${announcementCount}" class="file-input" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.zip,.jpg,.jpeg,.png,.gif,.bmp,.webp,.svg,.heic,.heif" />
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

        const fileInput = wrapper.querySelector(`#announcement-file-upload-${announcementCount}`);
        const preview = wrapper.querySelector(`#announcement-preview-${announcementCount}`);
        const fileNameSpan = wrapper.querySelector(`#announcement-file-name-${announcementCount}`);

        announcementFileDataMap[announcementCount] = [];

         fileInput.addEventListener('change', () => {
            const newFiles = Array.from(fileInput.files);

            announcementFileDataMap[announcementCount] =
                (announcementFileDataMap[announcementCount] || []).concat(newFiles);

            renderAnnouncementPreviewsCombined(announcementCount);
            updateFileInput(fileInput, announcementFileDataMap[announcementCount]);
        });
    }

    // Render file preview boxes
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
                 fileToDeleteIndex = index;
                fileToDeleteType = 'new';
                fileToDeleteIndexInArray = i;
                document.getElementById('fileDeleteModal').style.display = 'block';
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


    // Helper to re-assign selected files
    function updateFileInput(inputEl, files) {
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        inputEl.files = dataTransfer.files;
    }

    // Utility: Shorten long filenames
    function shortenName(name, maxLen) {
        if (name.length <= maxLen) return name;
        const ext = name.substring(name.lastIndexOf('.'));
        return name.substring(0, maxLen) + '...' + ext;
    }

    // Utility: Choose emoji icon for file type
    function getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const icons = {
            pdf: '📄', doc: '📝', docx: '📝', ppt: '📊', pptx: '📊',
            mp4: '🎬', zip: '🗜️', jpg: '🖼️', jpeg: '🖼️', png: '🖼️',
            gif: '🖼️', bmp: '🖼️', svg: '🖼️', webp: '🖼️', heic: '🖼️', heif: '🖼️',
        };
        return icons[ext] || '📁';
    }

    // Enable edit mode and populate data
    function enableEditMode(button) {
        const form = document.getElementById('editAnnouncementForm');
        form.style.display = 'block';

        const index = 0;
        const title = button.getAttribute('data-title');
        const date = button.getAttribute('data-date');
        const description = button.getAttribute('data-description');
        const link = button.getAttribute('data-link');
        const files = JSON.parse(button.getAttribute('data-files') || '[]');

        form.querySelector('input[name="announcements[0][title]"]').value = title;
        form.querySelector('input[name="announcements[0][date]"]').value = date;
        form.querySelector('textarea[name="announcements[0][description]"]').value = description;
        form.querySelector('input[name="announcements[0][link]"]').value = link;

        // Store existing files
        existingFilesMap[index] = [...files];
        announcementFileDataMap[index] = [];

        renderAnnouncementPreviewsCombined(index);
        document.getElementById('announcementDisplay').style.display = 'none';
    }


    // Cancel editing and revert
    function cancelEditMode() {
        document.getElementById('editAnnouncementForm').style.display = 'none';
        document.getElementById('announcementDisplay').style.display = 'block';
    }

    function renderAnnouncementPreviewsCombined(index) {
        const container = document.getElementById(`announcement-preview-${index}`);
        const fileNameSpan = document.getElementById(`announcement-file-name-${index}`);
        const inputEl = document.getElementById(`announcement-file-upload-${index}`);

        container.innerHTML = '';
        container.style.display = 'flex';

        // Existing files from Firebase (URLs)
        (existingFilesMap[index] || []).forEach((file, i) => {
            const url = typeof file === 'string' ? file : file.url;
            const name = typeof file === 'string' ? 'Attachment' : file.name;

            const fileBox = document.createElement('div');
            fileBox.style.width = '80px';
            fileBox.style.textAlign = 'center';
            fileBox.style.position = 'relative';

            const icon = document.createElement('div');
            icon.innerHTML = getFileIcon(name);
            icon.style.fontSize = '32px';
            icon.style.marginBottom = '5px';

            const label = document.createElement('div');
            label.textContent = shortenName(name, 10);
            label.style.fontSize = '12px';

            const downloadLink = document.createElement('a');
            downloadLink.href = url;
            downloadLink.target = '_blank';
            downloadLink.appendChild(icon);

            const removeBtn = document.createElement('span');
            removeBtn.textContent = '×';
            removeBtn.style = 'position: absolute; top: -5px; right: 2px; cursor: pointer; color: red; font-weight: bold; background: white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; font-size: 12px;';
            removeBtn.onclick = () => {
                const removedFile = existingFilesMap[index].splice(i, 1)[0];

                if (!undoStack[index]) undoStack[index] = [];
                undoStack[index].push({ type: 'existing', file: removedFile });

                // ADD THIS to track deleted files
                const removedFilesInput = document.createElement('input');
                removedFilesInput.type = 'hidden';
                removedFilesInput.name = `announcements[${index}][removed_files][]`;
                removedFilesInput.value = typeof removedFile === 'string' ? removedFile : removedFile.url;
                document.getElementById('editAnnouncementForm').appendChild(removedFilesInput);

                renderAnnouncementPreviewsCombined(index);
                showUndoButton(index); // optional if you want undo
            };


            fileBox.appendChild(removeBtn);
            fileBox.appendChild(downloadLink);
            fileBox.appendChild(label);
            container.appendChild(fileBox);
        });

        // Newly selected local files
        (announcementFileDataMap[index] || []).forEach((file, i) => {
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
                const removedFile = announcementFileDataMap[index].splice(i, 1)[0];

                if (!undoStack[index]) undoStack[index] = [];
                undoStack[index].push({ type: 'new', file: removedFile });

                renderAnnouncementPreviewsCombined(index);
                updateFileInput(inputEl, announcementFileDataMap[index]);
                showUndoButton(index);
            };


            fileBox.appendChild(removeBtn);
            fileBox.appendChild(icon);
            fileBox.appendChild(label);
            container.appendChild(fileBox);
        });

        const totalExisting = (existingFilesMap[index] || []).length;
            const totalNew      = (announcementFileDataMap[index] || []).length;
            fileNameSpan.textContent =
                totalNew ? `${totalExisting + totalNew} file(s) attached` : 'No file chosen';
        }

    function showUndoButton(index) {
        let container = document.getElementById(`announcement-preview-${index}`);
        let existing = container.querySelector('.undo-btn');
        if (existing) existing.remove();

        const undoBtn = document.createElement('button');
        undoBtn.className = 'undo-btn';
        undoBtn.textContent = 'Undo';
        undoBtn.style = 'margin-top: 10px; background: #eee; border: 1px solid #ccc; padding: 4px 8px; cursor: pointer;';

        undoBtn.onclick = () => {
            if (undoStack[index] && undoStack[index].length > 0) {
                const lastRemoved = undoStack[index].pop();
                if (lastRemoved.type === 'existing') {
                    existingFilesMap[index].push(lastRemoved.file);
                } else {
                    announcementFileDataMap[index].push(lastRemoved.file);
                    const inputEl = document.getElementById(`announcement-file-upload-${index}`);
                    updateFileInput(inputEl, announcementFileDataMap[index]);
                }
                renderAnnouncementPreviewsCombined(index);

                if (undoStack[index].length === 0) undoBtn.remove();
            }
        };

        container.appendChild(undoBtn);
    }


</script>

<script>
document.getElementById('editAnnouncementForm').addEventListener('submit', function () {
    const index = 0;
    const retainedFiles = existingFilesMap[index] || [];

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'retained_existing_files';
    input.value = JSON.stringify(retainedFiles);

    this.appendChild(input);
});
</script>

<script>
    function toggleReply() {
        const replySection = document.getElementById('replySection');
        replySection.style.display = replySection.style.display === 'none' ? 'block' : 'none';
    }
</script>

<script>
    document.querySelector('#announcement-file-upload-0').addEventListener('change', function (e) {
        const files = e.target.files;
        const maxSizeMB = 10;

        for (const file of files) {
            if (file.size > maxSizeMB * 1024 * 1024) {
                alert(`"${file.name}" exceeds the ${maxSizeMB}MB limit.`);
                e.target.value = ''; // Clear selection
                document.getElementById('announcement-file-name-0').textContent = 'No file chosen';
                return;
            }
        }

        // Display selected file names
        const names = Array.from(files).map(f => f.name).join(', ');
        document.getElementById('announcement-file-name-0').textContent = names;
    });
</script>

