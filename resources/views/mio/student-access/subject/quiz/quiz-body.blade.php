<section class="home-section">
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject.show-subject', ['subjectId' => $subject['subject_id']]) }}">
                {{ $subject['title'] }}
            </a>
        </div>

        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject.assignment', ['subjectId' => $subject['subject_id']]) }}">
                Assignments
            </a>
        </div>
        <div class="breadcrumb-item active">{{ $assignment['title'] }}</div>

    </div>
    <!-- 🟦 Header Banner -->
    <main class="main-banner">
        <div class="welcome-banner">
            <div class="banner">
                <div class="content">
                    <h5>Assignments</h5>
                </div>
            </div>
        </div>
    </main>

    @php
        $studentId = session('firebase_user.uid');
        $maxAttempts = (int) ($assignment['attempts'] ?? 0);
        $studentAttempts = (int) ($assignment['people'][$studentId]['attempts'] ?? 0);
        $hasReachedLimit = $studentAttempts >= $maxAttempts;

        $deadlineDateTime = \Carbon\Carbon::parse($assignment['deadline'] . ' ' . $assignment['availability']['end']);
        $now = \Carbon\Carbon::now();
        $isBeforeDeadline = $now->lte($deadlineDateTime);

        $rawScore = $assignment['people'][$studentId]['score'] ?? null;
        $studentScore = is_numeric($rawScore) ? (int) $rawScore : ' ';

    @endphp

    <!-- 📄 Assignment Cards List -->
    <main class="main-assignment-content">
       <div class="assignment-card">

           <div class="activity-info">
                <h1>{{ $assignment['title'] }}</h1>
                <h3><h2 style="color: red;">{{ $studentScore }}</h2> / {{ $assignment['total'] }}</h3>
            </div>


            <div class="details">
                 <div>
                    <span>Publish at</span>
                        <strong>{{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($assignment['published_at'])->format('Y-m-d') . ' ' . ($assignment['availability']['start'] ?? '00:00'))->format('F j, Y g:i A') }}</strong>
                </div>
                <div>
                    <span>Deadline</span>
                    <strong>
                        {{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($assignment['created_at'])->format('Y-m-d') . ' ' . ($assignment['availability']['end'] ?? '00:00'))->format('F j, Y g:i A')}}

                    </strong>
                </div>

                <div>
                    <span>Attempts</span>
                    <strong>{{ $assignment['attempts'] }}</strong>
                </div>
            </div>

            <!-- Assignment Description -->
            @if (!empty($assignment['description']))
                <div class="assignment-description" style="margin-top: 15px;">
                    <h4>Description</h4>
                    <p>{{ $assignment['description'] }}</p>
                </div>
            @endif

            <!-- Assignment Attachments -->
            @if (!empty($assignment['attachments']))
                <div class="assignment-attachments" style="margin-top: 15px;">
                    <h4>Attachments</h4>
                    <ul>
                        @foreach ($assignment['attachments'] as $attachment)
                            @if (!empty($attachment['file']))
                                <li>
                                    📎 <a href="{{ $attachment['file'] }}" target="_blank">{{ basename($attachment['file']) }}</a>
                                </li>
                            @endif
                            @if (!empty($attachment['link']))
                                <li>
                                    🔗 <a href="{{ $attachment['link'] }}" target="_blank">{{ $attachment['link'] }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
            </div>

            <!-- 📝 Student Submission Section -->
        <div class="assignment-submission" style="margin-top: 30px; margin-left: 20px;">
            <h4>Your Submission</h4>

            @if (!empty($assignment['people'][session('firebase_user.uid')]['work']))
                <p>📄
                    <a href="{{ $assignment['people'][session('firebase_user.uid')]['work'] }}" target="_blank">
                        {{ basename($assignment['people'][session('firebase_user.uid')]['work']) }}
                    </a>
                </p>
            @else
                <p>No submission yet.</p>
            @endif

        <!-- Button to open submission modal -->

        @if ($hasReachedLimit || !$isBeforeDeadline)
            <button type="button" class="btn btn-secondary" disabled>
                {{ $hasReachedLimit ? 'No More Attempts' : 'Closed' }}
            </button>
        @else
            <button type="button" class="btn btn-primary" id="openSubmitModal">
                Submit Assignment
            </button>
        @endif
    </div>

    <!-- Submission Modal -->
    <div id="submitAssignmentModal" class="modal" style="display: none;">
        <div class="modal-content" >
            <span class="close" id="closeSubmitModal" style="position: absolute; top: 10px; right: 15px; cursor: pointer; font-size: 24px;">&times;</span>
            <h2>Submit Your Assignment</h2>
            <form action="{{ route('mio.subject.assignment-submit', [$subjectId, $assignment['id']]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group" style="margin-top: 15px;">
                    <label for="work">Upload your work:</label>
                    <input type="file" name="work" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Submit Assignment</button>
            </form>
        </div>
    </div>


    </main>
</section>

<script>
    // Get the modal element
    const submitModal = document.getElementById("submitAssignmentModal");

    // Button to open the modal
    const openSubmitModalBtn = document.getElementById("openSubmitModal");

    // Button to close the modal (the "x")
    const closeSubmitModalBtn = document.getElementById("closeSubmitModal");

    // When the user clicks the button, open the modal
    openSubmitModalBtn.addEventListener("click", function(e) {
        e.preventDefault();
        submitModal.style.display = "block";
    });

    // When the user clicks on <span> (x), close the modal
    closeSubmitModalBtn.addEventListener("click", function() {
        submitModal.style.display = "none";
    });

    // When the user clicks anywhere outside of the modal, close it
    window.addEventListener("click", function(event) {
        if (event.target === submitModal) {
            submitModal.style.display = "none";
        }
    });
</script>

