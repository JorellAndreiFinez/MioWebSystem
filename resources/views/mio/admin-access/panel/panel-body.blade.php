
<section class="home-section">
      <div class="text">Admin Panel</div>

      <!-- BANNER -->
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

        <!-- FIRST ROW - GRID -->
        <div class="grid-row">
            <div class="main-overview">

            <a href="#"><div class="overviewcard">
                    <div class="overviewcard__icon">
                        <i class="bx bx-user"></i>
                    </div>
                    <div class="overviewcard__info">

                        <div class="info-num">
                        {{ $studentsCount }}
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
                        {{ $teachersCount }}
                        </div>
                        <div class="info-label">
                            Total Teachers
                        </div>
                    </div>
                    <div class="overviewcard__arrow">&rsaquo;</div>
            </div>
            </a>

            <a href="#"><div class="overviewcard">
                    <div class="overviewcard__icon">
                        <i class="bx bx-book"></i>
                    </div>
                    <div class="overviewcard__info">

                        <div class="info-num">
                        {{ $sectionsCount }}
                        </div>
                        <div class="info-label">
                            Total Sections
                        </div>
                    </div>
                    <div class="overviewcard__arrow">&rsaquo;</div>
            </div>
            </a>

            </div>
        <!--End Main Overview-->


        </div>

        <!-- SECOND ROW - GRID -->
        <div class="grid-row">
        <div class="second-row">
                <div class="analytics-card">
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
                                <canvas id="studentsChart1"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="analytics-card">
                <div class="card">
                    <h2>Scores</h2>
                    <div class="content">
                        <div class="legend">
                            <div class="legend-item"><span class="box blue"></span> Deaf</div>
                            <div class="legend-item"><span class="box light-blue"></span> Speech delay</div>
                            <div class="legend-item"><span class="box yellow"></span> SPED</div>
                            <div class="legend-item"><span class="box light-yellow"></span> Others</div>
                        </div>
                        <div class="chart-container">
                            <canvas id="studentsChart2"></canvas>
                        </div>
                    </div>
                </div>
            </div>
                </div>
        </div>


        <!-- THIRD ROW - GRID -->

        <div class="grid-row">
        <div class="third-row">
            <!-- DEPARTMENT LIST -->
            <div class="list-card">
                <div class="card">
                    <div class="card-header">
                        <span>Department List</span>
                        <span>➡</span>
                    </div>

                    @forelse ($departments as $dept)
                        <div class="list-item">
                            <span>{{ $dept['department_name'] ?? 'Unnamed Department' }}</span>
                            <div class="profile-group">
                                @php
                                    $teacherId = $dept['teacherid'] ?? null;
                                    $teacher = $teachers[$teacherId] ?? null;
                                @endphp

                                @if ($teacher)
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($teacher['fname'] . ' ' . $teacher['lname']) }}&background=random" alt="Profile" title="{{ $teacher['fname'] }} {{ $teacher['lname'] }}">
                                @else
                                    <span style="color: #999; font-size: 12px;">No teacher assigned</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="list-item">
                            <span>No departments available.</span>
                        </div>
                    @endforelse
                </div>
            </div>


            <!-- CLASSES LIST -->
            <div class="list-card">
            <div class="card">
                <div class="card-header">
                    <span>Classes</span>
                    <span>➡</span>
                </div>
                <div class="list-item">
                    <span>SEC-7A</span>
                    <span>8 Teachers | 48 Students</span>
                    <div class="profile-group">
                    <img src="https://i.pravatar.cc/40?img=10" alt="Profile">
                        <img src="https://i.pravatar.cc/40?img=12" alt="Profile">
                        <img src="https://i.pravatar.cc/40?img=13" alt="Profile">
                    </div>
                </div>
                <div class="list-item">
                    <span>SEC-7B</span>
                    <span>8 Teachers | 40 Students</span>
                    <div class="profile-group">
                    <img src="https://i.pravatar.cc/40?img=14" alt="Profile">
                        <img src="https://i.pravatar.cc/40?img=15" alt="Profile">
                        <img src="https://i.pravatar.cc/40?img=16" alt="Profile">
                    </div>
                </div>
                <div class="list-item">
                    <span>SEC-7C</span>
                    <span>8 Teachers | 38 Students</span>
                    <div class="profile-group">
                    <img src="https://i.pravatar.cc/40?img=17" alt="Profile">
                        <img src="https://i.pravatar.cc/40?img=18" alt="Profile">
                        <img src="https://i.pravatar.cc/40?img=19" alt="Profile">
                    </div>
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

        const ctx1 = document.getElementById('studentsChart1').getContext('2d');
        const ctx2 = document.getElementById('studentsChart2').getContext('2d');


        const studentsChart1 = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Deaf', 'Speech delay', 'SPED', 'Others'],
                datasets: [{
                    data: [30, 20, 25, 25],
                    backgroundColor: ['#3b82f6', '#93c5fd', '#facc15', '#fde68a'],
                    borderWidth: 2,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        const studentsChart2 = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Deaf', 'Speech delay', 'SPED', 'Others'],
                datasets: [{
                    data: [30, 20, 25, 25],
                    backgroundColor: ['#3b82f6', '#93c5fd', '#facc15', '#fde68a'],
                    borderWidth: 2,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

    </script>
