
<section class="home-section">
      <div class="text">Enrollment Dashboard</div>
        <main class="main-banner">
                @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="welcome-banner">
            <div class="banner">
            <div class="content">
                <h2>Welcome,<br> Jane Doe!</h2>
                <p>Helping deaf children develop communication skills and confidence for a brighter future.</p>
            </div>
            <div class="divider"></div>
            <div class="content">
                <h2>School Year <br> 2024 - 2025</h2>
                <p>June 2024 - March 2025<br>3rd Quarter</p>
            </div>
            </div>
            </div>
        </main>

    <div class="dashboard-grid">

        <div class="status-cards">

    <!-- Registration -->
    <div class="card revision">
        <div class="status-indicator
            {{ $enrollStatus === 'Assessment' ? 'green' : '' }}
            {{ $enrollStatus === 'Qualified' ? 'green' : '' }}
            {{ $enrollStatus === 'Revision' ? 'yellow' : '' }}
            {{ $enrollStatus === 'Registered' ? 'yellow' : '' }}
            {{ $enrollStatus === 'Rejected' ? 'red' : '' }}">
        </div>
        <div class="card-content">
            <h3>Registration</h3>
            <p>Enrollment Form</p>
        </div>
    </div>

    <!-- Evaluation -->
    <div class="card">
        <div class="status-indicator
            {{ $enrollStatus === 'Assessment' ? 'yellow' : '' }}
            {{ $enrollStatus === 'Enrolled' ? 'green' : '' }}
            {{ $enrollStatus === 'Qualified' ? 'green' : '' }}
            {{ $enrollStatus === 'Rejected' ? 'red' : '' }}">
        </div>
        <div class="card-content">
            <h3>Evaluation</h3>
            <p>Assessment Activity</p>
        </div>
    </div>

    <!-- Conclusion (Optional: Add logic here too if needed later) -->
    <div class="card">

        <div class="status-indicator
            {{ $enrollStatus === 'Qualified' ? 'yellow' : '' }}
            {{ $enrollStatus === 'Enrolled' ? 'green' : '' }}
            {{ $enrollStatus === 'Rejected' ? 'red' : '' }}"
        >
        </div>
        <div class="card-content">
            <h3>Conclusion</h3>
            <p>Enrollee Verification</p>
        </div>
    </div>
</div>


            <!-- admin feedback here -->
            <div class="admin-feedback-banner" style="margin-bottom: 3rem;">
            <h3 style="font-weight: 700;">Enrollment Feedback</h3>
            @if(!empty($adminFeedback))
                <p>{{ $adminFeedback }}</p>
            @else
                <p>No feedback from the admin yet.</p>
            @endif
        </div>


    </div>

  </section>
