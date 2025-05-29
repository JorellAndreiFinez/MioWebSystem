<section class="home-section">
   <div class="text">Scores</div>
     <!-- HEADER CONTROLS -->

    <div class="teacher-container">



        <!-- Legend Section -->


        <div class="search-legend-container">
            <div class="search-bar">
                 <div class="table-header">
                    <div class="search-container">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
                    </div>
                    <div class="button-group">
                    <button id="generateReportBtn" class="primary-btn btn" style="margin-bottom: 1rem; margin-left: 3rem">Generate Printable Report</button>
                    </div>
                </div>
            </div>

            <div class="score-legend">
                <h5>Score Legend</h5>
            <ul class="legend-list">
                <li><strong>CEFR</strong>: A0 to C2 scale assessing language proficiency.</li>
                <li><strong>IELTS</strong>: 0–9.0 band scores reflecting English skills.</li>
                <li><strong>PTE</strong>: 10–90 scale focusing on fluency and clarity.</li>
                <li><strong>TOEIC</strong>: 0-200 scale determining speaking in professional settings.</li>
                <li><strong>MIÓ </strong>: 0-100 scale evaluating overall speech for assessment</li>

            </ul>
            </div>
        </div>



        <main class="main-scores">
            <div class="table-container">
                <div class="score-table" style="max-width: 900px;">
                    @forelse($groupedAttempts as $activityType => $attempts)
                        <h3 >{{ ucfirst($activityType) }}</h3>
                        <table style="margin-bottom: 5rem;">
                            <thead>
                                <tr>
                                    <th style="width: 30px;"></th> <!-- toggle arrow narrow -->
                                    <th style="width: 180px;">Student ID</th>
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
                                        // Sort descending by answered_at
                                        usort($studentAttempts, function ($a, $b) {
                                            return strtotime($b['answered_at']) - strtotime($a['answered_at']);
                                        });

                                        $recentAttempt = $studentAttempts[0];
                                        $latestAnsweredAt = $recentAttempt['answered_at'] ?? '-';

                                        // Find latest attempt with valid pronunciation score (for feedback)
                                        $latestValid = collect($studentAttempts)->first(function ($attempt) {
                                            return !empty($attempt['pronunciation_details']['speechace_pronunciation_score']);
                                        }) ?? [];
                                    @endphp

                                    <tr class="student-summary" data-student="{{ $studentId }}">

                                        <td style="cursor:pointer; user-select:none;" class="toggle-arrow">▶</td>

                                        <td>{{ $studentId }}</td>
                                        <td>{{ $latestAnsweredAt ?? '-' }}</td>

                                      @php
                                            $latestValid = collect($studentAttempts)->first(function ($attempt) {
                                                return !empty($attempt['pronunciation_details']['speechace_pronunciation_score']);
                                            });
                                        @endphp

                                        <td>{{ isset($recentAttempt['mio_score']) ? number_format($recentAttempt['mio_score'], 2) : 'N/A' }}</td>
                                        <td>{{ $latestValid['pronunciation_details']['feedback'] ?? '-' }}</td>


                                    </tr>

                                    <tr class="student-details-row" data-student="{{ $studentId }}" style="display:none; background-color: #f9f9f9;">
                                        <td colspan="{{ $activityType === 'pronunciation' && $subject['specialized_type'] === 'speech' ? 9 : 5 }}" style="padding: 1rem;">
                                            <div class="student-details">
                                                <table style="width: 100%; border-collapse: collapse;">
                                                    <thead>
                                                        <tr>
                                                            <th>Audio</th>
                                                            @if($activityType === 'pronunciation' && $subject['specialized_type'] === 'speech')
                                                                <th>Text</th>
                                                                <th>CEFR</th>
                                                                <th>IELTS</th>
                                                                <th>PTE</th>
                                                                <th>TOEIC</th>
                                                            @endif
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

                                                                @if($activityType === 'pronunciation' && $subject['specialized_type'] === 'speech')
                                                                    <td>
                                    @if (isset($attempt['pronunciation_details']['words']))
                                        <p style="margin:0;">
                                        @foreach ($attempt['pronunciation_details']['words'] as $wordIndex => $wordInfo)
                                            <span
                                                class="word-info"
                                                data-word="{{ $wordInfo['word'] }}"
                                                data-quality-score="{{ $wordInfo['quality_score'] }}"
                                                data-syllables='@json($wordInfo['syllables'])'
                                                style="border-bottom: 1px dotted #666; cursor: help;"
                                            >
                                                {{ $wordInfo['word'] }}
                                            </span>
                                            {{-- Add a space after each word --}}
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
                           @endforeach
                            </tbody>
                        </table>
                    @empty
                        <p>No attempts found for any activity type.</p>
                    @endforelse
                </div>
            </div>
        </main>
    </div>

    <div id="printableReport" style="display:none; padding: 20px; font-family: Arial, sans-serif; max-width: 900px;">
        <h2>MIÓ Activity Report</h2>
        <div id="reportContent"></div>
    </div>

</section>
<script>
    const specializedType = @json($subject['specialized_type'] ?? null);
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggles = document.querySelectorAll('.student-summary .toggle-arrow');

        toggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const summaryRow = toggle.closest('.student-summary');
                const studentId = summaryRow.dataset.student;

                const detailsRow = document.querySelector(`.student-details-row[data-student="${studentId}"]`);

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

<div id="tooltip" class="tooltip"></div>

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
            const syllables = JSON.parse(el.dataset.syllables);

            let syllablesHtml = '<ul>';
            syllables.forEach(syl => {
                syllablesHtml += `<li><strong>${syl.letters}</strong> (Quality: ${syl.quality_score}, Stress: ${syl.stress_level})</li>`;
            });
            syllablesHtml += '</ul>';

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

<script>
document.getElementById('generateReportBtn').addEventListener('click', () => {
    const reportContent = document.getElementById('reportContent');
    reportContent.innerHTML = ''; // clear old

    const attemptsData = @json($groupedAttempts);
    const specializedType = @json($subject['specialized_type'] ?? null);

    for (const [activityType, attempts] of Object.entries(attemptsData)) {
        let html = `<h3>${activityType.charAt(0).toUpperCase() + activityType.slice(1)} </h3>`;

        const hasSpeechColumns = (activityType === 'pronunciation' && specializedType === 'speech');

        html += `<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 2rem;">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Answered At</th>
                            <th>MIÓ Score</th>
                            <th>Feedback</th>
                        </tr>
                    </thead>
                    <tbody>`;

        // Group attempts by student id
        const attemptsByStudent = {};
        attempts.forEach(attempt => {
            if (!attemptsByStudent[attempt.student_id]) {
                attemptsByStudent[attempt.student_id] = [];
            }
            attemptsByStudent[attempt.student_id].push(attempt);
        });

        for (const studentId in attemptsByStudent) {
            const studentAttempts = attemptsByStudent[studentId];

            // Sort by answered_at descending
            studentAttempts.sort((a, b) => new Date(b.answered_at) - new Date(a.answered_at));

            const latestAnsweredAt = studentAttempts.length > 0 ? studentAttempts[0].answered_at : '-';
            const recentAttempt = studentAttempts[0]; // most recent
            const latestValid = studentAttempts.find(attempt => attempt.pronunciation_details?.speechace_pronunciation_score) || {};

            html += `<tr>
                        <td>${studentId}</td>
                        <td>${latestAnsweredAt || '-'}</td>
                        <td>${typeof recentAttempt.mio_score !== 'undefined' ? recentAttempt.mio_score.toFixed(2) : 'N/A'}</td>
                        <td>${latestValid.pronunciation_details?.feedback ?? '-'}</td>
                    </tr>`;


            const colspan = hasSpeechColumns ? 5 : 4; // Adjust colspan for detail row

            html += `<tr style="background-color:#f9f9f9;">
                        <td colspan="${colspan}" style="padding: 1rem;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>`;

            // Remove Audio column header entirely
            // if hasSpeechColumns, only include text and scores columns
            if (hasSpeechColumns) {
                html += `
                                        <th>Text</th>
                                        <th>CEFR</th>
                                        <th>IELTS</th>
                                        <th>PTE</th>
                                        <th>TOEIC</th>`;
            }

            html += `          </tr>
                                </thead>
                                <tbody>`;

            for (const attempt of studentAttempts) {
                // Remove audio cell, so just start with empty string if no speech columns,
                // else start the row with nothing (because no audio column)
                html += `<tr>`;

                if (hasSpeechColumns) {
                    let textContent = '-';
                    if (attempt.pronunciation_details?.words) {
                        textContent = attempt.pronunciation_details.words.map(w => w.word).join(' ');
                    } else if (attempt.pronunciation_details?.text) {
                        textContent = attempt.pronunciation_details.text;
                    }

                    html += `<td>${textContent}</td>
                             <td>${attempt.pronunciation_details?.cefr_pronunciation_score ?? '-'}</td>
                             <td>${attempt.pronunciation_details?.ielts_pronunciation_score ?? '-'}</td>
                             <td>${attempt.pronunciation_details?.pte_pronunciation_score ?? '-'}</td>
                             <td>${attempt.pronunciation_details?.toeic_pronunciation_score ?? '-'}</td>`;
                }

                html += `</tr>`;
            }

            html += `       </tbody>
                            </table>
                        </td>
                    </tr>`;
        }

        html += `   </tbody>
                </table>`;

        reportContent.innerHTML += html;
    }

    // Show printable section and print
    const printableReport = document.getElementById('printableReport');
    printableReport.style.display = 'block';

    window.print();

    // Optionally hide printable report after print (for UX)
    printableReport.style.display = 'none';
});

</script>




