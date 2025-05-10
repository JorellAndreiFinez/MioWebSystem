
<section class="home-section">
<div class="text">
      <a href="{{ route('mio.subject') }}">Subject</a>
      > Announcement
      </div>
        <main class="main-banner">
            <div class="welcome-banner">
            <div class="banner">
            <div class="content">
            <h5>Announcement</h5>

            </div>

            </div>
            </div>
        </main>


        <div class="grid-container">

        <!-- Begin Main-->
        <main class="main">
            <!--Begin Main Overview-->
            <div class="main-overview">

            @foreach ($announcements as $announcementId => $announcement)
            <a href="{{ route('mio.subject.announcement-body', ['subjectId' => $subject['subject_id'], 'announcementId' => $announcementId]) }}">
                <div class="overviewcard">
                    <div class="overviewcard__icon"></div>
                    <div class="overviewcard__info">{{ $announcement['title'] ?? 'No Title' }}</div>
                    <div class="overviewcard__date">{{ $announcement['date_created'] ?? '' }}</div>
                    <div class="overviewcard__arrow">&rsaquo;</div>
                </div>
            </a>
        @endforeach



            </div>
        <!--End Main Overview-->


        </main>
        <!-- End Main -->
        </div>

  </section>


