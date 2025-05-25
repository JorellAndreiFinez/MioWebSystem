<section class="home-section">
    <div class="text">Enrollment Assessment</div>

    <main class="main-banner">
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="welcome-banner">
            <div class="banner">
                <div class="content">
                    <h2>Welcome,<br> {{ $user['name'] ?? 'Student' }}!</h2>
                    <p>Prepare to take your enrollment assessment to move forward with your application.</p>
                </div>
                <div class="divider"></div>
                <div class="content">
                    <h2>School Year <br> 2024 - 2025</h2>
                    <p>June 2024 - March 2025<br>3rd Quarter</p>
                </div>
            </div>
        </div>
    </main>

    <div class="assessment-instructions" style="padding: 2rem; max-width: 800px; margin: auto;">
        <h3>Instructions</h3>
        <ul style="margin-left: 1.5rem;">
            <li>Make sure you are in a quiet environment.</li>
            <li>Read each question carefully before answering.</li>
            <li>You can only take the assessment once.</li>
            <li>Your answers will be reviewed by the administration.</li>
        </ul>

        <div style="margin-top: 2rem; text-align: center;">
            <form action="{{ route('enroll.assessment.start') }}" method="GET">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size: 16px;">
                    Take Assessment
                </button>
            </form>
        </div>
    </div>
</section>
