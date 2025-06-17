

<section class="home-section">
    <div class="text">Scores</div>

    <div class="teacher-container">

        <!-- Search and Legend -->
        <div class="search-legend-container">
            <div class="search-bar">
                <div class="table-header">
                    <div class="search-container">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
                    </div>
                    <div class="button-group">
                        <button id="generateReportBtn" class="primary-btn btn" style="margin-bottom: 1rem; margin-left: 3rem">
                            Generate Printable Report
                        </button>
                    </div>
                </div>
            </div>

            <div class="score-legend">
                <h5>Score Legend</h5>
                <ul class="legend-list">
                    @if ($subject['specialized_type'] === 'speech' && $subject['subjectType'] === 'specialized')
                        <li><strong>CEFR</strong>: A0 to C2 scale assessing language proficiency.</li>
                        <li><strong>IELTS</strong>: 0–9.0 band scores reflecting English skills.</li>
                        <li><strong>PTE</strong>: 10–90 scale focusing on fluency and clarity.</li>
                        <li><strong>TOEIC</strong>: 0-200 scale determining speaking in professional settings.</li>
                    @endif
                    <li><strong>MIÓ</strong>: 0-100 scale evaluating overall speech for assessment.</li>
                </ul>
            </div>
        </div>

        <!-- SHOW THIS FOR SPEECH -->
        @if ($specializedType === 'speech')
        <main class="main-scores">
            <div class="table-container">
                @forelse($groupedAttempts as $activityType => $attempts)
                    <div class="score-table" style="max-width: 900px; margin-bottom: 5rem;">
                        <h3>{{ $activityType === 'phrase' ? 'Reading' : ucfirst($activityType) }}</h3>

                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 30px;"></th>
                                    <th style="width: 180px;">Student ID</th>
                                    <th style="width: 180px;">Name</th>
                                    <th style="width: 180px;">Answered At</th>
                                    <th style="width: 180px;">MIÓ Score</th>
                                    <th style="width: 300px;">Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $attemptsByStudent = [];
                                    foreach ($attempts as $attempt) {
                                        $attemptsByStudent[$attempt['student_id']][] = $attempt;
                                    }
                                @endphp

                                @foreach($attemptsByStudent as $studentId => $studentAttempts)
                                    @php
                                        usort($studentAttempts, function ($a, $b) {
                                            return strtotime($b['answered_at']) - strtotime($a['answered_at']);
                                        });

                                        $recentAttempt = $studentAttempts[0];
                                        $latestAnsweredAt = $recentAttempt['answered_at'] ?? '-';

                                        $latestValid = collect($studentAttempts)->first(function ($attempt) {
                                            return !empty($attempt['pronunciation_details']['speechace_pronunciation_score']);
                                        }) ?? [];

                                        $studentFirstName = $studentAttempts[0]['student_first_name'] ?? '';
                                        $studentLastName = $studentAttempts[0]['student_last_name'] ?? '';
                                    @endphp

                                    <tr class="student-summary" data-student="{{ $studentId }}" data-activity-type="{{ $activityType }}">
                                        <td class="toggle-arrow" style="cursor:pointer; user-select:none;">▶</td>
                                        <td>{{ $studentId }}</td>
                                        <td>{{ $studentFirstName }} {{ $studentLastName }}</td>
                                        <td>{{ $latestAnsweredAt }}</td>
                                        <td>{{ isset($recentAttempt['mio_score']) ? number_format($recentAttempt['mio_score'], 2) : 'N/A' }}</td>
                                        <td>{{ $latestValid['pronunciation_details']['feedback'] ?? '-' }}</td>
                                    </tr>

                                    <tr class="student-details-row" data-student="{{ $studentId }}" data-activity-type="{{ $activityType }}" style="display:none; background-color: #f9f9f9;">
                                        <td colspan="{{ ($subject['specialized_type'] === 'speech') ? 9 : 5 }}" style="padding: 1rem;">
                                            <div class="student-details">
                                                <table style="width: 100%; border-collapse: collapse;">
                                                    <thead>
                                                        <tr>
                                                            <th>Audio</th>
                                                                <th>Text</th>
                                                                <th>CEFR</th>
                                                                <th>IELTS</th>
                                                                <th>PTE</th>
                                                                <th>TOEIC</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        @foreach($studentAttempts as $attempt)
                                                            <tr>
                                                                <td>
                                                                    @if(!empty($attempt['audio_url']))
                                                                        <audio controls style="width: 120px; height: 30px;">
                                                                            <source src="{{ $attempt['audio_url'] }}" type="audio/mpeg">
                                                                            Your browser does not support the audio element.
                                                                        </audio>
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>

                                                                @if($subject['specialized_type'] === 'speech')
                                                                    <td>
                                                                        @if(isset($attempt['pronunciation_details']['words']))
                                                                            <p style="margin:0;">
                                                                                @foreach ($attempt['pronunciation_details']['words'] as $wordInfo)
                                                                                   <span
                                                                                    class="word-info"
                                                                                    data-word="{{ $wordInfo['word'] }}"
                                                                                    data-quality-score="{{ $wordInfo['quality_score'] }}"
                                                                                    data-syllables='@json($wordInfo['syllables'])'
                                                                                    data-lowest-score="{{ $wordInfo['lowest_phoneme_score'] ?? $wordInfo['quality_score'] }}"
                                                                                    data-low-phones='@json($wordInfo['low_phones'] ?? [])'
                                                                                    style="border-bottom: 1px dotted #666; cursor: help;"
                                                                                    >
                                                                                    {{ $wordInfo['word'] }}
                                                                                    </span>


                                                                                @endforeach
                                                                            </p>

                                                                        @else
                                                                            {{ $attempt['pronunciation_details']['text'] ?? '-' }}
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ $attempt['pronunciation_details']['cefr_pronunciation_score'] ?? '-' }}</td>
                                                                    <td>{{ $attempt['pronunciation_details']['ielts_pronunciation_score'] ?? '-' }}</td>
                                                                    <td>{{ $attempt['pronunciation_details']['pte_pronunciation_score'] ?? '-' }}</td>
                                                                    <td>{{ $attempt['pronunciation_details']['toeic_pronunciation_score'] ?? '-' }}</td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    </tbody>

                                                </table>
                                            </div>

                                        </td>
                                    </tr>
                                     <!-- placeholder for SUMMARY row -->
                                    <tr class="word-summary-row" style="background-color: #eee;">
                                        <td colspan="6" class="word-summary-cell" data-student="{{ $studentId }}" data-activity-type="{{ $activityType }}">
                                            <!-- summary will be injected here -->
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @empty
                    <p>No attempts found for any activity type.</p>
                @endforelse
            </div>

        </main>

        @elseif($specializedType === 'auditory')

        <main class="main-scores">
    <div class="table-container">
        @forelse($groupedAttempts as $activityType => $attempts)
            @if(in_array($activityType, ['bingo', 'matching']))
                <div class="score-table" style="max-width: 900px; margin-bottom: 5rem;">
                    <h3>{{ ucfirst($activityType) }} (Auditory)</h3>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 30px;"></th>
                                <th style="width: 180px;">Student ID</th>
                                <th style="width: 180px;">Name</th>
                                <th style="width: 180px;">Started At</th>
                                <th style="width: 180px;">Completed At</th>
                                <th style="width: 120px;">Score</th>
                                <th style="width: 180px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $attemptsByStudent = [];
                                foreach ($attempts as $attempt) {
                                    $attemptsByStudent[$attempt['student_id']][] = $attempt;
                                }
                            @endphp

                            @foreach($attemptsByStudent as $studentId => $studentAttempts)
                                @php
                                    usort($studentAttempts, function ($a, $b) {
                                        return strtotime($b['started_at'] ?? '') - strtotime($a['started_at'] ?? '');
                                    });

                                    $recentAttempt = $studentAttempts[0];
                                    $startedAt = $recentAttempt['started_at'] ?? null;
                                    $completedAt = $recentAttempt['completed_at'] ?? null;
                                    $studentFirstName = $recentAttempt[0]['student_first_name'] ?? '';
                                    $studentLastName = $recentAttempt[0]['student_last_name'] ?? '';

                                    // Duration
                                    $duration = ($startedAt && $completedAt)
                                        ? (strtotime($completedAt) - strtotime($startedAt))
                                        : null;

                                    // Audio Playback Info
                                    $audioPlayed = $recentAttempt['audio_played'][0]['played_at'] ?? [];
                                    $audioPlayCount = count($audioPlayed);
                                    $audioFirstPlay = $audioPlayed[0] ?? null;
                                    $audioLastPlay = end($audioPlayed) ?: null;

                                    // Answered Items and Response Times
                                    $answeredItems = [];
                                    foreach ($recentAttempt['items'] ?? [] as $item) {
                                        if (!empty($item['selected_at'])) {
                                            $refTime = $audioFirstPlay ?? $startedAt;
                                            $responseTime = strtotime($item['selected_at']) - strtotime($refTime);
                                            $answeredItems[] = [
                                                'image_id' => $item['image_id'] ?? '',
                                                'selected_at' => $item['selected_at'],
                                                'response_time' => $responseTime > 0 ? $responseTime : 0
                                            ];
                                        }
                                    }
                                @endphp

                                <tr class="student-summary-auditory" data-student="{{ $studentId }}" data-activity-type="{{ $activityType }}">
                                    <td class="toggle-arrow" style="cursor:pointer; user-select:none;">▶</td>
                                    <td>{{ $studentId }}</td>
                                    <td>{{ $studentFirstName }} {{ $studentLastName }}</td>
                                    <td>{{ $startedAt ?? '-' }}</td>
                                    <td>{{ $completedAt ?? '-' }}</td>
                                    <td>{{ isset($recentAttempt['score']) ? number_format($recentAttempt['score'], 2) : 'N/A' }}</td>
                                    <td>{{ $recentAttempt['status'] ?? '-' }}</td>
                                </tr>

                                <tr class="student-details-row" data-student="{{ $studentId }}" data-activity-type="{{ $activityType }}" style="display:none; background-color: #f9f9f9;">
                                    <td colspan="7" style="padding: 1rem;">
                                        <div class="student-details">
                                            <strong>Audio Played:</strong> {{ $audioPlayCount }} time(s)<br>
                                            @if($audioFirstPlay)
                                                <strong>First Played At:</strong> {{ $audioFirstPlay }}<br>
                                            @endif
                                            @if($audioLastPlay)
                                                <strong>Last Played At:</strong> {{ $audioLastPlay }}<br>
                                            @endif
                                            @if($duration !== null)
                                                <strong>Total Duration:</strong> {{ $duration }} second(s)<br>
                                            @endif
                                            <br>
                                            <strong>Answered Items:</strong>
                                            <ul>
                                                @forelse($answeredItems as $item)
                                                    <li>
                                                        Item ID: {{ $item['image_id'] }} -
                                                        Selected at {{ $item['selected_at'] }}
                                                        (Response Time: {{ $item['response_time'] }}s)
                                                    </li>
                                                @empty
                                                    <li>No answered items found.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @empty
            <p>No scores available.</p>
        @endforelse
    </div>
</main>
        @endif

    </div>
</section>




<!-- SCRIPT -->

<script>
    // Optional toggle script to show/hide details
    document.querySelectorAll('.student-summary-auditory').forEach(row => {
        row.addEventListener('click', () => {
            const studentId = row.getAttribute('data-student');
            const activityType = row.getAttribute('data-activity-type');
            const detailRow = document.querySelector(`.student-details-row[data-student="${studentId}"][data-activity-type="${activityType}"]`);
            const arrow = row.querySelector('.toggle-arrow');
            if (detailRow.style.display === 'none') {
                detailRow.style.display = 'table-row';
                arrow.textContent = '▼';
            } else {
                detailRow.style.display = 'none';
                arrow.textContent = '▶';
            }
        });
    });
</script>

<script>
     const getColorByScore = (score) => {
    if (score >= 90) return '#3cb371'; // Green (Excellent)
    if (score >= 80) return '#32cd32'; // Green (Very good)
    if (score >= 70) return '#ffa500'; // Orange (Good)
    if (score >= 60) return '#ff4500'; // Red-Orange (Fair)
    return '#ff0000';                   // Red (Poor)
  };
document.addEventListener('DOMContentLoaded', () => {
 

  document.body.addEventListener('click', (event) => {
  const span = event.target.closest('.word-info');
  if (!span) return;

  // Remove previous summary if one exists
  document.querySelectorAll('.word-summary-popup').forEach(el => el.remove());

  const word = span.dataset.word || 'N/A';
  const qualityScore = span.dataset.qualityScore || 'N/A';

  let syllables = [];
  try {
    syllables = JSON.parse(span.dataset.syllables || '[]');
  } catch (e) {
    console.error('Invalid syllables JSON:', e);
  }

  // Build syllables HTML
  const syllablesHtml = syllables.map((syl, index) => {
    const color = getColorByScore(syl.quality_score ?? 0);
    const phones = syl.phones?.map(p => `/${p.phone}/ (${Math.round(p.quality_score)}%)`).join(', ') || '—';
    return `
      <div class="syllable-box"
        data-syllable-index="${index}"
        data-phones='${JSON.stringify(syl.phones || [])}'
        style="
          display: inline-block;
          margin: 0 4px;
          padding: 8px 12px;
          border-radius: 8px;
          background-color: ${color}33;
          text-align: center;
          min-width: 50px;
          font-family: monospace;
          cursor: pointer;
        ">
        <div style="font-weight: 700; font-size: 1.2rem; color: ${color};">${syl.letters}</div>
        <div style="font-size: 0.75rem; color: ${color};">${Math.round(syl.quality_score)}%</div>
      </div>`;
  }).join('') || `<div style="color: #999; font-style: italic;">No syllable data available</div>`;

  // Feedback logic
  let lowPhones = [];
  try {
    lowPhones = JSON.parse(span.dataset.lowPhones || '[]');
  } catch (e) {
    console.error('Invalid low_phones JSON:', e);
  }

  let lowestScore = 100;
  syllables.forEach(syl => {
    if (typeof syl.quality_score === 'number') {
      lowestScore = Math.min(lowestScore, syl.quality_score);
    }
  });

  let feedbackMsg = '';
  if (lowPhones.length > 0) {
    feedbackMsg += '❗ Low-scoring phonemes detected:<br>';
    lowPhones.forEach(p => {
      feedbackMsg += `&nbsp;&nbsp;&bull; <code>/${p.phone}/</code> &mdash; score: <strong>${p.score}</strong>, sounds like: <em>${p.like}</em><br>`;
    });
  } else if (lowestScore < 60) {
    feedbackMsg += '❗ Some sounds were unclear. Try focusing on your pronunciation.';
  } else if (lowestScore < 80) {
    feedbackMsg += '🔍 Good effort! A few sounds could be clearer.';
  } else {
    feedbackMsg += '✅ Excellent clarity throughout the word!';
  }

  // Build and insert the summary popup
  const summaryDiv = document.createElement('div');
  summaryDiv.className = 'word-summary-popup';
  summaryDiv.style = `
    background: #f8f8f8;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 10px;
    margin-top: 6px;
    max-width: 100%;
    font-family: Arial, sans-serif;
  `;
  summaryDiv.innerHTML = `
    <div style="font-size: 1.2rem; font-weight: bold; color: #007acc;">Word: ${word}</div>
    <div style="margin: 6px 0;">Quality Score: <strong>${qualityScore}</strong></div>
    <div style="display: flex; flex-wrap: wrap; gap: 6px;">${syllablesHtml}</div>
    <div style="margin-top: 10px; font-style: italic;">Feedback: ${feedbackMsg}</div>
    <div style="text-align: right; margin-top: 0.5rem;">
      <button onclick="this.closest('.word-summary-popup').remove()" style="
        cursor: pointer;
        background: #007acc;
        border: none;
        color: white;
        padding: 6px 14px;
        border-radius: 4px;
        font-weight: 600;">Close</button>
    </div>
  `;

  // Insert directly after the clicked span
    // Find the parent row of the clicked word
    const attemptRow = span.closest('tr');
    if (!attemptRow) return;

    // Remove any existing summary row within this student’s detail row
    const existingSummary = attemptRow.querySelector('.word-summary-popup');
    if (existingSummary) existingSummary.remove();

    // Create a full-width new row for the popup
    const summaryRow = document.createElement('tr');
    summaryRow.className = 'word-summary-popup';
    summaryRow.innerHTML = `
    <td colspan="${attemptRow.children.length}" style="padding: 1rem;">
        ${summaryDiv.outerHTML}
    </td>
    `;

    // Insert directly after the clicked word's row
    attemptRow.parentNode.insertBefore(summaryRow, attemptRow.nextSibling);

});


  // Close button handler
  document.body.addEventListener('click', (event) => {
    if (event.target && event.target.id === 'close-summary-btn') {
      const summaryCell = event.target.closest('.word-summary-cell');
      if (summaryCell) {
        summaryCell.innerHTML = '';
      }
    }
  });
});

document.body.addEventListener('click', (event) => {
  const syllableBox = event.target.closest('.syllable-box');
  if (!syllableBox) return;

  const phonesRaw = syllableBox.dataset.phones || '[]';
  let phones = [];
  try {
    phones = JSON.parse(phonesRaw);
  } catch (e) {
    return;
  }

  const list = phones.map(p => {
    const score = Math.round(p.quality_score || 0);
    const color = getColorByScore(score);
    return `
      <li style="margin-bottom: 4px;">
        <code style="color: ${color}">/${p.phone}/</code> – 
        <strong>${score}%</strong>, sounds like <em>${p.sound_most_like || '—'}</em>
      </li>`;
  }).join('');

  const dialog = document.createElement('div');
  dialog.innerHTML = `
    <div style="
      position: fixed;
      top: 20%;
      left: 50%;
      transform: translateX(-50%);
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      max-width: 400px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
      z-index: 9999;
      font-family: Arial, sans-serif;
    ">
      <h3 style="margin-top:0;">Syllable Phonemes</h3>
      <ul style="padding-left: 1rem; margin: 0 0 1rem 0;">${list}</ul>
      <button onclick="this.parentElement.remove()" style="
        background: #007acc;
        color: white;
        padding: 6px 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
      ">Close</button>
    </div>
  `;
  document.body.appendChild(dialog);
});

</script>



<!-- <div id="tooltip" class="tooltip"></div> -->


<script>
    const specializedType = @json($subject['specialized_type'] ?? null);
</script>

<!-- STUDENT SUMMARY FOR ALL -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggles = document.querySelectorAll('.student-summary .toggle-arrow');

        toggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const summaryRow = toggle.closest('.student-summary');
                const studentId = summaryRow.dataset.student;

                const activityType = summaryRow.dataset.activityType;
                const detailsRow = document.querySelector(`.student-details-row[data-student="${studentId}"][data-activity-type="${activityType}"]`);


                if (!detailsRow) return;

                if (detailsRow.style.display === 'none') {
                    detailsRow.style.display = 'table-row';
                    toggle.textContent = '▼';
                } else {
                    detailsRow.style.display = 'none';
                    toggle.textContent = '▶';
                }
            });
        });
    });
</script>



<!-- GENERATE REPORT -->
<script>
    document.getElementById('generateReportBtn').addEventListener('click', () => {
        const win = window.open('', '', 'width=1000,height=800');
        if (!win) return alert('Popup blocked! Please allow popups.');

        const attemptsData = @json($groupedAttempts);
        const specializedType = @json($subject['specialized_type'] ?? null);

        let reportHtml = `
        <html>
        <head>
            <title>MIÓ-Based Student Report</title>
            <style>
                body {
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    margin: 40px;
                    color: #333;
                }
                h1 {
                    text-align: center;
                    font-size: 38px;
                    margin-bottom: 40px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                h3 {
                    margin-top: 40px;
                    font-size: 20px;
                    border-bottom: 2px solid #2264DC;
                    padding-bottom: 5px;
                    color: #2264DC;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    margin-bottom: 30px;
                    page-break-inside: avoid;
                }
                th, td {
                    border: 1px solid #ccc;
                    padding: 10px 12px;
                    font-size: 14px;
                    vertical-align: top;
                }
                th {
                    background-color: #f5f5f5;
                    color: #444;
                    font-weight: 600;
                }
                td {
                    background-color: #fff;
                }
                .sub-table th, .sub-table td {
                    font-size: 13px;
                    padding: 6px 8px;
                }
                .sub-table {
                    margin-top: 10px;
                }
                .feedback-cell {
                    white-space: pre-wrap;
                }
                @media print {
                    body {
                        margin: 10mm;
                    }
                    h1 {
                        font-size: 24px;
                    }
                    table, th, td {
                        font-size: 12px;
                    }
                }
            </style>
        </head>
        <body>
            <h1>Student Specialized Assessment</h1>
        `;


        for (const [activityType, attempts] of Object.entries(attemptsData)) {
           reportHtml += `
                <table>

                    <caption style="margin-top: 2rem; caption-side: top; text-align: left; font-weight: bold; font-size: 24px; border-bottom: 2px solid #2264DC; margin-bottom: 1rem; color: #2264DC;" >
                        ${activityType.charAt(0).toUpperCase() + activityType.slice(1)}
                    </caption>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Answered At</th>
                            <th>MIÓ Score</th>
                            <th>Feedback</th>
                        </tr>
                    </thead>
            `;

            const attemptsByStudent = {};
            attempts.forEach(attempt => {
                if (!attemptsByStudent[attempt.student_id]) {
                    attemptsByStudent[attempt.student_id] = [];
                }
                attemptsByStudent[attempt.student_id].push(attempt);
            });

            for (const studentId in attemptsByStudent) {
                const studentAttempts = attemptsByStudent[studentId];
                studentAttempts.sort((a, b) => new Date(b.answered_at) - new Date(a.answered_at));
                const recentAttempt = studentAttempts[0];
                const latestValid = studentAttempts.find(a => a.pronunciation_details?.speechace_pronunciation_score) || {};
                const fullName = `${recentAttempt.student_first_name || ''} ${recentAttempt.student_last_name || ''}`;
                const answeredAt = recentAttempt.answered_at || '-';
                const mioScore = recentAttempt.mio_score !== undefined ? recentAttempt.mio_score.toFixed(2) : 'N/A';
                const feedback = latestValid.pronunciation_details?.feedback || '-';

                reportHtml += `
                    <tr>
                        <td>${studentId}</td>
                        <td>${fullName}</td>
                        <td>${answeredAt}</td>
                        <td>${mioScore}</td>
                        <td>${feedback}</td>
                    </tr>
                `;

                if (specializedType === 'speech') {
                    reportHtml += `
                        <tr style="background-color: #f9f9f9;">
                            <td colspan="5">
                                <table style="width:100%; margin-top: 1rem;">
                                    <thead>
                                        <tr>
                                            <th>Text</th>
                                            <th>CEFR</th>
                                            <th>IELTS</th>
                                            <th>PTE</th>
                                            <th>TOEIC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;

                    for (const attempt of studentAttempts) {
                        const pd = attempt.pronunciation_details || {};
                        const text = pd.text || '-';
                        const cefr = pd.cefr_pronunciation_score ?? '-';
                        const ielts = pd.ielts_pronunciation_score ?? '-';
                        const pte = pd.pte_pronunciation_score ?? '-';
                        const toeic = pd.toeic_pronunciation_score ?? '-';

                        reportHtml += `
                            <tr>
                                <td>${text}</td>
                                <td>${cefr}</td>
                                <td>${ielts}</td>
                                <td>${pte}</td>
                                <td>${toeic}</td>
                            </tr>
                        `;
                    }

                    reportHtml += `
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    `;
                }
            }

            reportHtml += `
                    </tbody>
                </table>
            `;
        }

        reportHtml += `
                <p><em>Generated on ${new Date().toLocaleString()}</em></p>
            </body>
            </html>
        `;

        win.document.write(reportHtml);
        win.document.close();
        win.focus();
        win.print();
    });
</script>


<!-- TOOLTIP -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tooltip = document.getElementById('tooltip');

    function showTooltip(event, content) {
        tooltip.innerHTML = content;
        tooltip.style.opacity = 1;
        tooltip.style.left = event.pageX + 15 + 'px';
        tooltip.style.top = event.pageY + 15 + 'px';
    }

    function hideTooltip() {
        tooltip.style.opacity = 0;
    }

    document.querySelectorAll('.word-info').forEach(el => {
        el.addEventListener('mouseenter', (event) => {
            const word = el.dataset.word;
            const qualityScore = el.dataset.qualityScore;
            let syllables = [];
            try {
            syllables = JSON.parse(span.dataset.syllables || '[]');
            if (!Array.isArray(syllables)) syllables = [];
            } catch (e) {
            console.error('Invalid syllables JSON:', e);
            syllables = [];
            }

            syllables.forEach(syl => {
                syllablesHtml += `<li><strong>${syl.letters}</strong> (Quality: ${syl.quality_score}, Stress: ${syl.stress_level})</li>`;
            });

            const content = `
                <strong>Word:</strong> ${word}<br>
                <strong>Quality Score:</strong> ${qualityScore}<br>
                <strong>Syllables:</strong> ${syllablesHtml}
            `;
            showTooltip(event, content);
        });

        el.addEventListener('mousemove', (event) => {
            tooltip.style.left = event.pageX + 15 + 'px';
            tooltip.style.top = event.pageY + 15 + 'px';
        });

        el.addEventListener('mouseleave', () => {
            hideTooltip();
        });
    });
});
</script>





<style>
    .word-info:hover {
  text-decoration: underline;
}

.word-summary-popup {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

</style>