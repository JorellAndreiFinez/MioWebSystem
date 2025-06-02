<section class="home-section">
  <div class="text">Data Analytics</div>

  <!-- Mini Cards with Sparkline Charts -->
  <div class="summary-grid">
  <div class="summary-card">
    <div class="title">Total Students <span class="growth">+10%</span></div>
    <div class="count">1,003</div>
    <canvas id="studentsMiniChart" height="50"></canvas>
  </div>
  <div class="summary-card">
    <div class="title">Total Teachers <span class="growth">+2%</span></div>
    <div class="count">153</div>
    <canvas id="teachersMiniChart" height="50"></canvas>
  </div>
</div>

<!-- ✅ Move this outside -->
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
  </div>
</div>




  <!-- Full Chart Cards -->
  <div class="dashboard-grid">
    <!-- Doughnut Chart -->
    <div class="card">
      <h3>Students by Type</h3>
      <canvas id="studentsChart"></canvas>
    </div>

    <!-- Line Chart -->
    <div class="card">
      <h3>Student Development</h3>
      <canvas id="developmentChart"></canvas>
    </div>

    <!-- Progress Bars -->
    <div class="card">
      <h3>Student Progress</h3>
      <p>Training Exercises</p>
      <div class="progress-bar"><div class="progress-fill" style="width: 74%"></div></div>
      <p>Assignments</p>
      <div class="progress-bar"><div class="progress-fill" style="width: 52%"></div></div>
    </div>
  </div>
</section>

<script>
  // Mini line charts
  const miniOptions = {
    plugins: { legend: { display: false } },
    scales: { x: { display: false }, y: { display: false } },
    elements: { point: { radius: 0 }, line: { tension: 0.4 } }
  };

  new Chart(document.getElementById('studentsMiniChart'), {
    type: 'line',
    data: {
      labels: Array.from({length: 10}, (_, i) => i),
      datasets: [{
        data: [900, 920, 930, 950, 970, 980, 990, 1000, 1002, 1003],
        borderColor: '#fbbf24',
        backgroundColor: 'rgba(251,191,36,0.2)',
        fill: true
      }]
    },
    options: miniOptions
  });

  new Chart(document.getElementById('teachersMiniChart'), {
    type: 'line',
    data: {
      labels: Array.from({length: 10}, (_, i) => i),
      datasets: [{
        data: [140, 145, 147, 149, 150, 151, 152, 152, 153, 153],
        borderColor: '#fbbf24',
        backgroundColor: 'rgba(251,191,36,0.2)',
        fill: true
      }]
    },
    options: miniOptions
  });

  // Doughnut Chart
  new Chart(document.getElementById('studentsChart'), {
    type: 'doughnut',
    data: {
      labels: ['Deaf', 'Hard of Hearing', 'Speech Delay', 'Others'],
      datasets: [{
        data: [300, 150, 200, 100],
        backgroundColor: ['#3b82f6', '#93c5fd', '#fbbf24', '#fcd34d'],
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'right' }
      }
    }
  });

  // Line Chart
  new Chart(document.getElementById('developmentChart'), {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
      datasets: [
        {
          label: 'Speech',
          data: [60, 70, 55, 80, 75, 90],
          borderColor: '#fbbf24',
          fill: false
        },
        {
          label: 'Auditory',
          data: [40, 50, 60, 55, 65, 70],
          borderColor: '#3b82f6',
          fill: false
        },
        {
          label: 'Language',
          data: [50, 60, 70, 65, 60, 80],
          borderColor: '#10b981',
          fill: false
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'top' }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>
