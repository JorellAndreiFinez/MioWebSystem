@php
    function hasRole($role) {
        return session()->has('firebase_user') && session('firebase_user.role') === $role;
    }
@endphp

<section class="home-section">

    <!-- BREADCRUMBS -->
        <div class="text">
            <div class="breadcrumb-item">
                <a href="{{ route('mio.subject.show-subject', ['subjectId' => $subjectId]) }}">
                    {{ $subject['title'] }}
                </a>
            </div>

            @if(isset($announcementId))
                <div class="breadcrumb-item">
                    <a href="{{ route('mio.subject.announcements', ['subjectId' => $subjectId]) }}">
                        Announcements
                    </a>
                </div>
            @else
                <div class="breadcrumb-item active">
                    Announcements
                </div>
            @endif
        </div>


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

                    <div class="reply-section" id="replySection" style="display: none;">
                        <form method="POST" action="{{ route('mio.subject.storeReply', ['subjectId' => $subject['subject_id'], 'announcementId' => $announcementId]) }}">
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
        <!-- Replies Container -->
        <div class="all-replies">
            <h4>Replies</h4>

            @if(count($announcement['replies'] ?? []) > 0)
                @foreach($announcement['replies'] as $replyId => $reply)
                <div class="reply-item">
                    <div class="reply-header">
                        <strong>{{ $reply['user_name'] }}</strong> • <small>{{ $reply['timestamp'] }}</small>
                        @if (hasRole('admin') || session('firebase_user.uid') === $reply['user_id'])
                            <form action="{{ route('mio.subject.deleteReply', ['subjectId' => $subject['subject_id'], 'announcementId' => $announcementId, 'replyId' => $replyId]) }}" method="POST" style="display:inline;">
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

<script>
    function toggleReply() {
        const replySection = document.getElementById('replySection');
        replySection.style.display = replySection.style.display === 'none' ? 'block' : 'none';
    }
</script>
