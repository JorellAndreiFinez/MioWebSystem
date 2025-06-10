
<!-- Modal overlay -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Delete Schedule</span>
    </div>
    <div class="modal-body">
      <p id="confirmMessage">Are you sure you want to remove this schedule?</p>
    </div>
    <div class="modal-footer">
      <button class="btn cancel-btn" onclick="closeModal()">Cancel</button>

      <form id="deleteStudentForm" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn confirm-btn">Confirm</button>
      </form>

    </div>
  </div>
</div>



<section class="home-section">
<div class="text">Schedule</div>
<div class="teacher-container">
    <!-- HEADER CONTROLS -->
    <div class="table-header">
    <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
        </div>
        <div class="button-group">
            <button class="btn sort-btn" id="sortButton" onclick="toggleSort()">Newest ⬇</button>

            <button class="btn add-btn"><a href="{{ route('mio.AddSchedule') }}">+ New Schedule</a></button>
        </div>
    </div>

    <!-- TEACHER TABLE -->
   <div class="table-container">
   <table>
        <thead>
            <tr>
                <th>Schedule ID</th>
                <th>Schedule Name</th>
                <th>Teacher</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>


        </tbody>
    </table>
   </div>
</div>

</section>

<script>
function openModal(deleteUrl, studentName) {
    document.getElementById("confirmModal").style.display = "flex";

    // Dynamically set form action
    document.getElementById("deleteStudentForm").action = deleteUrl;

    // (Optional UX) Customize the confirmation message
    document.getElementById("confirmMessage").textContent = `Are you sure you want to remove "${studentName}"?`;
}

function closeModal() {
    document.getElementById("confirmModal").style.display = "none";
}
</script>

<script>
function searchCards() {
    let input = document.getElementById('searchBar').value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        let rowText = row.innerText.toLowerCase();
        if (rowText.includes(input)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<script>
    const originalSchedules = Object.values(@json($schedules));
</script>
<script>
     window.addEventListener('DOMContentLoaded', () => {
        renderSchedules();
    });
let currentSort = 'newest';

document.querySelector('#sortButton').addEventListener('click', () => {
    currentSort = currentSort === 'newest' ? 'oldest' : 'newest';
    document.querySelector('#sortButton').textContent = currentSort === 'newest' ? 'Newest ⬇' : 'Oldest ⬆';
    renderSchedules();
});

function renderSchedules() {
    const sortedSchedules = [...originalSchedules].sort((a, b) => {
        const dateA = new Date(a.created_at || '2000-01-01');
        const dateB = new Date(b.created_at || '2000-01-01');
        return currentSort === 'oldest' ? dateA - dateB : dateB - dateA;
    });

    const tbody = document.querySelector('tbody');
    tbody.innerHTML = '';

    if (sortedSchedules.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="no-data">No schedules found.</td></tr>`;
        return;
    }

    sortedSchedules.forEach(sched => {
        const teacher = sched.teacherid ?? 'TBA';
        tbody.innerHTML += `
        <tr>
            <td>${sched.scheduleid}</td>
            <td>${sched.schedule_name}</td>
            <td>${teacher}</td>
            <td class="action-icons">
                <a href="/mio/admin/EditSchedule/${sched.scheduleid}"><i class="fa fa-pencil"></i></a>
                <button onclick="openModal('/mio/admin/DeleteSchedule/${sched.scheduleid}', '${sched.schedule_name}')" class="open-btn">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>`;
    });
}
</script>



