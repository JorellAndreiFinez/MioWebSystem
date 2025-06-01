
<section class="home-section">
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject.show-subject', ['subjectId' => $subject['subject_id']]) }}">
                {{ $subject['title'] }}
            </a>
        </div>
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject.quiz', ['subjectId' => $subject['subject_id']]) }}">
                Quizzes
            </a>
        </div>
        <div class="breadcrumb-item active">{{ $quiz['title'] }}</div>
    </div>

    @php
        $studentId = session('firebase_user.uid');
        session(['quiz_start_time' => now()]);

        $currentIndex = session('current_question_index', 0);

        if (request()->input('action') === 'next') {
            session(['current_question_index' => $currentIndex + 1]);
        } elseif (request()->input('action') === 'prev') {
            session(['current_question_index' => max(0, $currentIndex - 1)]);
        }


        $maxAttempts = (int) ($quiz['attempts'] ?? 0);
        $studentAttempts = (int) ($quiz['people'][$studentId]['attempts'] ?? 0);
        $hasReachedLimit = $studentAttempts >= $maxAttempts;

        $hasDeadline = !empty($quiz['deadline_date']);

        if ($hasDeadline) {
            $deadlineDateTime = \Carbon\Carbon::parse($quiz['deadline_date'] . ' ' . ($quiz['end_time'] ?: '23:59'));
            $now = \Carbon\Carbon::now();
            $isBeforeDeadline = $now->lte($deadlineDateTime);
        } else {
            $isBeforeDeadline = true; // No deadline means quiz is always available
        }

        $canTakeQuiz = !$hasReachedLimit && $isBeforeDeadline;

        $oneQuestionAtATime = $quiz['one_question_at_a_time'] ?? false;
        $canGoBack = $quiz['can_go_back'] ?? false;
    @endphp

     <!-- 🕒 Timer Moved Here -->
    <div id="countdown-timer" class="countdown-timer">
        Time: <span id="timer">Loading...</span>
    </div>
    <main class="main-assignment-content">
        <div class="assignment-card2">
            <div class="activity-info2">
                <h1>{{ $quiz['title'] }}</h1>
                <h4 style="color: #444">{{ $quiz['description'] }}</h4>
            </div>

            <div class="details2">
                <div>
                    <span>Publish Date</span>
                    <strong>{{ \Carbon\Carbon::parse($quiz['publish_date'])->format('F j, Y') }}</strong>
                </div>
                <div>
                <span>Deadline</span>
                @if (!empty($quiz['deadline_date']))
                    <strong>{{ \Carbon\Carbon::parse($quiz['deadline_date'] . ' ' . ($quiz['end_time'] ?: '23:59'))->format('F j, Y g:i A') }}</strong>
                @else
                    <strong>No due date</strong>
                @endif
            </div>
                <div>
                    <span>Attempts Allowed</span>
                    <strong>{{ $quiz['attempts'] }}</strong>
                </div>
                @if (!($quiz['no_time_limit'] ?? false))
                <div>
                    <span>Time Limit</span>
                    <strong>{{ $quiz['time_limit'] }} minutes</strong>
                </div>
            @else
                <div>
                    <span>Time Limit</span>
                    <strong>No time limit</strong>
                </div>
            @endif
            </div>

            <!-- route('mio.subject.quiz-submit', [$subjectId, $quizId])  -->
        </div>



        <div class="assigment-card2">
            @if ($canTakeQuiz)
                <form method="POST" action="{{ route('mio.subject.quiz-submit', [$subject['subject_id'], $quiz['quiz_id']]) }}" enctype="multipart/form-data">
                @csrf

                @if (!$oneQuestionAtATime)
                    {{-- Show all questions at once --}}
                    @foreach ($quiz['questions'] as $questionId => $question)
                        <div class="question-card2 question-slide" style="margin-top: 20px; display: none;" data-question-index="{{ $loop->index }}">
                            <h4>{{ $loop->iteration }}. {{ $question['question'] }}</h4>

                            {{-- MULTIPLE CHOICE --}}
                            @if (isset($question['type']) && $question['type'] === 'multiple_choice' && isset($question['options']))
                                @foreach ($question['options'] as $optionKey => $optionText)
                                    <div class="form-check">
                                        <input type="radio"
                                            name="answers[{{ $questionId }}]"
                                            value="{{ $optionKey }}"
                                            class="form-check-input"
                                            id="{{ $questionId }}-{{ $optionKey }}"
                                            required>
                                        <label class="form-check-label" for="{{ $questionId }}-{{ $optionKey }}">
                                            {{ $optionText }}
                                        </label>
                                    </div>
                                @endforeach

                            {{-- ESSAY --}}
                            @elseif (isset($question['type']) && $question['type'] === 'essay')
                                <textarea name="answers[{{ $questionId }}]" rows="4" class="form-control" required></textarea>

                            {{-- FILE UPLOAD --}}
                            @elseif (isset($question['type']) && $question['type'] === 'file_upload')
                                <input type="file" name="answers[{{ $questionId }}]" class="form-control-file" required>

                            {{-- FILL IN THE BLANK --}}
                            @elseif (isset($question['type']) && $question['type'] === 'fill_in_the_blank')
                                <input type="text" name="answers[{{ $questionId }}]" class="form-control" required>

                            {{-- DEFAULT (optional fallback) --}}
                            @else
                                <p style="color: red;">Unknown question type or missing data.</p>
                            @endif
                        </div>
                    @endforeach


                    <div class="submit-btn2" style="margin-top: 30px;">
                        <button type="submit" class="btn2 btn-primary">Submit Quiz</button>
                    </div>

                @else
                    {{-- One question at a time --}}
                    @foreach ($quiz['questions'] as $questionId => $question)
                        <div class="question-card2 question-slide" style="margin-top: 20px; display: none;" data-question-index="{{ $loop->index }}">
                            <h4>{{ $loop->iteration }}. {{ $question['question'] }}</h4>

                            {{-- MULTIPLE CHOICE --}}
                            @if (isset($question['type']) && $question['type'] === 'multiple_choice' && isset($question['options']))
                                @foreach ($question['options'] as $optionKey => $optionText)
                                    <div class="form-check">
                                        <input type="radio"
                                            name="answers[{{ $questionId }}]"
                                            value="{{ $optionKey }}"
                                            class="form-check-input"
                                            id="{{ $questionId }}-{{ $optionKey }}"
                                            required>
                                        <label class="form-check-label" for="{{ $questionId }}-{{ $optionKey }}">
                                            {{ $optionText }}
                                        </label>
                                    </div>
                                @endforeach

                            {{-- ESSAY --}}
                            @elseif (isset($question['type']) && $question['type'] === 'essay')
                                <textarea name="answers[{{ $questionId }}]" rows="4" class="form-control" required>SADSAD</textarea>

                            {{-- FILE UPLOAD --}}
                            @elseif (isset($question['type']) && $question['type'] === 'file_upload')
                                <input type="file" name="answers[{{ $questionId }}]" class="form-control-file" required>

                            {{-- FILL IN THE BLANK --}}
                            @elseif (isset($question['type']) && $question['type'] === 'fill_in_the_blank')
                                <input type="text" name="answers[{{ $questionId }}]" class="form-control" required>

                            {{-- DEFAULT (fallback) --}}
                            @else
                                <p style="color: red;">Unknown question type or missing data.</p>
                            @endif
                        </div>
                    @endforeach

                    <div class="quiz-navigation" style="margin-top: 30px;">
                        @if ($canGoBack)
                            <button type="button" id="prevBtn" class="btn btn-secondary" disabled>Previous</button>
                        @endif
                        <button type="button" id="nextBtn" class="btn btn-primary">Next</button>
                        <button type="submit" id="submitBtn" class="btn btn-success" style="display: none;">Submit Quiz</button>
                    </div>
                @endif
            </form>

            @else
                <div class="alert alert-warning" style="margin-top: 20px;">
                    @if ($hasReachedLimit)
                        You have used all your quiz attempts.
                    @elseif (!$isBeforeDeadline)
                        The quiz is no longer available.
                    @endif
                </div>
            @endif
        </div>
    </main>

</section>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const oneQuestionAtATime = @json($oneQuestionAtATime);
    const canGoBack = @json($canGoBack);

    if (oneQuestionAtATime) {
        const slides = document.querySelectorAll(".question-slide");
        let currentIndex = 0;

        const prevBtn = document.getElementById("prevBtn");
        const nextBtn = document.getElementById("nextBtn");
        const submitBtn = document.getElementById("submitBtn");

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.style.display = i === index ? "block" : "none";
            });

            // Disable prev button if at first question
            if (canGoBack) {
                prevBtn.disabled = index === 0;
            }

            // Show submit button only on last question
            if (index === slides.length - 1) {
                nextBtn.style.display = "none";
                submitBtn.style.display = "inline-block";
            } else {
                nextBtn.style.display = "inline-block";
                submitBtn.style.display = "none";
            }
        }


        if (slides.length > 0) {
            showSlide(currentIndex);
        }

        if (canGoBack && prevBtn) {
            prevBtn.addEventListener("click", () => {
                if (currentIndex > 0) {
                    currentIndex--;
                    showSlide(currentIndex);
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener("click", () => {
                if (currentIndex < slides.length - 1) {
                    currentIndex++;
                    showSlide(currentIndex);
                }
            });
        }
    }
});

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const timeLimitMinutes = {{ $quiz['time_limit'] ?? 0 }};
    const hasNoTimeLimit = {{ $quiz['no_time_limit'] ?? false ? 'true' : 'false' }};
    const quizId = '{{ $quiz["quiz_id"] }}';
    const quizKey = 'quiz_timer_' + quizId;
    const timerDisplay = document.getElementById("timer");
    const quizForm = document.querySelector("form");

    if (hasNoTimeLimit || timeLimitMinutes <= 0 || !timerDisplay || !quizForm) {
        timerDisplay.textContent = "No time limit";
        return;
    }

    let startTime = localStorage.getItem(quizKey);
    if (!startTime) {
        startTime = Date.now();
        localStorage.setItem(quizKey, startTime);
    } else {
        startTime = parseInt(startTime);
    }

    const timeLimitMillis = timeLimitMinutes * 60 * 1000;
    const endTime = startTime + timeLimitMillis;

    function updateTimer() {
        const now = Date.now();
        const remaining = endTime - now;

        if (remaining <= 0) {
            timerDisplay.textContent = "Time's up!";
            localStorage.removeItem(quizKey);
            quizForm.submit();
            return;
        }

        const minutes = Math.floor((remaining / 1000 / 60));
        const seconds = Math.floor((remaining / 1000) % 60);

        timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }

    updateTimer();
    const intervalId = setInterval(updateTimer, 1000);
});


</script>








