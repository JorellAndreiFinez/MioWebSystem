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
      <button class="btn confirm-btn">Confirm</button>
    </div>
  </div>
</div>

<section class="home-section">
    <div class="text">School</div>
    <section class="grade-section">
    <div class="grade-grid">

        <a href="{{ route('mio.view-calendar') }}">
        <div class="grade-card">
            <span class="icon"></span>
            <p>School Calendar</p>
            <span class="arrow">&rsaquo;</span>
        </div>
        </a>
       <a href="{{ route('mio.view-department') }}">
       <div class="grade-card">
            <span class="icon"></span>
            <p>Departments</p>
            <span class="arrow">&rsaquo;</span>
        </div>
       </a>

    </div>
</section>

<section class="announcements-section">
    <div class="header">
        <h2>Announcements</h2>
        <a href="{{ route('mio.add-announcement') }}">
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
            <tr>
                <td>Jan 30, 2025</td>
                <td>
                    <a href="{{ route('mio.view-announcement') }}">
                    Walang Pasok
                    </a>
                </td>
                <td>12</td>
                <td class="action-icons">
                    <a href="{{ route('mio.edit-announcement') }}"><i class="fa fa-pencil"></i></a>

                    <button onclick="openModal()" class="open-btn"><i class="fa fa-trash"></i></button>
                </td>
            </tr>

            <tr>
                <td>Jan 30, 2025</td>
                <td>Walang Pasok</td>
                <td>12</td>
                <td class="action-icons">
                    <a href="{{ route('mio.edit-announcement') }}"><i class="fa fa-pencil"></i></a>

                    <button onclick="openModal()" class="open-btn"><i class="fa fa-trash"></i></button>
                </td>
            </tr>

        </tbody>
    </table>
   </div>

    <!-- PAGINATION -->
    <div class="pagination">
        <a href="#">1</a>
        <a href="#">2</a>
        <a href="#">3</a>
        <a href="#">4</a>
        <a href="#">...</a>
        <a href="#">12</a>
    </div>

</section>


</section>

<script>
    function openModal() {
    document.getElementById("confirmModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("confirmModal").style.display = "none";
}
</script>
