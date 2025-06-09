<!-- Modal overlay -->
<div class="modal-overlay" id="deleteConfirmModal" style="display: none;">
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

<!-- Admin Password Modal -->
<div class="modal-overlay" id="adminPasswordModal" style="display: none;">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Admin Verification</span>
    </div>
    <div class="modal-body">
      <p id="confirmMessage">Enter your admin password to delete the teacher:</p>
      <input type="password" id="adminPasswordInput" placeholder="Admin Password" class="form-control" />
      <p id="errorMessage" style="color: red; display: none; font-size: 0.9rem;">Incorrect password.</p>
    </div>
    <div class="modal-footer">
      <button class="btn cancel-btn" onclick="closeAdminPasswordModal()">Cancel</button>
      <button type="button" class="btn confirm-btn" onclick="verifyAdminPassword()">Confirm</button>
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
                            <a href="{{ url('mio/admin/EditTeacher/'.$item['teacherid']) }}">
                                <i class="fa fa-pencil"></i>
                            </a>

                            <button onclick="openModal('{{ url('mio/admin/DeleteTeacher/'.$item['teacherid']) }}', '{{ $item['fname'] }} {{ $item['lname'] }}')" class="open-btn">
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
function openModal(url, name) {
    deleteUrl = url;
    studentName = name;

    // Open admin password modal first
    document.getElementById("adminPasswordModal").style.display = "flex";
    document.getElementById("adminPasswordInput").value = '';
    document.getElementById("errorMessage").style.display = 'none';
}

function closeAdminPasswordModal() {
    document.getElementById('adminPasswordModal').style.display = 'none';
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmModal').style.display = 'none';
}

function verifyAdminPassword() {
    const enteredPassword = document.getElementById('adminPasswordInput').value;
    const email = '{{ session("firebase_user.email") }}';

    fetch("{{ route('mio.verify-admin-password') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ email: email, password: enteredPassword })
    })
    .then(response => {
        if (!response.ok) throw new Error("Invalid credentials");
        return response.json();
    })
    .then(data => {
        closeAdminPasswordModal();

        // Show delete confirmation modal
        document.getElementById("deleteConfirmModal").style.display = "flex";
        document.getElementById("confirmMessage").textContent = `Are you sure you want to remove "${studentName}"?`;
        document.getElementById("deleteTeacherForm").action = deleteUrl;
    })
    .catch(error => {
        document.getElementById('errorMessage').style.display = 'block';
    });
}
</script>
