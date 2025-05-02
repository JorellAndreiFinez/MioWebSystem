
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
            <button class="btn sort-btn">Newest ⬇</button>
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
        @forelse ($sections as $section)
        <tr>
            <td>{{ $section['sectionid'] }}</td>
            <td>{{ $section['section_name'] }}</td>
            <td>{{ $section['teacherid'] ?? 'TBA' }} </td>
            <td class="action-icons">
                <a href="{{ route('mio.EditSection', ['id' => $section['sectionid']]) }}"><i class="fa fa-pencil"></i></a>

                <button onclick="openModal('{{ url('mio/admin/DeleteSection/'.$section['sectionid']) }}', '{{ $section['section_name'] }}')"
                    class="open-btn">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="no-data">No sections found.</td>
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
