
<section class="home-section">
      <div class="text">Admin Panel</div>

      <!-- BANNER -->
        <main class="main-banner">
            <div class="welcome-banner">
            <div class="banner">
            <div class="content">
                <h2>Welcome back,<br> John Doe!</h2>
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

        <!-- FIRST ROW - GRID -->
        <main class="main">
            <!--Begin Main Overview-->
            <div class="main-overview">

            <a href="#"><div class="overviewcard">
                    <div class="overviewcard__icon">
                        <i class="bx bx-user"></i>
                    </div>
                    <div class="overviewcard__info">

                        <div class="info-num">
                            32
                        </div>
                        <div class="info-label">
                            Total Students
                        </div>
                    </div>
                    <div class="overviewcard__arrow">&rsaquo;</div>
            </div>
            </a>

            <a href="#"><div class="overviewcard">
                    <div class="overviewcard__icon">
                        <i class="bx bxs-graduation"></i>
                    </div>
                    <div class="overviewcard__info">

                        <div class="info-num">
                            32
                        </div>
                        <div class="info-label">
                            Total Teachers
                        </div>
                    </div>
                    <div class="overviewcard__arrow">&rsaquo;</div>
            </div>
            </a>



            </div>
        <!--End Main Overview-->


        </main>

        <!-- SECOND ROW - GRID -->
        <div class="dashboard-grid">
            <div class="analytics-card">
                <h4 >Cards</h4>

                <div class="card">
                <h2>Students</h2>
                <div class="content">
                    <div class="legend">
                        <div class="legend-item"><span class="box blue"></span> Deaf</div>
                        <div class="legend-item"><span class="box light-blue"></span> Speech delay</div>
                        <div class="legend-item"><span class="box yellow"></span> SPED</div>
                        <div class="legend-item"><span class="box light-yellow"></span> Others</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="studentsChart"></canvas>
                    </div>
                </div>
            </div>

            </div>

            <div class="right-side">
                <div class="announcement-card">
                    <h4 >Announcements</h4>

                    <div class="sub-card">
                        <div class="announce-header">
                            <p class="announce-date">Jan 10 2025</p> <!-- italic small gray -->
                            <h2 class="announce-subject">General or Specified Subject</h2> <!-- small bold -->
                            <h3 class="announce-title">Announcement Title</h3> <!-- slightly bigger bold -->
                        </div>
                    </div>

                    <div class="sub-card">
                        <div class="announce-header">
                            <p class="announce-date">Jan 10 2025</p> <!-- italic small gray -->
                            <h2 class="announce-subject">General or Specified Subject</h2> <!-- small bold -->
                            <h3 class="announce-title">Announcement Title</h3> <!-- slightly bigger bold -->
                        </div>
                    </div>

                    <div class="sub-card">
                        <div class="announce-header">
                            <p class="announce-date">Jan 10 2025</p> <!-- italic small gray -->
                            <h2 class="announce-subject">General or Specified Subject</h2> <!-- small bold -->
                            <h3 class="announce-title">Announcement Title</h3> <!-- slightly bigger bold -->
                        </div>
                    </div>

                    <div class="sub-card">
                        <div class="announce-header">
                            <p class="announce-date">Jan 10 2025</p> <!-- italic small gray -->
                            <h2 class="announce-subject">General or Specified Subject</h2> <!-- small bold -->
                            <h3 class="announce-title">Announcement Title</h3> <!-- slightly bigger bold -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- THIRD ROW - GRID -->

        <main class="main">
            <!--Begin Main Overview-->
            <div class="main-overview">

            <a href="#"><div class="overviewcard">
                    <div class="overviewcard__icon"></div>
                    <div class="overviewcard__info">Announcements</div>
                    <div class="overviewcard__arrow">&rsaquo;</div>
            </div>
            </a>

            <a href="#"><div class="overviewcard">
                    <div class="overviewcard__icon"></div>
                    <div class="overviewcard__info">Assignments</div>
                    <div class="overviewcard__arrow">&rsaquo;</div>
            </div>
            </a>


            </div>
        <!--End Main Overview-->


        </main>

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
