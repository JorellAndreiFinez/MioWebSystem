@php
    use Carbon\Carbon;

    $now = Carbon::now();
@endphp

  @php
        $publishDate = isset($quiz['publish_date']) ? Carbon::parse($quiz['publish_date']) : null;
        $deadline = isset($quiz['deadline']) ? Carbon::parse($quiz['deadline'])->setTimeFromTimeString($quiz['end_time'] ?? '23:59') : null;
        $studentId = session('user_id'); // or use your method of getting logged-in student ID

        $isStudentInPeopleList = isset($quiz['people']) && array_key_exists($studentId, $quiz['people']);
        $studentAttempts = $quiz['student_data']['attempts'] ?? 0;
        $maxAttempts = $quiz['attempts'] ?? 0;

        $isBeforePublish = $publishDate && $now->lt($publishDate);
        $isAfterDeadline = $deadline && $now->gt($deadline);
        $hasExceededAttempts = $studentAttempts >= $maxAttempts;

        $isLocked = !$isStudentInPeopleList || $isBeforePublish || $isAfterDeadline || $hasExceededAttempts;
    @endphp

<section class="home-section">
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject.show-subject', ['subjectId' => $subject['subject_id']]) }}">
                {{ $subject['title'] }}
            </a>
        </div>
        <div class="breadcrumb-item active"> Quizzes</div>

    </div>
    <main class="main-assignment-content">
        @forelse($quizzes as $quiz)
            <div class="assignment-card">
                <div class="activity-info">
                    <h3>{{ $quiz['title'] ?? 'Untitled Activity' }}</h3>
                </div>

                <div class="details">
                    <div>
                        <span>Publish at</span>
                        <strong>
                            {{ isset($quiz['publish_date']) ? \Carbon\Carbon::parse($quiz['publish_date'])->format('F d, Y') : '' }}
                            {{ isset($quiz['start_time']) && $quiz['start_time'] ? \Carbon\Carbon::createFromFormat('H:i', $quiz['start_time'])->format('g:i A') : '' }}
                        </strong>
                    </div>
                    <div>
                        <span>Deadline</span>
                        <strong>
                            {{ isset($quiz['deadline']) && $quiz['deadline'] ? \Carbon\Carbon::parse($quiz['deadline'])->format('F d, Y') : 'No Due Date' }}
                            {{ isset($quiz['end_time']) && $quiz['end_time'] ? \Carbon\Carbon::createFromFormat('H:i', $quiz['end_time'])->format('g:i A') : '' }}
                        </strong>
                    </div>
                    <div>
                        <span>Points</span>
                        <strong>{{ $quiz['total'] ?? '0' }}</strong>
                    </div>
                    <div>
                        <span>Total Attempts</span>
                        <strong>{{ $quiz['attempts'] ?? 0 }}</strong>
                    </div>
                    <div>
                        <span>Your Attempts</span>
                        <strong>{{ $quiz['student_data']['attempts'] ?? 0 }}</strong>
                    </div>
                </div>
            @if ($isLocked)
                <button class="take-quiz-btn disabled" disabled>
                    @if ($isBeforePublish)
                        Not Yet Available
                    @elseif ($isAfterDeadline)
                        Deadline Passed
                    @elseif ($hasExceededAttempts)
                        No More Attempts
                    @else
                        Locked
                    @endif
                </button>
            @else
                <a href="{{ route('mio.subject.quiz-body', ['subjectId' => $subjectId, 'quizId' => $quiz['id']]) }}" class="take-quiz-btn">
                    Take Assignment
                </a>
            @endif
                </div>
        @empty
            <p>No assignments available.</p>
        @endforelse
    </main>
</section>
