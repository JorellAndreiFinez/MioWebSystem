<section class="home-section">
    @include('mio.dashboard.breadcrumbs')

    <main class="main-announcement">
        <div class="announcement-banner">
            <div class="banner">
                <div class="content">
                    <h3>Walang Pasok</h3>
                    <h6>March 30, 2025 9:00am</h6>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi odit consequuntur animi ex ad, sunt labore velit esse aperiam voluptates ratione aliquid tenetur similique repudiandae voluptas quia sed at officia?
                        <br><br>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Modi aspernatur fugiat, obcaecati dolore quae voluptate nam a distinctio laborum nesciunt tempore sit nobis quaerat voluptatum eveniet doloribus sapiente quam laboriosam.
                    </p>

                    <div class="image-wrapper">
                        <img src="https://www.joserizal.com/wp-content/uploads/2013/09/jose-rizal-writing.jpg" alt="">
                    </div>

                    <!-- Delete Button (Visible to authorized users only) -->
                    <!-- <div class="delete-btn-container">
                        <form method="POST" action="#">

                            <button type="submit" class="delete-btn">🗑 Delete Announcement</button>
                        </form>
                    </div> -->

                    <!-- Toggle Reply Button -->
                    <button class="toggle-reply-btn" onclick="toggleReply()">💬 Reply</button>

                    <!-- Reply Section (Hidden by Default) -->
                    <div class="reply-section" id="replySection" style="display: none;">
                        <form method="POST" action="#">
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

            <!-- Someone else's reply -->
            <div class="reply-item">
                <div class="reply-header">
                    <strong>Juan Dela Cruz</strong> • <small>April 18, 2025 at 10:12 AM</small>
                </div>
                <p>Thank you for the update, stay safe everyone!</p>
            </div>

            <!-- Logged-in user's reply (with delete button) -->
            <div class="reply-item reply-own">
                <div class="reply-header">
                    <strong>You</strong> • <small>April 18, 2025 at 10:45 AM</small>

                </div>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sit, vitae porro. Ad ratione sed, nihil id fugit voluptatem nesciunt qui nemo eaque eos, distinctio minus, voluptatibus tempore accusamus vel facere.</p>
                <button class="reply-delete-btn" title="Delete your reply">🗑</button>
            </div>

        </div>



    </main>
</section>

<script>
    function toggleReply() {
        const replySection = document.getElementById('replySection');
        replySection.style.display = replySection.style.display === 'none' ? 'block' : 'none';
    }
</script>
