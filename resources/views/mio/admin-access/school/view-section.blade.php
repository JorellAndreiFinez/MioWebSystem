
<!-- Modal overlay -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Delete Admin</span>
    </div>
    <div class="modal-body">
      <p id="confirmMessage">Are you sure you want to remove this admin?</p>
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
<div class="text">Sections</div>
<div class="teacher-container">
    <!-- HEADER CONTROLS -->
    <div class="table-header">
    <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
        </div>
        <div class="button-group">
            <button class="btn sort-btn" id="sortButton" onclick="toggleSort()">Newest ⬇</button>
            <button class="btn add-btn"><a href="{{ route('mio.AddSection') }}">+ New Section</a></button>
        </div>
    </div>

    <!-- TEACHER TABLE -->
   <div class="table-container">
   <table>
        <thead>
            <tr>
                <th>Section ID</th>
                <th>Section Name</th>
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
    const originalSections = Object.values(@json($sections));
</script>
<script>
     window.addEventListener('DOMContentLoaded', () => {
        renderSections();
    });
let currentSort = 'newest';

document.querySelector('#sortButton').addEventListener('click', () => {
    currentSort = currentSort === 'newest' ? 'oldest' : 'newest';
    document.querySelector('#sortButton').textContent = currentSort === 'newest' ? 'Newest ⬇' : 'Oldest ⬆';
    renderSections();
});

function renderSections() {
    const sortedSections = [...originalSections].sort((a, b) => {
        const dateA = new Date(a.created_at || '2000-01-01');
        const dateB = new Date(b.created_at || '2000-01-01');
        return currentSort === 'oldest' ? dateA - dateB : dateB - dateA;
    });

    const tbody = document.querySelector('tbody');
    tbody.innerHTML = '';

    if (sortedSections.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="no-data">No sections found.</td></tr>`;
        return;
    }

    sortedSections.forEach(sect => {
        const teacher = sect.teacherid ?? 'TBA';
        tbody.innerHTML += `
        <tr>
            <td>${sect.sectionid}</td>
            <td>${sect.section_name}</td>
            <td>${teacher}</td>
            <td class="action-icons">
                <a href="/mio/admin/EditSection/${sect.sectionid}"><i class="fa fa-pencil"></i></a>
                <button onclick="openModal('/mio/admin/DeleteSection/${sect.sectionid}', '${sect.section_name}')" class="open-btn">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>`;
    });
}
</script>

