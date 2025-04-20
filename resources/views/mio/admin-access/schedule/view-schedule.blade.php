
<!-- Modal overlay -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Delete Schedule</span>
    </div>
    <div class="modal-body">
      <p>Are you sure you want to remove this schedule?</p>
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
            <button class="btn add-btn"><a href="{{ route('mio.add-schedule') }}">+ New Schedule</a></button>
        </div>
    </div>

    <!-- TEACHER TABLE -->
   <div class="table-container">
   <table>
        <thead>
            <tr>
                <th>Section ID</th>
                <th>Total Students</th>
                <th>Total Courses</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    SEC701
                </td>
                <td>45</td>
                <td>3</td>
                <td class="action-icons">
                <a href="{{ route('mio.edit-schedule') }}"><i class="fa fa-pencil"></i></a>

                <button onclick="openModal()" class="open-btn"><i class="fa fa-trash"></i></button>
                </td>
            </tr>

            <tr>
                <td>
                    SEC701
                </td>
                <td>45</td>
                <td>3</td>
                <td class="action-icons">
                <a href="{{ route('mio.edit-schedule') }}"><i class="fa fa-pencil"></i></a>

                <button onclick="openModal()" class="open-btn"><i class="fa fa-trash"></i></button>
                </td>
            </tr>

            <tr>
                <td>
                    SEC701
                </td>
                <td>45</td>
                <td>3</td>
                <td class="action-icons">
                <a href="{{ route('mio.edit-schedule') }}"><i class="fa fa-pencil"></i></a>

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
