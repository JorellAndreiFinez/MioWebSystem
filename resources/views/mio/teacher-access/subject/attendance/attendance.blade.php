<section class="home-section">
     <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject-teacher.show-subject', ['subjectId' => $subject['subject_id']]) }}">
                {{ $subject['title'] }}
            </a>
        </div>

        <div class="breadcrumb-item active" style="font-size: 1.3rem;">Attendance</div>
    </div>


  <main class="main-assignment-content">
    <div class="container">
@php
    use Carbon\Carbon;
    $currentDate = Carbon::parse($attendanceDate);
    $previousDate = $currentDate->copy()->subDay()->toDateString();
    $nextDate = $currentDate->copy()->addDay()->toDateString();
@endphp

      {{-- Date Selection Form (separate) --}}
      <div class="attendance-date-selector mb-4 d-flex justify-content-between align-items-center">
            <div class="search-container d-flex align-items-center gap-2">
                <i class="fas fa-search"></i>
                <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
            </div>

            <div class="date-navigation d-flex align-items-center gap-2">
                {{-- Previous Date Button --}}
                <a href="{{ route('mio.subject-teacher.attendance', ['subjectId' => $subject['subject_id'], 'attendance_date' => $previousDate]) }}"
                class="btn btn-outline-secondary" title="Previous Day">
                    &larr;
                </a>

                {{-- Date Input --}}
                <form id="date-form" method="GET" action="{{ route('mio.subject-teacher.attendance', ['subjectId' => $subject['subject_id']]) }}">
                    <input type="date" name="attendance_date" id="attendance_date" class="form-control"
                        value="{{ old('attendance_date', $attendanceDate) }}"
                        onchange="document.getElementById('date-form').submit();">
                </form>

                {{-- Next Date Button --}}
                <a href="{{ route('mio.subject-teacher.attendance', ['subjectId' => $subject['subject_id'], 'attendance_date' => $nextDate]) }}"
                class="btn btn-outline-secondary" title="Next Day">
                    &rarr;
                </a>
            </div>
        </div>



      <div class="mb-3">
        <label for="status-filter"><strong>Filter by Status:</strong></label>
        <select id="status-filter" class="form-control" onchange="filterByStatus()">
            <option value="all" selected>All</option>
            <option value="present">Present</option>
            <option value="late">Late</option>
            <option value="absent">Absent</option>
        </select>
        </div>


     {{-- Attendance Form --}}
    <form action="{{ $attendanceId
    ? route('mio.subject-teacher.attendance.update', ['subjectId' => $subject['subject_id'], 'attendanceId' => $attendanceId])
    : route('mio.subject-teacher.attendance.store', ['subjectId' => $subject['subject_id']]) }}" method="POST">
    @csrf
        <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">

    <div class="attendance-card">
        <table>
        <thead>
            <tr>
            <th>Student ID</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($people as $studentId => $studentInfo)
            @php
            $studentAttendance = isset($attendance['people'][$studentId])
                ? $attendance['people'][$studentId]
                : ['status' => null]; // or 'absent' if you want to default
        @endphp

            <tr>
                <td>{{ $studentId }}</td>
                <td>{{ $studentInfo['last_name'] ?? '' }}</td>
                <td>{{ $studentInfo['first_name'] ?? '' }}</td>
                <td>
                <div
                    class="clickable-status status-{{ $studentAttendance['status'] ?? 'none' }}"
                    onclick="cycleStatus(this)"
                    data-student="{{ $studentId }}"
                >
                    {{ ucfirst($studentAttendance['status'] ?? 'Click') }}
                </div>
                <input type="hidden" name="people[{{ $studentId }}][status]" value="{{ $studentAttendance['status'] ?? '' }}">
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No students found for this subject.</td>
            </tr>
            @endforelse
        </tbody>
        </table>

        <div class="btn">
            <button type="submit" class="btn primary-btn">
                {{ $attendanceId ? 'Save' : 'Save (No Record Yet)' }}
            </button>
            <a href="{{ url()->previous() }}" class="btn cancel-btn">Cancel</a>
        </div>

    </div>
    </form>


    </div>
  </main>
</section>


<!-- FITERING -->

<script>
  function filterByStatus() {
    const selectedStatus = document.getElementById('status-filter').value;
    const rows = document.querySelectorAll('table tbody tr');

    rows.forEach(row => {
      const statusSelect = row.querySelector('select.status-dropdown');
      const currentStatus = statusSelect.value;

      if (selectedStatus === 'all' || currentStatus === selectedStatus) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  }

  function updateDropdownStyles() {
    const selects = document.querySelectorAll('select.status-dropdown');
    selects.forEach(select => {
      const status = select.value;
      select.classList.remove('status-present', 'status-late', 'status-absent');
      if (status === 'present') {
        select.classList.add('status-present');
      } else if (status === 'late') {
        select.classList.add('status-late');
      } else if (status === 'absent') {
        select.classList.add('status-absent');
      }
    });
  }

  // Run on page load
  document.addEventListener('DOMContentLoaded', updateDropdownStyles);

  // Run whenever dropdown changes
  document.querySelectorAll('select.status-dropdown').forEach(select => {
    select.addEventListener('change', () => {
      updateDropdownStyles();
    });
  });
</script>

<script>
  function cycleStatus(element) {
    const statuses = ['present', 'late', 'absent'];
    const studentId = element.dataset.student;
    const hiddenInput = document.querySelector(`input[name="people[${studentId}][status]"]`);

    let currentStatus = hiddenInput.value || 'none';
    let currentIndex = statuses.indexOf(currentStatus);
    let nextIndex = (currentIndex + 1) % statuses.length;
    let nextStatus = statuses[nextIndex];

    // Update visible text and classes
    element.textContent = capitalize(nextStatus);
    element.classList.remove(`status-${currentStatus}`, 'status-none');
    element.classList.add(`status-${nextStatus}`);

    // Update hidden input
    hiddenInput.value = nextStatus;
  }

  function capitalize(word) {
    return word.charAt(0).toUpperCase() + word.slice(1);
  }
</script>
