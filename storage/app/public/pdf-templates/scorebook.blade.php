<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scorebook</title>
    <style>
            body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; margin: 40px; color: #333; }
            h1 { text-align: center; font-size: 38px; margin-bottom: 40px; text-transform: uppercase; }
            h3 { margin-top: 40px; font-size: 20px; border-bottom: 2px solid #2264DC; padding-bottom: 5px; color: #2264DC; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 30px; page-break-inside: avoid; }
            th, td { border: 1px solid #ccc; padding: 10px 12px; font-size: 14px; vertical-align: top; }
            th { background-color: #f5f5f5; color: #444; font-weight: 600; }
            td { background-color: #fff; }
            .feedback-cell { white-space: pre-wrap; }
        </style>
</head>
<body>
    <h1>STUDENT SPECIALIZED<br>ASSESSMENT</h1>

    @foreach($results as $result)
        <h3>{{ ucfirst($result['activity_type']) }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Answered At</th>
                    <th>MIÓ Score</th>
                    <th>Feedback</th>
                    <th>Low-Scoring Phonemes (≤ 50%)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $result['student_id'] }}</td>
                    <td>{{ $result['name'] }}</td>
                    <td>date</td>
                    <td>{{ $result['overall_score'] }}%</td>
                    <td class="feedback-cell">feedback</td>
                    <td>{!! nl2br(e($result['low_scoring_phonemes'])) !!}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <p style="text-align:center; margin-top:3em;">
        Generated on {{ now()->format('F j, Y, g:i A') }}
    </p>
</body>
</html>
