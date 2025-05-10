<section class="home-section">
    @include('mio.dashboard.breadcrumbs')

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
                    <a href="{{ route('mio.subject.module-body', ['subjectId' => $module['subject_id'], 'moduleIndex' => $module['module_index']]) }}">
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
