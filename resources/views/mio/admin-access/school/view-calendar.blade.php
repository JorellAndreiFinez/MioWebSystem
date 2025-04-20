
<!-- Modal overlay -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Delete Calendar</span>
    </div>
    <div class="modal-body">
      <p>Are you sure you want to remove this calendar?</p>
    </div>
    <div class="modal-footer">
      <button class="btn cancel-btn" onclick="closeModal()">Cancel</button>
      <button class="btn confirm-btn">Confirm</button>
    </div>
  </div>
</div>

<section class="home-section">
<div class="text">Grade 7 > All Calendar</div>
<div class="teacher-container">
    <!-- HEADER CONTROLS -->
    <div class="table-header">
    <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
        </div>
        <div class="button-group">
            <button class="btn sort-btn">Newest ⬇</button>
            <button class="btn add-btn"><a href="{{ route('mio.add-calendar') }}">+ New Calendar</a></button>
        </div>
    </div>

    <!-- TEACHER TABLE -->
   <div class="table-container">
   <table>
        <thead>
            <tr>
                <th>SY ID</th>
                <th>Students</th>
                <th>Teachers</th>
                <th>Section</th>
                <th>Courses</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>C10001</td>
                <td>1,003</td>
                <td>12</td>
                <td>40</td>
                <td>120</td>
                <td><p class="calendar-active-status">
                    Active
                </p></td>

                <td class="action-icons">
                    <a href="{{ route('mio.edit-subject') }}"><i class="fa fa-pencil"></i></a>

                    <button onclick="openModal()" class="open-btn"><i class="fa fa-trash"></i></button>
                </td>
            </tr>

            <tr>
                <td>C10001</td>
                <td>1,003</td>
                <td>12</td>
                <td>40</td>
                <td>120</td>
                <td>
                    <p class="calendar-close-status">
                    Closed</p>
                </td>
                <td class="action-icons">
                <a href="{{ route('mio.edit-subject') }}"><i class="fa fa-pencil"></i></a>

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
