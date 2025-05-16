<section class="home-section">
    <main class="main-assignment-content">
        @forelse($assignments as $assignment)
            <div class="assignment-card">
                <div class="activity-info">
                    <h3>{{ $assignment['title'] ?? 'Untitled Activity' }}</h3>
                </div>

                <div class="details">
                    <div>
                        <span>Publish at</span>
                        <strong>{{ \Carbon\Carbon::parse($assignment['published_at'])->format('F d, Y') }} {{ \Carbon\Carbon::createFromFormat('H:i', $assignment['availability']['start'])->format('g:i A') }}</strong>
                    </div>
                    <div>
                        <span>Deadline</span>
                        <strong>
                            {{ \Carbon\Carbon::parse($assignment['deadline'])->format('F d, Y') }}
                            {{ \Carbon\Carbon::createFromFormat('H:i', $assignment['availability']['end'])->format('g:i A') }}


                        </strong>
                    </div>
                    <div>
                        <span>Points</span>
                        <strong>{{ $assignment['points']['total'] ?? '0' }}</strong>
                    </div>
                    <div>
                        <span>Attempts</span>
                        <strong>{{ $assignment['student_data']['attempts'] ?? 0 }}</strong>
                    </div>
                </div>

                @php
                    $publishedDateTime = \Carbon\Carbon::parse($assignment['published_at']);
                    $deadlineDateTime = \Carbon\Carbon::parse($assignment['deadline'] . ' ' . $assignment['availability']['end']);
                    $now = \Carbon\Carbon::now();
                @endphp

                {{-- Route for editing the assignment view (for admin or teacher use) --}}
                {{-- route('assignment.edit', [$assignment['subject_id'], $assignment['id']]) --}}

                @if ($publishedDateTime->isPast())
                    @if ($now->lte($deadlineDateTime))
                        <a href="{{ route('mio.subject.assignment-body', ['subjectId' => $subjectId, 'assignmentId' => $assignment['id']]) }}"  class="take-quiz-btn">
                            View Assignment
                        </a>
                    @else
                        <a href="#" class="take-quiz-btn disabled" style="pointer-events: none; opacity: 0.6; cursor: not-allowed;">
                            Locked
                        </a>
                    @endif
                @endif
            </div>
        @empty
            <p>No assignments available.</p>
        @endforelse
    </main>
</section>
