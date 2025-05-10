<section class="home-section">

    <main class="main-announcement">
        <div class="announcement-banner">
            <div class="banner">
                <div class="content">
                    <h3>{{ $announcement['title'] ?? 'No Title' }}</h3>
                    <h6>{{ $announcement['date_posted'] ?? 'No Date' }}</h6>
                    <p>{{ $announcement['description'] ?? 'No description available.' }}</p>

                    <div class="image-wrapper">
                        <img src="{!! $announcement['link'] ?? '' !!}" alt="Announcement Image">
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

            <!-- Sample replies -->
            <div class="reply-item">
                <div class="reply-header">
                    <strong>Juan Dela Cruz</strong> • <small>April 18, 2025 at 10:12 AM</small>
                </div>
                <p>Thank you for the update, stay safe everyone!</p>
            </div>

            <div class="reply-item reply-own">
                <div class="reply-header">
                    <strong>You</strong> • <small>April 18, 2025 at 10:45 AM</small>
                </div>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sit, vitae porro...</p>
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
