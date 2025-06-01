@php
    use Carbon\Carbon;

    $now = Carbon::now();
    $studentId = session('firebase_user.uid');

    $publishDate = isset($quiz['publish_date'])
        ? Carbon::parse($quiz['publish_date'])->setTimeFromTimeString($quiz['start_time'] ?: '00:00')
        : null;

    $deadline = isset($quiz['deadline_date']) && !empty($quiz['deadline_date'])
        ? (Carbon::parse($quiz['deadline_date'])->setTimeFromTimeString(!empty($quiz['end_time']) ? $quiz['end_time'] : '23:59'))
        : null;
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
          @php
            $now = Carbon::now();


            $isStudentInPeopleList = isset($quiz['people']) && array_key_exists($studentId, $quiz['people']);

            $studentAttempts = $quiz['people'][$studentId]['total_student_attempts'] ?? 0;

            $maxAttempts = $quiz['attempts'] ?? 0;

            $isBeforePublish = $publishDate && $now->lt($publishDate);
            $isAfterDeadline = $deadline && $now->gt($deadline);
            $hasExceededAttempts = $maxAttempts > 0 && $studentAttempts >= $maxAttempts;

            $isLocked = !$isStudentInPeopleList || $isBeforePublish || $isAfterDeadline || $hasExceededAttempts;

             $studentData = $quiz['people'][$studentId] ?? null;
            $studentAttempts = $studentData['total_student_attempts'] ?? 0;

            // Find the latest attempt (if any)
            $latestAttempt = null;
            $studentScore = 0;
            $studentTotalPoints = $quiz['total_points'] ?? 0;

            $status = $attempt['status'] ?? 'complete';
            $incompleteAttemptId = null;
            $currentQuestionIndex = 0;

            if ($status === 'incomplete') {
                $incompleteAttemptId = $key; // attempt ID like ATTM123
                $currentQuestionIndex = $attempt['current_question_index'] ?? 0;
            }


            if ($studentData) {
                foreach ($studentData as $key => $attempt) {
                    if (Str::startsWith($key, 'ATTM')) {
                        $latestAttempt = $attempt;
                        $studentScore = $attempt['score'] ?? 0;
                        $studentTotalPoints = $attempt['total_points'] ?? ($quiz['total_points'] ?? 0);
                    }
                }
            }
        @endphp


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
                            {{ isset($quiz['deadline_date']) && $quiz['deadline_date'] ? \Carbon\Carbon::parse($quiz['deadline_date'])->format('F d, Y') : 'No Due Date' }}
                            {{ isset($quiz['end_time']) && $quiz['end_time'] ? \Carbon\Carbon::createFromFormat('H:i', $quiz['end_time'])->format('g:i A') : '' }}
                        </strong>
                    </div>
                    <div>
                        <span>Points</span>
                        <strong>{{ $quiz['total_points'] ?? '0' }}</strong>
                    </div>
                    <div>
                        <span>Total Attempts</span>
                        <strong>{{ $quiz['attempts'] ?? 0 }}</strong>
                    </div>
                   <div>
                    <span>Your Attempts</span>
                    <strong>{{ $studentAttempts }}</strong>
                </div>
                <div>
                    <span>Your Score</span>
                    <strong>
                     {{ $studentAttempts > 0 ? $studentScore : '?' }} / {{ $studentTotalPoints }}
                </strong>
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
                @if ($incompleteAttemptId)
                <a href="{{ route('mio.subject.quiz-resume', [
                    'subjectId' => $subjectId,
                    'quizId' => $quiz['id'],
                    'attemptId' => $incompleteAttemptId,
                    'questionIndex' => $currentQuestionIndex
                ]) }}" class="take-quiz-btn">
                    Resume Quiz
                </a>
            @else
                <a href="{{ route('mio.subject.quiz-body', ['subjectId' => $subjectId, 'quizId' => $quiz['id']]) }}" class="take-quiz-btn">
                    Take Assignment
                </a>
            @endif

            @endif
                </div>
        @empty
            <p>No assignments available.</p>
        @endforelse
    </main>
</section>
