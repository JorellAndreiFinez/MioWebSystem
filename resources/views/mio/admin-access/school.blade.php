<!-- Modal overlay -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Delete Announcement</span>
    </div>
    <div class="modal-body">
      <p>Are you sure you want to remove this announcement?</p>
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
    <div class="text">School</div>
    <section class="grade-section">
    <div class="grade-grid">
        <a href="{{ route('mio.view-schoolyear') }}">
        <div class="grade-card">
            <span class="icon"></span>
            <p>School Year</p>
            <span class="arrow">&rsaquo;</span>
        </div>
        </a>
       <a href="{{ route('mio.ViewDepartment') }}">
       <div class="grade-card">
            <span class="icon"></span>
            <p>Departments</p>
            <span class="arrow">&rsaquo;</span>
        </div>
       </a>

       <a href="{{ route('mio.ViewSection') }}">
        <div class="grade-card">
            <span class="icon"></span>
            <p>Section</p>
            <span class="arrow">&rsaquo;</span>
        </div>
        </a>

    </div>
    </section>

<section class="announcements-section">
@include('mio.dashboard.status-message')

    <div class="header" style="margin-top: 1.2rem">
        <h2 style="font-size: 2rem; margin-left: 1rem;">Announcements</h2>
        <a href="{{ route('mio.AddAnnouncement') }}">
        <button class="new-announcement">+ New Announcement</button>
        </a>
    </div>
    <div class="table-container">
   <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Title</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($announcements as $announcement)
                <tr>
                    <td>{{ $announcement['date'] }}</td>
                    <td>
                        <a href="{{ route('mio.view-announcement', ['id' => $announcement['id']]) }}">
                            {{ $announcement['title'] }}
                        </a>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($announcement['description'], 50) }}</td>
                    <td class="action-icons">
                        <a href="{{ route('mio.EditAnnouncement', ['id' => $announcement['id']]) }}">
                            <i class="fa fa-pencil"></i>
                        </a>
                        <button onclick="openModal('{{ url('mio/admin/DeleteAnnouncement/'.$announcement['id']) }}', '{{ $announcement['title'] }}')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No announcements found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
   </div>

</section>


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
