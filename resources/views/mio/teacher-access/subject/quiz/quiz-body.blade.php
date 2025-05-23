<section class="home-section">
    <!-- 🟦 Header Banner -->
    <main class="main-banner">
        <div class="welcome-banner">
            <div class="banner">
                <div class="content">
                    <h5>Quizzes</h5>
                </div>
            </div>
        </div>
    </main>

    <!-- 📄 Assignment Cards List -->
    <main class="main-assignment-content">
        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
       <div class="assignment-card">
            <div class="activity-info">
                <h1>{{ $quiz['title'] }}</h1>
            </div>

            <div class="details">
                 <div>
                    <span>Publish at</span>
                        <strong>{{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($quiz['publish_date'])->format('Y-m-d') . ' ' . ($quiz['start_time'] ?? '00:00'))->format('F j, Y g:i A') }}</strong>
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
                    <strong>{{ $quiz['attempts'] }}</strong>
                </div>
            </div>

            <!-- Assignment Description -->
            @if (!empty($quiz['description']))
                <div class="assignment-description" style="margin-top: 15px;">
                    <h4>Description</h4>
                    <p>{{ $quiz['description'] }}</p>
                </div>
            @endif

            <!-- Assignment Attachments -->
            @if (!empty($quiz['attachments']))
                <div class="assignment-attachments" style="margin-top: 15px;">
                    <h4>Attachments</h4>
                    <ul>
                        @foreach ($quiz['attachments'] as $attachment)
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

                <div class="edit-assignment-container">
                            <a href="{{ route('mio.subject-teacher.edit-acads-quiz', ['subjectId' => $subjectId, 'quizId' => $quiz['id']]) }}" class="primary-btn" id="openAssignmentModal">Edit Quiz</a>

                            @php
                                $submittedCount = 0;
                                $totalStudents = 0;

                                if (!empty($quiz['people'])) {
                                    $totalStudents = count($quiz['people']);
                                    foreach ($quiz['people'] as $student) {
                                        if (!empty($student['work'])) {
                                            $submittedCount++;
                                        }
                                    }
                                }
                            @endphp

                            <a href="#" class="secondary-btn" id="openReviewModal">
                                Submissions [{{ $submittedCount }} / {{ $totalStudents }}]
                            </a>



                            <form action="{{ route('mio.subject-teacher.deleteQuiz', ['subjectId' => $subjectId, 'quizId' => $quizId]) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" style="background: none; border: none; cursor: pointer;">
                                    <i class="fas fa-trash-alt" style="color: red; font-size: 20px;"></i>
                                </button>
                        </form>

                </div>



            </div>

        <div class="assignment-card">
            @if (!empty($questions))
                    <div class="quiz-questions mt-4">
                        <h4>Questions</h4>
                        @foreach ($questions as $questionId => $question)
                            <div class="question-box mb-4 p-3 border rounded" style="background-color: #f9f9f9;">
                                <p class="font-weight-bold mb-2">Question: {{ $question['question'] }}</p>

                                <div class="options-list">
                                    @foreach ($question['options'] as $optionKey => $optionText)
                                        <div class="option-item p-2 mb-1 rounded"
                                            style="background-color: {{ $optionKey === $question['answer'] ? '#d4edda' : '#f1f1f1' }};
                                                    border-left: 5px solid {{ $optionKey === $question['answer'] ? '#28a745' : '#ccc' }};">
                                            {{ $optionText }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
        </div>

    </main>

</section>





