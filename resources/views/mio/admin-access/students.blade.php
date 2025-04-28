<!-- Modal overlay -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Delete Student</span>
    </div>
    <div class="modal-body">
      <p>Are you sure you want to remove this student?</p>
    </div>
    <div class="modal-footer">
      <button class="btn cancel-btn" onclick="closeModal()">Cancel</button>
      <button class="btn confirm-btn">Confirm</button>
    </div>
  </div>
</div>

<section class="home-section">
<div class="text">Students</div>
@if (session(key: 'status'))
    <div class="alert alert-success" id="alert-message">
        {{ session(key: 'status') }}
    </div>

@endif
<div class="teacher-container">
    <!-- HEADER CONTROLS -->
    <div class="table-header">
    <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
        </div>
        <div class="button-group">
        <button class="btn sort-btn">Newest ⬇</button>
        <!-- Corrected 'Add Student' Button Link -->
        <a href="{{ route('mio.AddStudent') }}" class="btn add-btn">+ New Student</a>
    </div>
    </div>

    <!-- TEACHER TABLE -->
   <div class="table-container">
   <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Downloadable Files</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
           @forelse ($students as $key => $item)
           <tr>
                <td>{{ $item['studentid'] }}</td>
                <td>{{ $item['fname'] }} {{ $item['lname'] }}</td>
                <td>
                    <button class="download-btn pdf-btn">PDF</button>
                    <button class="download-btn csv-btn">CSV</button>
                </td>

                <td class="action-icons">
                <a href="{{ url('mio/admin1/EditStudent/'.$item['studentid']) }}"><i class="fa fa-pencil"></i></a>





                <button onclick="openModal()" class="open-btn"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="no-data">No students found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
   </div>


</div>

</section>

<script>
    function openModal() {
    document.getElementById("confirmModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("confirmModal").style.display = "none";
}
</script>
