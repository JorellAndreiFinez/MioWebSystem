<section class="home-section">
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject.show-subject', ['subjectId' => $subject['subject_id']]) }}">
                {{ $subject['title'] }}
            </a>
        </div>
        <div class="breadcrumb-item active"> Assignments</div>

    </div>
    <main class="main-assignment-content">
        @forelse($assignments as $assignment)
            <div class="assignment-card">
                <div class="activity-info">
                    <h3>{{ $assignment['title'] ?? 'Untitled Activity' }}</h3>
                </div>

                <div class="details">
                    <div>
                        <span>Publish at</span>
                        <strong>
                            {{ isset($assignment['published_at']) ? \Carbon\Carbon::parse($assignment['published_at'])->format('F d, Y') : '' }}
                            {{ isset($assignment['availability']['start']) && $assignment['availability']['start'] ? \Carbon\Carbon::createFromFormat('H:i', $assignment['availability']['start'])->format('g:i A') : '' }}
                        </strong>
                    </div>
                    <div>
                        <span>Deadline</span>
                        <strong>
                            {{ isset($assignment['deadline']) && $assignment['deadline'] ? \Carbon\Carbon::parse($assignment['deadline'])->format('F d, Y') : 'No Due Date' }}
                            {{ isset($assignment['availability']['end']) && $assignment['availability']['end'] ? \Carbon\Carbon::createFromFormat('H:i', $assignment['availability']['end'])->format('g:i A') : '' }}
                        </strong>
                    </div>
                    <div>
                        <span>Points</span>
                        <strong>{{ $assignment['total'] ?? '0' }}</strong>
                    </div>
                    <div>
                        <span>Attempts</span>
                        <strong>{{ $assignment['student_data']['attempts'] ?? 0 }}</strong>
                    </div>
                </div>

                <a href="{{ route('mio.subject.assignment-body', ['subjectId' => $subjectId, 'assignmentId' => $assignment['id']]) }}" class="take-quiz-btn">
                    View Assignment
                </a>
            </div>
        @empty
            <p>No assignments available.</p>
        @endforelse
    </main>
</section>
