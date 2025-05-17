<section class="home-section">
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject.show-subject', ['subjectId' => $subject['subject_id']]) }}">
                {{ $subject['title'] }}
            </a>
        </div>
        <div class="breadcrumb-item active">Scores</div>
    </div>

    <div class="grid-container">
        <main class="main-scores">

            {{-- Assignment Scores --}}
            <div class="score-card">
                <div class="score-card-header">
                    <h2>Assignments</h2>
                </div>
                <div class="score-card-body">
                    <div class="score-table-wrapper">
                        <table class="score-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Submitted</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($assignmentScores as $score)
                                    <tr>
                                        <td>{{ $score['title'] }}</td>
                                        <td>{{ $score['submitted'] }}</td>
                                        <td>{{ $score['score'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No assignment scores available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="score-card-header">
                    <h2>Quizzes</h2>
                </div>
                <div class="score-card-body">
                    <div class="score-table-wrapper">
                        <table class="score-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Submitted</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($quizScores as $score)
                                    <tr>
                                        <td>{{ $score['title'] }}</td>
                                        <td>{{ $score['submitted'] }}</td>
                                        <td>{{ $score['score'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No quiz scores available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>

        </main>
    </div>
</section>
