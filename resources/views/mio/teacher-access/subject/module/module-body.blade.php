<section class="home-section">

<div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject-teacher.show-subject', ['subjectId' => $subject['subject_id']]) }}">
                {{ $subject['title'] }}
            </a>
        </div>

        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject-teacher.modules', ['subjectId' => $subject['subject_id']]) }}">
                Modules
            </a>
        </div>
        <div class="breadcrumb-item active" style="font-size: 1.3rem;">{{ $moduleIndex }}</div>

    </div>


    <main class="main-banner">

        <div class="welcome-banner">
            <div class="banner">
                <div class="content">
                    <h5>Module: {{ $module['title'] }}</h5>
                </div>
            </div>
        </div>
    </main>

    <div class="grid-container">

    <!-- Begin Main -->
    <main class="main main-module-content">

    <div class="edit-btn-container" style="margin-bottom: 2rem;">
        <button type="button" class="edit-btn" onclick="enableEditMode()">✏️ Edit Module </button>
    </div>
        <!-- Module Description -->
        <div class="module-card">
            <div class="module-card__content">
                <p class="module-description">{{ $module['description'] }}</p>
            </div>
        </div>
        <br>
        <hr class="section-divider">

        <br>
        <!-- View Button -->
        <div class="view-button-container">
            <a href="#" class="preview-module-btn" id="togglePdfBtn">View Module</a>
        </div>

        <!-- PDF Viewer -->
        <div class="module-viewer" id="pdfViewer">
            <object class="pdf"
                    data="{{ $module['file']['url'] ?? '#' }}"
                    type="application/pdf"
                    width="100%"
                    height="600px">
                <p>Your browser does not support PDFs.
                    <a href="{{ $module['file']['url'] ?? '#' }}">Download the PDF</a>.
                </p>
            </object>
        </div>

    </main>
    <!-- End Main -->
</div>

</section>

<script>
document.getElementById('togglePdfBtn').addEventListener('click', function (event) {
    event.preventDefault();
    const viewer = document.getElementById('pdfViewer');
    console.log('Viewer state:', viewer.style.display);  // Check if it's being toggled
    if (viewer.style.display === "none" || viewer.style.display === "") {
        viewer.style.display = "block";
        this.textContent = "Close Module";
    } else {
        viewer.style.display = "none";
        this.textContent = "View Module";
    }
});

</script>
