<section class="home-section">

<div class="text">

        <div class="breadcrumb-item active">Quizzes</div>

    </div>

<main class="main-banner">
            <div class="welcome-banner">
            <div class="banner">
            <div class="content">
            <h5>Quizzes</h5>

            </div>

            </div>
            </div>
        </main>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

        @include("mio.teacher-access.subject.quiz.academics-quiz")
       
        @if ($subject['subjectType'] === 'specialized' && $subject['specialized_type'] === 'auditory')
            <div class="grid-container">

            <!-- Begin Main-->
            <main class="main">
                <!--Begin Main Overview-->
                <div class="main-overview">

                <a href="{{ route('mio.subject-teacher.auditory-bingo', ['subjectId' => $subject['subject_id']]) }}"><div class="overviewcard">
                        <div class="overviewcard__icon"></div>
                        <div class="overviewcard__info">Bingo</div>
                        <div class="overviewcard__arrow">&rsaquo;</div>
                </div>
                </a>

                <a href="{{ route('mio.subject-teacher.auditory-matching', ['subjectId' => $subject['subject_id']]) }}"><div class="overviewcard">
                        <div class="overviewcard__icon"></div>
                        <div class="overviewcard__info">Matching Cards</div>
                        <div class="overviewcard__arrow">&rsaquo;</div>
                </div>
                </a>
                </div>
            <!--End Main Overview-->


            </main>
            <!-- End Main -->
        </div>
        @endif


        @if ($subject['subjectType'] === 'specialized' && $subject['specialized_type'] === 'speech')
            <div class="grid-container">

            <!-- Begin Main-->
            <main class="main">
                <!--Begin Main Overview-->
                <div class="main-overview">

                <a href="{{ route('mio.subject-teacher.speech-phrase', ['subjectId' => $subject['subject_id']]) }}"><div class="overviewcard">
                        <div class="overviewcard__icon"></div>
                        <div class="overviewcard__info">Reading</div>
                        <div class="overviewcard__arrow">&rsaquo;</div>
                </div>
                </a>
                <a href="{{ route('mio.subject-teacher.speech-picture', ['subjectId' => $subject['subject_id']]) }}"><div class="overviewcard">
                        <div class="overviewcard__icon"></div>
                        <div class="overviewcard__info">Picture</div>
                        <div class="overviewcard__arrow">&rsaquo;</div>
                </div>
                </a>

                <a href="{{ route('mio.subject-teacher.speech-question', ['subjectId' => $subject['subject_id']]) }}"><div class="overviewcard">
                        <div class="overviewcard__icon"></div>
                        <div class="overviewcard__info">Question</div>
                        <div class="overviewcard__arrow">&rsaquo;</div>
                </div>
                </a>

                </div>
            <!--End Main Overview-->


            </main>
            <!-- End Main -->
        </div>
        @endif

        @if ($subject['subjectType'] === 'specialized' && $subject['specialized_type'] === 'language')
            <div class="grid-container">

            <!-- Begin Main-->
            <main class="main">
                <!--Begin Main Overview-->
                <div class="main-overview">

                <a href="{{ route('mio.subject-teacher.language-fill', ['subjectId' => $subject['subject_id']]) }}"><div class="overviewcard">
                        <div class="overviewcard__icon"></div>
                        <div class="overviewcard__info">Fill</div>
                        <div class="overviewcard__arrow">&rsaquo;</div>
                </div>
                </a>

                <a href="{{ route('mio.subject-teacher.language-homonym', ['subjectId' => $subject['subject_id']]) }}"><div class="overviewcard">
                        <div class="overviewcard__icon"></div>
                        <div class="overviewcard__info">Homonyms</div>
                        <div class="overviewcard__arrow">&rsaquo;</div>
                </div>
                </a>
                </div>
            <!--End Main Overview-->


            </main>
            <!-- End Main -->
        </div>
        @endif





</section>


