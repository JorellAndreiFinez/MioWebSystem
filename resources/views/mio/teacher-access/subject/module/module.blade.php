<section class="home-section">
     <!-- BREADCRUMBS -->
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject-teacher.show-subject', ['subjectId' => $subject['subject_id']]) }}">
                {{ $subject['title'] }}
            </a>
        </div>

        <div class="breadcrumb-item active">Modules</div>
    </div>


    <main class="main-banner">
        <div class="welcome-banner">
            <div class="banner">
                <div class="content">
                    <h5>Modules</h5>
                </div>
            </div>
        </div>
    </main>

    <div class="grid-container">
        <!-- Begin Main-->
        <main class="main main-module">
            <!-- Begin Main Overview -->
            <div class="main-overview">
                @foreach ($modules as $module)
                    <a href="{{ route('mio.subject-teacher.module-body', ['subjectId' => $module['subject_id'], 'moduleIndex' => $module['module_index']]) }}">
                        <div class="overviewcard">
                            <div class="overviewcard__icon"></div>
                            <div class="overviewcard__info">[{{ $module['title'] }}]</div>
                            <div class="overviewcard__arrow">&rsaquo;</div>
                        </div>
                    </a>
                @endforeach
            </div>
            <!-- End Main Overview -->
        </main>
        <!-- End Main -->
    </div>
</section>
