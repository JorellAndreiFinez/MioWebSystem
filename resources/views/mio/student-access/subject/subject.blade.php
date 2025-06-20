
<section class="home-section">
<h1 class="text">{{ $subject['title']  }}</h1>

        <main class="main-banner">
            <div class="welcome-banner">
            <div class="banner">
            <div class="content">
            <h5>Welcome back</h5>
                <h2>Welcome back,<br> John Doe!</h2>
                <p>Helping deaf children develop communication skills and confidence for a brighter future.</p>
            </div>
            <div class="divider"></div>

            </div>
            </div>
        </main>


<div class="grid-container">

  <!-- Begin Main-->
  <main class="main">
    <!--Begin Main Overview-->
    <div class="main-overview">

    <a href="{{ route('mio.subject.announcements', ['subjectId' => $subject['subject_id']]) }}"><div class="overviewcard">
            <div class="overviewcard__icon"></div>
            <div class="overviewcard__info">Announcements</div>
            <div class="overviewcard__arrow">&rsaquo;</div>
    </div>
    </a>

    <a  href="{{ route('mio.subject.assignment', ['subjectId' => $subject['subject_id']]) }}"><div class="overviewcard">
            <div class="overviewcard__icon"></div>
            <div class="overviewcard__info">Assignments</div>
            <div class="overviewcard__arrow">&rsaquo;</div>
    </div>
    </a>

    <a href="{{ route('mio.subject-teacher.attendance', ['subjectId' => $subject['subject_id']]) }}">
        <div class="overviewcard">
            <div class="overviewcard__icon"></div>
            <div class="overviewcard__info">Attendance</div>
            <div class="overviewcard__arrow">&rsaquo;</div>
        </div>
    </a>

    <a href="{{ route('mio.subject.scores', ['subjectId' => $subject['subject_id']]) }}"><div class="overviewcard">
            <div class="overviewcard__icon"></div>
            <div class="overviewcard__info">Scores</div>
            <div class="overviewcard__arrow">&rsaquo;</div>
    </div>
    </a>

    <a href="{{ route('mio.subject.modules', ['subjectId' => $subject['subject_id']]) }}">
        <div class="overviewcard">
            <div class="overviewcard__icon"></div>
            <div class="overviewcard__info">Modules</div>
            <div class="overviewcard__arrow">&rsaquo;</div>
        </div>
    </a>
    <a href="{{ route('mio.subject.quiz', ['subjectId' => $subject['subject_id']]) }}"><div class="overviewcard">
            <div class="overviewcard__icon"></div>
            <div class="overviewcard__info">Quizzes</div>
            <div class="overviewcard__arrow">&rsaquo;</div>
    </div>
    </a>

    <a href="{{ route('mio.subject.people', ['subjectId' => $subject['subject_id']]) }}">
        <div class="overviewcard">
            <div class="overviewcard__icon"></div>
            <div class="overviewcard__info">People</div>
            <div class="overviewcard__arrow">&rsaquo;</div>
        </div>
    </a>


    </div>
   <!--End Main Overview-->


  </main>
<!-- End Main -->
</div>

  </section>


