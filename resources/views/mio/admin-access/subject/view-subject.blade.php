
<!-- Modal overlay -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Delete Subject</span>
    </div>
    <div class="modal-body">
      <p>Are you sure you want to remove this subject?</p>
    </div>
    <div class="modal-footer">
      <button class="btn cancel-btn" onclick="closeModal()">Cancel</button>
      <button class="btn confirm-btn">Confirm</button>
    </div>
  </div>
</div>

<section class="home-section">
<div class="text">Grade 7 > All Schedule</div>
<div class="teacher-container">
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
        @foreach($subjects as $subjectId => $subject)
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
                <form method="POST" action="{{ route('mio.DeleteSubject', ['grade' => $grade, 'subjectId' => $subjectId]) }}" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach

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
