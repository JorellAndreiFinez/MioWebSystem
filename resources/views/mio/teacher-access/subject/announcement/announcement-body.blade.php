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
    <main class="main-announcement">
        <div class="announcement-banner">
            <div class="banner">

                <div class="content">
                    <!-- Edit Button (Visible only to the teacher who posted the announcement) -->
                    <div class="edit-btn-container">
                        <button type="button" class="edit-btn" onclick="enableEditMode()">✏️ Edit Announcement</button>
                    </div>

                    <!-- Edit Form (hidden initially) -->
                    <form method="POST"
                        action="{{ route('mio.subject-teacher.editAnnouncement', ['subjectId' => $subject['subject_id'], 'announcementId' => $announcementId]) }}"
                        enctype="multipart/form-data"
                        id="editAnnouncementForm"
                        style="display:none;">
                        @csrf
                        @method('PUT')

                        <input type="text" name="title" class="form-control" value="{{ $announcement['title'] ?? 'No Title' }}" required>

                        <input type="date" name="date_posted" class="form-control" value="{{ $announcement['date_posted'] ?? '' }}" required>

                        <textarea name="description" class="form-control" required>{{ $announcement['description'] ?? '' }}</textarea>

                        <label for="link">Image Link (optional):</label>
                        <input type="text" name="link" class="form-control" value="{{ $announcement['link'] ?? '' }}" placeholder="Image link (optional)">

                        <label for="image_file">Or Upload Image (optional):</label>
                        <input type="file" name="image_file" class="form-control" accept="image/*">

                        <button type="submit" class="save-btn">Save Announcement</button>
                        <button type="button" onclick="cancelEditMode()" class="cancel-btn-2">Cancel</button>
                    </form>

                     <div id="announcementDisplay">
                        <h3>{{ $announcement['title'] ?? 'No Title' }}</h3>
                        <h6>{{ $announcement['date_posted'] ?? 'No Date' }}</h6>
                        <p>{{ $announcement['description'] ?? 'No description available.' }}</p>
                        <div class="image-wrapper">
                            <img src="{!! $announcement['link'] ?? '' !!}" alt="Announcement Image">
                        </div>
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

<script>
    function toggleReply() {
        const replySection = document.getElementById('replySection');
        replySection.style.display = replySection.style.display === 'none' ? 'block' : 'none';
    }
</script>

<script>
    function enableEditMode() {
        document.getElementById('announcementDisplay').style.display = 'none';
        document.getElementById('editAnnouncementForm').style.display = 'block';
    }

    function cancelEditMode() {
        document.getElementById('announcementDisplay').style.display = 'block';
        document.getElementById('editAnnouncementForm').style.display = 'none';
    }

    function toggleReply() {
        const replySection = document.getElementById('replySection');
        replySection.style.display = replySection.style.display === 'none' ? 'block' : 'none';
    }
</script>

