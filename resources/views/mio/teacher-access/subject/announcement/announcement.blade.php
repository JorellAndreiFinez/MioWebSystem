
<!-- File Delete Confirmation Modal -->
<div class="modal-overlay" id="fileDeleteModal" style="display: none;">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Remove File</span>
    </div>
    <div class="modal-body">
      <p id="fileDeleteMessage">Are you sure you want to remove this file? This action cannot be undone.</p>
    </div>
    <div class="modal-footer">
      <button class="btn cancel-btn" onclick="closeFileDeleteModal()">Cancel</button>
      <button class="btn confirm-btn" onclick="confirmFileDelete()">Confirm</button>
    </div>
  </div>
</div>


<!-- Announcement Modal -->
<div id="announcementModal" class="announcement-modal" style="display: none;">
    <div class="announcement-modal-content">
        <span class="close" onclick="closeAnnouncementModal()">&times;</span>
         <form method="POST" action="{{ route('mio.subject-teacher.announcement-store', ['subjectId' => $subjectId]) }}" enctype="multipart/form-data">
            @csrf

            <div class="section-header">
                Announcement
            </div>
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
            <div class="modal-footer" style="margin-top: 1rem;">
                <button type="submit" class="btn primary-btn">Post Announcement</button>
            </div>

        </form>
    </div>
</div>


<section class="home-section">
<!-- BREADCRUMBS -->
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject-teacher.show-subject', ['subjectId' => $subjectId]) }}" >
                {{ $subject['title'] }}
            </a>
        </div>

        @if(isset($announcementId))
            <div class="breadcrumb-item">
                <a href="{{ route('mio.subject-teacher.announcements', ['subjectId' => $subjectId]) }}">
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
        <main class="main-banner">
            <div class="welcome-banner">
            <div class="banner">

            <div class="content">
                <h5>Announcement</h5>
            </div>

            <button class="btn primary-btn" style="margin: 1rem 0; z-index: 9999;" onclick="openAnnouncementModal()">+ Create Announcement</button>


            </div>
            </div>
        </main>

    @include('mio.dashboard.status-message')
        <div class="grid-container">

        <!-- Begin Main-->
        <main class="main">
            <!--Begin Main Overview-->
            <div class="main-overview">

        @foreach($announcements as $announcement)
              <a href="{{ route('mio.subject-teacher.announcements-body', [
                    'subjectId' => $subjectId,
                    'announcementId' => $announcement['id']
                ]) }}">


                <div class="overviewcard">
                    <div class="overviewcard__icon"></div>
                    <div class="overviewcard__info">{{ $announcement['title'] ?? 'No Title' }}</div>
                    <div class="overviewcard__date">{{ $announcement['date_posted'] ?? '' }}</div>
                    <div class="overviewcard__arrow">&rsaquo;</div>
                </div>
                </a>
            @endforeach
        <!--End Main Overview-->
        </main>
        <!-- End Main -->
        </div>

  </section>

<div id="fileDeleteModal" style="display:none; position:fixed; top:30%; left:50%; transform:translate(-50%, -50%); background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.2); z-index:1000;">
    <p>Are you sure you want to delete this file?</p>
    <button onclick="confirmFileDelete()" class="btn confirm-btn">Yes, Delete</button>
    <button onclick="closeFileDeleteModal()" class="btn cancel-btn">Cancel</button>
</div>

<!-------- SCRIPTS --------->

<!-- ANNOUNCEMENT -->

<script>
    let announcementCount = 0;
    const announcementFileDataMap = {};
    let fileToDelete = null;
    let deleteContext = null;

    document.addEventListener('DOMContentLoaded', function () {
        const index = 0;
        const fileInput = document.getElementById(`announcement-file-upload-${index}`);
        const preview = document.getElementById(`announcement-preview-${index}`);
        const fileNameSpan = document.getElementById(`announcement-file-name-${index}`);
        announcementFileDataMap[index] = [];

        if (fileInput) {
            fileInput.addEventListener('change', () => {
                const newFiles = Array.from(fileInput.files);
                announcementFileDataMap[index] = newFiles;
                renderAnnouncementPreviews(preview, newFiles, index, fileNameSpan, fileInput);
            });
        }
    });

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

        const fileInput = wrapper.querySelector(`#announcement-file-upload-${announcementCount}`);
        const preview = wrapper.querySelector(`#announcement-preview-${announcementCount}`);
        const fileNameSpan = wrapper.querySelector(`#announcement-file-name-${announcementCount}`);

        announcementFileDataMap[announcementCount] = [];

        fileInput.addEventListener('change', () => {
            const newFiles = Array.from(fileInput.files);
            announcementFileDataMap[announcementCount] = newFiles;
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
            removeBtn.onclick = () => showFileDeleteModal(i, 'new', index);

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

    function showFileDeleteModal(fileIndex, source, index) {
        fileToDelete = fileIndex;
        deleteContext = { source, index };
        document.getElementById('fileDeleteModal').style.display = 'block';
    }

    function closeFileDeleteModal() {
        fileToDelete = null;
        deleteContext = null;
        document.getElementById('fileDeleteModal').style.display = 'none';
    }

    function confirmFileDelete() {
    if (!deleteContext) return;

    const { source, index } = deleteContext;

    if (source === 'new') {
        // Remove from temporary preview list
        announcementFileDataMap[index].splice(fileToDelete, 1);

        const inputEl = document.getElementById(`announcement-file-upload-${index}`);
        const preview = document.getElementById(`announcement-preview-${index}`);
        const fileNameSpan = document.getElementById(`announcement-file-name-${index}`);

        // Update file input and re-render previews
        updateFileInput(inputEl, announcementFileDataMap[index]);
        renderAnnouncementPreviews(preview, announcementFileDataMap[index], index, fileNameSpan, inputEl);
    }

    closeFileDeleteModal();
}

</script>


<script>
function openAnnouncementModal() {
    document.getElementById('announcementModal').style.display = 'flex';
}

function closeAnnouncementModal() {
    document.getElementById('announcementModal').style.display = 'none';
}

// Optional: Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('announcementModal');
    if (event.target == modal) {
        closeAnnouncementModal();
    }
}
</script>



