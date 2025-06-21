<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Scorebook</title>
  <style>
    body { font-family: sans-serif; margin: 0; padding: 1em; }
    h1 { text-align: center; margin-bottom: 1em; }
    h2 { margin-top: 2em; font-size: 1.2em; color: #344BFD; border-bottom: 3px solid #344BFD }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 0.5em; text-align: left; background: #fff }
    thead { background: #fff; }
  </style>
</head>
<body>
  <h1>STUDENT SPECIALIZED<br>ASSESSMENT</h1>

  @php
    $grouped = collect($results)->groupBy('activity_type');
  @endphp

  @foreach($grouped as $activity => $rows)
    <h2>{{ ucfirst($activity) }}</h2>
    <table>
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Name</th>
          <th>MIÓ Score</th>
          <th>Low-Scoring Phonemes</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rows as $row)
          <tr>
            <td>{{ $row['student_id'] }}</td>
            <td>{{ $row['name'] }}</td>
            <td>{{ $row['overall_score'] }}%</td>
            <td>{!! nl2br(e($row['low_scoring_phonemes'])) !!}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endforeach

  <p style="text-align:center; margin-top:3em;">
    Generated on {{ now()->format('F j, Y, g:i A') }}
  </p>
</body>
</html>
