
<section class="home-section">

    <!-- BREADCRUMBS -->
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject.show-subject', ['subjectId' => $subjectId]) }}" >
                {{ $subject['title'] }}
            </a>
        </div>

        @if(isset($announcementId))
            <div class="breadcrumb-item">
                <a href="{{ route('mio.subject.announcements', ['subjectId' => $subjectId]) }}">
                    Announcements
                </a>
            </div>
        @else
            <div class="breadcrumb-item active" style="color: #474747;
">
                Announcements
            </div>
        @endif
    </div>




        <main class="main-banner adjustable-banner">
            <div class="welcome-banner">
            <div class="banner">
            <div class="content">
            <h5>Announcement</h5>

            </div>

            </div>
            </div>
        </main>

    @include('mio.dashboard.status-message')
        <div class="grid-container">

        <!-- Begin Main-->
        <main class="main">
            <!--Begin Main Overview-->
            <div class="main-overview">

        @foreach($announcements as $announcement)


              <a href="{{ route('mio.subject.announcements-body', [
                                'subjectId' => $announcement['subject_id'] ?? '',
                                'announcementId' =>  $announcement['id']
                            ]) }}">


                <div class="overviewcard">
                    <div class="overviewcard__icon"></div>
                    <div class="overviewcard__info">{{ $announcement['title'] ?? 'No Title' }}</div>
                    <div class="overviewcard__date">{{ $announcement['date_posted'] ?? '' }}</div>
                    <div class="overviewcard__arrow">&rsaquo;</div>
                </div>
            @endforeach




        <!--End Main Overview-->


        </main>
        <!-- End Main -->
        </div>

  </section>


