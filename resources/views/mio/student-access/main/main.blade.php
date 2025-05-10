
<section class="home-section">
      <div class="text">Dashboard</div>
      <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
        </div>

        <main class="main-banner">
            <div class="welcome-banner">
            <div class="banner">
            <div class="content">
                <h2>Welcome back,<br>  {{ session('firebase_user.name') }}</h2>
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

    @include("mio.dashboard.breadcrumbs")
        <div class="dashboard-grid">

        <div class="card-grid">
        <div class="top-label">
            <div class="label-container">
                <span class="label">Available Subjects</span>
                <select class="dropdown">
                    <option value="option1">All Subjects</option>
                    <option value="option2">Academics</option>
                    <option value="option3">Speech and Auditory</option>


                </select>
            </div>
        </div>
        @forelse($allSubjects as $gradeLevel => $subjects)
        @foreach($subjects as $subject)
            <div class="card-wrap">
                <a href="{{ route('mio.subject.show-subject', ['subjectId' => $subject['subject_id']]) }}" class="card-link"> <!-- Updated link to go to the subject overview -->
                    <div class="card">
                        <img src="{{ $subject['image_url'] ?? asset('images/default-subject.png') }}" class="card-img" />
                        <div>
                            <h4>{{ $subject['title'] ?? 'Untitled Subject' }}</h4>
                            <p>{{ $subject['section_id'] ?? 'No Section' }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    @empty
        <p style="text-align:center; margin-top: 2rem;">No subjects available yet.</p>
    @endforelse



        </div>

        <div class="right-side">
        <div class="announcement-card">
            <h4 >Announcements</h4>

            <div class="sub-card">
                <div class="announce-header">
                    <p class="announce-date">March 30, 2025</p> <!-- italic small gray -->
                    <h2 class="announce-subject">General</h2> <!-- small bold -->
                    <h3 class="announce-title">All Grade Homeroom Meeting</h3> <!-- slightly bigger bold -->
                </div>
            </div>

            <div class="sub-card">
                <div class="announce-header">
                    <p class="announce-date">Jan 10 2025</p> <!-- italic small gray -->
                    <h2 class="announce-subject">General</h2> <!-- small bold -->
                    <h3 class="announce-title">Grade 10 Seminar</h3> <!-- slightly bigger bold -->
                </div>
            </div>

            <div class="sub-card">
                <div class="announce-header">
                    <p class="announce-date">April 10 2025</p> <!-- italic small gray -->
                    <h2 class="announce-subject">English</h2> <!-- small bold -->
                    <h3 class="announce-title">Missing Assignments</h3> <!-- slightly bigger bold -->
                </div>
            </div>

            <div class="sub-card">
                <div class="announce-header">
                    <p class="announce-date">Apr 1 2025</p> <!-- italic small gray -->
                    <h2 class="announce-subject">General</h2> <!-- small bold -->
                    <h3 class="announce-title">No Classes</h3> <!-- slightly bigger bold -->
                </div>
            </div>
        </div>

            <div class="task-card">
                <h4>Assigned Tasks</h4>
                <div class="sub-card">
                    <div class="task-header">
                        <h3>Activity 1</h3>
                        <p class="task-date">Jan 10 2025</p>
                    </div>
                </div>

                <div class="sub-card">
                    <div class="task-header">
                        <h3>Speech Activity 2</h3>
                        <p class="task-date">Jan 10 2025</p>
                    </div>
                </div>

                <div class="sub-card">
                    <div class="task-header">
                        <h3>Quiz 1</h3>
                        <p class="task-date">Jan 10 2025</p>
                    </div>
                </div>

                <div class="sub-card">
                    <div class="task-header">
                        <h3>Undertaking</h3>
                        <p class="task-date">Jan 10 2025</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

  </section>

      <script>
        function searchCards() {
            let input = document.getElementById('searchBar').value.toLowerCase();
            let cards = document.querySelectorAll('.card-wrap');

            cards.forEach(card => {
                let title = card.querySelector('h3').innerText.toLowerCase();
                let description = card.querySelector('p').innerText.toLowerCase();

                if (title.includes(input) || description.includes(input)) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        }
    </script>
