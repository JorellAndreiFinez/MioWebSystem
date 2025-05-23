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
                            {{ isset($quiz['availability']['start']) && $quiz['availability']['start'] ? \Carbon\Carbon::createFromFormat('H:i', $quiz['availability']['start'])->format('g:i A') : '' }}
                        </strong>
                    </div>
                    <div>
                        <span>Deadline</span>
                        <strong>
                            {{ isset($quiz['deadline']) && $quiz['deadline'] ? \Carbon\Carbon::parse($quiz['deadline'])->format('F d, Y') : 'No Due Date' }}
                            {{ isset($quiz['availability']['end']) && $quiz['availability']['end'] ? \Carbon\Carbon::createFromFormat('H:i', $quiz['availability']['end'])->format('g:i A') : '' }}
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

                <a href="{{ route('mio.subject.quiz-body', ['subjectId' => $subjectId, 'quizId' => $quiz['id']]) }}" class="take-quiz-btn">
                    Take Assignment
                </a>
            </div>
        @empty
            <p>No assignments available.</p>
        @endforelse
    </main>
</section>
