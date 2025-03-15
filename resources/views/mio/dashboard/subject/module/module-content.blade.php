
<section class="home-section">
      <div class="text">
      <a href="{{ route('mio.subject') }}">Subject</a>
      >
      <a href="{{ route('mio.subject.module') }}">Modules</a>
      > Module 1</div>

        <main class="main-module-content ">
            <div class="module-card">
            <div class="banner">
            <div class="content">
            <h3>[Module 1 - Lorem Ipsum]</h3>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Esse perspiciatis adipisci illo magni, labore autem incidunt, assumenda mollitia neque est quos harum nostrum odit cum pariatur. Exercitationem odio libero temporibus!
                Sint id atque sapiente sed labore incidunt at aliquam harum, accusantium, aut quos! Et magnam molestiae vitae illum deleniti! Consequuntur, quis illum? Et aperiam fugit vel nemo autem libero ea?
                Itaque quis magnam harum eaque repellendus sapiente, est corrupti quisquam accusamus maxime voluptatibus consequatur, doloremque asperiores atque, fugiat fugit ut sunt dolores doloribus libero ad ex voluptatem minus. Quisquam, aliquam.
                Molestias nesciunt aspernatur similique, ullam nam earum eaque cupiditate fuga incidunt eum impedit recusandae dolor? Fugit impedit velit aperiam odit pariatur, repudiandae fugiat assumenda provident recusandae placeat eligendi, veniam consequatur?</p>
            </div>

            </div>

            </div>

        </main>

        <a href="#" class="preview-module-btn" id="togglePdfBtn">View Module</a>

    <div class="module-viewer" id="pdfViewer">
        <!-- <h1>[ Module Title ]</h1> -->
        <object class="pdf"
            data="https://media.geeksforgeeks.org/wp-content/cdn-uploads/20210101201653/PDF.pdf"
            type="application/pdf">
        </object>
    </div>

  </section>

  <script>
        // Get the button and the PDF viewer div
        const togglePdfBtn = document.getElementById('togglePdfBtn');
        const pdfViewer = document.getElementById('pdfViewer');

        // Add click event listener to toggle the display of the PDF viewer
        togglePdfBtn.addEventListener('click', function(event) {
            event.preventDefault();  // Prevent default anchor behavior
            // Toggle the display of the PDF viewer
            if (pdfViewer.style.display === "none" || pdfViewer.style.display === "") {
                pdfViewer.style.display = "block";
                togglePdfBtn.textContent = "Close Module";  // Show the PDF viewer
            } else {
                pdfViewer.style.display = "none";  // Hide the PDF viewer
            }
        });
    </script>

