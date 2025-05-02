
<!-- Modal overlay -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Delete Department</span>
    </div>
    <div class="modal-body">
      <p id="confirmMessage">Are you sure you want to remove this department?</p>
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
<div class="text">Departments</div>
<div class="teacher-container">
    <!-- HEADER CONTROLS -->
    <div class="table-header">
    <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
        </div>
        <div class="button-group">
            <button class="btn sort-btn">Newest ⬇</button>
            <button class="btn add-btn"><a href="{{ route('mio.AddDepartment') }}">+ New Department</a></button>
        </div>
    </div>

    <!-- TEACHER TABLE -->
   <div class="table-container">
   <table>
        <thead>
            <tr>
                <th>Department ID</th>
                <th>Department Name</th>
                <th>Teacher</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($departments as $department)
        <tr>
            <td>{{ $department['departmentid'] }}</td>
            <td>{{ $department['department_name'] }}</td>
            <td>{{ $department['teacherid'] ?? 'TBA' }} </td>
            <td class="action-icons">
                <a href="{{ route('mio.EditDepartment', ['id' => $department['departmentid']]) }}"><i class="fa fa-pencil"></i></a>

                <button onclick="openModal('{{ url('mio/admin/DeleteDepartment/'.$department['departmentid']) }}', '{{ $department['department_name'] }}')"
                    class="open-btn">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="no-data">No departments found.</td>
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
