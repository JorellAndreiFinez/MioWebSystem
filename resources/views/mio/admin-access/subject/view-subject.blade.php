
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
    <!-- BREADCRUMBS -->
<div class="teacher-container">
<div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subjects') }}">
                Subjects
            </a>
        </div>
        <div class="breadcrumb-item active">{{ $gradeLevel['name'] }}</div>

    </div>

    <!-- HEADER CONTROLS -->
    <div class="table-header">
    <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
        </div>
        <div class="button-group">
            <button class="btn sort-btn" onclick="sortTableByNewest()">Newest ⬇</button>
            <button class="btn add-btn"><a href="{{ route('mio.AddSubject', ['grade' => $grade]) }}">+ New Subject</a></button>
        </div>
    </div>

    <!-- TEACHER TABLE -->
   <div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Subject ID</th>
                <th>Subject Code</th>
                <th>Title</th>
                <th>Teacher</th> {{-- Changed from Teacher ID --}}
                <th>Section ID</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse($subjects as $subjectId => $subject)
            <tr data-date="{{ $subject['date_created'] }}">
                <td>{{ $subjectId }}</td>
                <td>{{ $subject['code'] }}</td>
                <td>{{ $subject['title'] }}</td>
                <td title="{{ $subject['teacher_id'] }}">{{ $subject['teacher_name'] }}</td>
                <td title="{{ $subject['section_id'] }}">{{ $subject['section_name'] }}</td>
                <td class="action-icons">
                    <a href="{{ route('mio.EditSubject', ['grade' => $grade, 'subjectId' => $subjectId]) }}">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <button onclick="openModal('{{ url('mio/admin/subjects/'.$grade.'/DeleteSubject/'.$subjectId) }}', '{{ $subject['title'] }}')" class="open-btn">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="no-data">No subjects found.</td>
            </tr>
        @endforelse
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
    const searchInput = document.getElementById("searchBar").value.toLowerCase();
    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        const teacherId = row.children[0].textContent.toLowerCase();
        const code = row.children[1].textContent.toLowerCase();
        const title = row.children[2].textContent.toLowerCase();
        const teacher = row.children[3].textContent.toLowerCase();

        if (teacherId.includes(searchInput) || code.includes(searchInput) || title.includes(searchInput) || teacher.includes(searchInput)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>

<script>
let sortNewestFirst = true;

function sortTableByNewest() {
    const tbody = document.querySelector("table tbody");
    const rows = Array.from(tbody.querySelectorAll("tr")).filter(row => row.querySelector("td")); // Skip no-data row

    rows.sort((a, b) => {
        const dateA = new Date(a.getAttribute("data-date"));
        const dateB = new Date(b.getAttribute("data-date"));

        return sortNewestFirst ? dateB - dateA : dateA - dateB;
    });

    rows.forEach(row => tbody.appendChild(row));

    // Toggle sort direction for next click
    sortNewestFirst = !sortNewestFirst;

    // Update button text
    document.querySelector(".sort-btn").innerText = sortNewestFirst ? "Newest ⬇" : "Oldest ⬆";
}
</script>

