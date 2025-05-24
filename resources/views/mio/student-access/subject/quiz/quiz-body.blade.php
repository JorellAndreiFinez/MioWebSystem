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
        $maxAttempts = (int) ($quiz['attempts'] ?? 0);
        $studentAttempts = (int) ($quiz['people'][$studentId]['attempts'] ?? 0);
        $hasReachedLimit = $studentAttempts >= $maxAttempts;

        $deadlineDateTime = \Carbon\Carbon::parse($quiz['deadline'] . ' ' . ($quiz['end_time'] ?? '23:59'));
        $now = \Carbon\Carbon::now();
        $isBeforeDeadline = $now->lte($deadlineDateTime);

        $canTakeQuiz = !$hasReachedLimit && $isBeforeDeadline;

        $oneQuestionAtATime = $quiz['one_question_at_a_time'] ?? false;
        $canGoBack = $quiz['can_go_back'] ?? false;
    @endphp

     <!-- 🕒 Timer Moved Here -->
    <div id="countdown-timer" class="countdown-timer">
        Time Remaining: <span id="timer">Loading...</span>
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
                    <strong>{{ \Carbon\Carbon::parse($quiz['deadline'] . ' ' . $quiz['end_time'])->format('F j, Y g:i A') }}</strong>
                </div>
                <div>
                    <span>Attempts Allowed</span>
                    <strong>{{ $quiz['attempts'] }}</strong>
                </div>
                <div>
                    <span>Time Limit</span>
                    <strong>{{ $quiz['time_limit'] }} minutes</strong>
                </div>
            </div>

            <!-- route('mio.subject.quiz-submit', [$subjectId, $quizId])  -->
        </div>

        <div class="assigment-card2">
            @if ($canTakeQuiz)
                <form method="POST" action="#">
                @csrf

                @if (!$oneQuestionAtATime)
                    {{-- Show all questions at once --}}
                    @foreach ($quiz['questions'] as $questionId => $question)
                        <div class="question-card2" style="margin-top: 20px;">
                            <h4>{{ $loop->iteration }}. {{ $question['question'] }}</h4>
                            @foreach ($question['options'] as $optionKey => $optionText)
                                <div class="form-check2">
                                    <input type="radio"
                                        name="answers[{{ $questionId }}]"
                                        value="{{ $optionKey }}"
                                        class="form-check-input2"
                                        id="{{ $questionId }}-{{ $optionKey }}"
                                        required>
                                    <label class="form-check-label" for="{{ $questionId }}-{{ $optionKey }}">
                                        {{ $optionText }}
                                    </label>
                                </div>
                            @endforeach
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
    window.addEventListener("beforeunload", function (e) {
        e.preventDefault();
        e.returnValue = '';
    });

    document.addEventListener("DOMContentLoaded", function () {
        const timeLimitMinutes = {{ $quiz['time_limit'] ?? 0 }};
        const quizKey = 'quiz_timer_{{ $quiz["id"] }}';
        const now = Date.now();
        const quizForm = document.querySelector("form");
        const timerDisplay = document.getElementById("timer");

        // Load or initialize quiz start time
        let startTime = localStorage.getItem(quizKey);
        if (!startTime) {
            startTime = now;
            localStorage.setItem(quizKey, startTime);
        }

        const endTime = parseInt(startTime) + (timeLimitMinutes * 60 * 1000);

        function updateTimer() {
            const remaining = Math.max(0, Math.floor((endTime - Date.now()) / 1000));
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;

            timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

            if (remaining <= 0) {
                clearInterval(timerInterval);
                localStorage.removeItem(quizKey); // Clear timer state
                alert("Time is up! Your quiz will be submitted automatically.");
                quizForm.submit();
            }
        }

        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    });
</script>

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
