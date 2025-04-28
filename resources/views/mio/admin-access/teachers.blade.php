<!-- Modal overlay -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Delete Teacher</span>
    </div>
    <div class="modal-body">
      <p id="confirmMessage">Are you sure you want to remove this teacher?</p>
    </div>
    <div class="modal-footer">
      <button class="btn cancel-btn" onclick="closeModal()">Cancel</button>

      <form id="deleteTeacherForm" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn confirm-btn">Confirm</button>
      </form>

    </div>
  </div>
</div>

<section class="home-section">
<div class="text">Teachers</div>

<div class="teacher-container">
    <!-- HEADER CONTROLS -->
    <div class="table-header">
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
        </div>
        <div class="button-group">
            <button class="btn sort-btn">Newest ⬇</button>
            <a href="{{ route('mio.AddTeacher') }}" class="btn add-btn">+ New Teacher</a>
        </div>
    </div>

    <!-- STUDENT TABLE -->
    <div class="table-container">
        @include('mio.dashboard.status-message')

        <table>
            <thead>
                <tr>
                    <th>Teacher ID</th>
                    <th>Name</th>
                    <th>Downloadable Files</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($teachers as $key => $item)
                @if (isset($item['role']) && $item['role'] === 'teacher')
                    <tr>
                        <td>{{ $item['teacherid'] }}</td>
                        <td>{{ $item['fname'] }} {{ $item['lname'] }}</td>
                        <td>
                            <button class="download-btn pdf-btn">PDF</button>
                            <button class="download-btn csv-btn">CSV</button>
                        </td>
                        <td class="action-icons">
                            <a href="{{ url('mio/admin1/EditTeacher/'.$item['teacherid']) }}">
                                <i class="fa fa-pencil"></i>
                            </a>

                            <button onclick="openModal('{{ url('mio/admin1/DeleteTeacher/'.$item['teacherid']) }}', '{{ $item['fname'] }} {{ $item['lname'] }}')" class="open-btn">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="4" class="no-data">No teachers found.</td>
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
    document.getElementById("deleteTeacherForm").action = deleteUrl;

    // (Optional UX) Customize the confirmation message
    document.getElementById("confirmMessage").textContent = `Are you sure you want to remove "${studentName}"?`;
}

function closeModal() {
    document.getElementById("confirmModal").style.display = "none";
}
</script>
