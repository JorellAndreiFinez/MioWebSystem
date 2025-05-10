<section class="home-section">

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
            <div class="module-card">
                <div class="module-card__content">
                    <h3>{{ $module['title'] }}</h3>
                    <p>{{ $module['description'] }}</p>
                </div>
            </div>

            <a href="#" class="preview-module-btn" id="togglePdfBtn">View Module</a>

            <div class="module-viewer" id="pdfViewer" style="display: none;">
                <object class="pdf"
                        data="{{ $module['url'] ?? '#' }}"
                        type="application/pdf"
                        width="100%"
                        height="600px">
                    <p>Your browser does not support PDFs.
                        <a href="{{ $module['url'] ?? '#' }}">Download the PDF</a>.</p>
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
