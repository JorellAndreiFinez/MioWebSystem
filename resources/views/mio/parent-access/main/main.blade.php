
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
        <div class="card-wrap">
            <a href="{{ route('mio.subject') }}" class="card-link">
                <div class="card">
                    <img src="https://www.radiancevisiongroup.com/assets/images/blogs/2d-and-3d-animation-in-mumbai-thane1.jpg" class="card-img" />
                    <div>
                        <h4>SPD701 Speech Development</h4>
                        <p>SEC-7A</p>
                    </div>
                </div>
 </a>
        </div>
            <div class="card-wrap">
                <div class="card">
                    <img src="https://www.radiancevisiongroup.com/assets/images/blogs/2d-and-3d-animation-in-mumbai-thane1.jpg" class="card-img" />
                    <div>
                        <h4>SPD702 Speech Development</h4>
                        <p>SEC-7B</p>
                    </div>
                </div>
            </div>
            <div class="card-wrap">
                <div class="card">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTfbEYWJwIfyiqseSNKVHkCPlsoSC8ToVHO8Ws3ThnMNpNA42_ScCo0CrT2t5zNM1cFqKc&usqp=CAU" class="card-img" />
                    <div>
                        <h4>ENG704 English Language</h4>
                        <p>SEC-7D</p>
                    </div>
                </div>
            </div>

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
                <h4>Student Tasks</h4>
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
