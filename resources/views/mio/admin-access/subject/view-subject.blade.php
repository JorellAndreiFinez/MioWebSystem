
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
@include('mio.dashboard.breadcrumbs', ['page' => 'view-subject'])
<div class="teacher-container">
@include('mio.dashboard.status-message')

    <!-- HEADER CONTROLS -->
    <div class="table-header">
    <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
        </div>
        <div class="button-group">
            <button class="btn sort-btn">Newest ⬇</button>
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
                <th>Teacher ID</th>
                <th>Section ID</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse($subjects as $subjectId => $subject)
        <tr>
            <td>{{ $subjectId }}</td>
            <td>{{ $subject['code'] }}</td>
            <td>{{ $subject['title'] }}</td>
            <td>{{ $subject['teacher_id'] }}</td>
            <td>{{ $subject['section_id'] }}</td>
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
