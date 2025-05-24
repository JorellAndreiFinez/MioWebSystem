<section class="home-section">
<main class="main-banner">
            <div class="welcome-banner">
            <div class="banner">
            <div class="content">
            <h5>Available Quizzes</h5>

            </div>

            </div>
            </div>
        </main>

    <main class="main-assignment-content">
        @foreach ($quizzes as $quiz)
            <div class="assignment-card">
                <div class="activity-info">
                    <h3>{{ $quiz['title'] ?? 'Untitled Activity' }}</h3>
                </div>

                <div class="details">
                    <div>
                        <span>Publish at</span>
                        <strong>
                             \Carbon\Carbon::parse($quiz['publish_date'] . ' ' . ($quiz['start_time'] ?? '00:00'))->format('F j, Y g:i A')

                        </strong>
                    </div>
                    <div>
                        <span>Deadline</span>
                        <strong>
                            @if (!empty($quiz['deadline']))
                                {{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($quiz['deadline'])->format('Y-m-d') . ' ' . ($quiz['end_time'] ?? '00:00'))->format('F j, Y g:i A') }}
                            @else
                                No Due Date
                            @endif
                        </strong>
                    </div>

                        <div>
                            <span>Points</span>
                            <strong>{{ $quiz['total'] ?? '0' }}</strong>
                        </div>
                    <div>
                        <span>Attempt/s</span>
                        <strong>{{ $quiz['attempts'] ?? '1' }}</strong>
                    </div>
                </div>

               <a href="{{ route('mio.subject-teacher.quiz-body', ['subjectId' => $subjectId, 'quizId' => $quiz['id']]) }}" class="take-quiz-btn">View Assignment</a>

                <!-- Trash Icon Button -->
            <form action="{{ route('mio.subject-teacher.deleteQuiz', ['subjectId' => $subjectId, 'quizId' => $quiz['id']]) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="delete-btn" style="background: none; border: none; cursor: pointer;">
                    <i class="fas fa-trash-alt" style="color: red; font-size: 20px;"></i>  <!-- Trash icon -->
                </button>
            </form>

            </div>
        @endforeach

         <div class="assignment-card">
             <div class="add-assignment-container">
                <a href="{{ route('mio.subject-teacher.add-acads-quiz', ['subjectId' => $subject['subject_id']]) }}" class="add-assignment-btn" id="openModal">+ Add Quiz</a>
            </div>
        </div>

    </main>



</section>


