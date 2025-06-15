<!-- Modal overlay -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Delete Parent</span>
    </div>
    <div class="modal-body">
      <p id="confirmMessage">Are you sure you want to remove this parent?</p>
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
<div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.accounts') }}">
                Other Users
            </a>
        </div>
        <div class="breadcrumb-item active">Parents</div>

    </div>

<div class="teacher-container">
    <!-- HEADER CONTROLS -->
    <div class="table-header">
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
        </div>
        <div class="button-group">
            <button class="btn sort-btn" onclick="sortTableByNewest()">Newest ⬇</button>

            <a href="{{ route('mio.AddParent') }}" class="btn add-btn">+ New Parent</a>
        </div>
    </div>

    <!-- STUDENT TABLE -->
    <div class="table-container">
        @include('mio.dashboard.status-message')

        <table>
            <thead>
                <tr>
                    <th>Parent ID</th>
                    <th>Name</th>
                    <th>Created at</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($parents as $key => $item)
                    @if (isset($item['role']) && $item['role'] === 'parent')
                        <tr data-date="{{ $item['date_created'] ?? ''}}">
                            <td>{{ $item['parentid'] }}</td>
                            <td>{{ $item['fname'] }} {{ $item['lname'] }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($item['date_created'])->format('M d, Y h:i A') }}
                            </td>
                            <td class="action-icons">
                            <a href="{{ url('mio/admin/EditParent/'.$item['parentid']) }}">
                                <i class="fa fa-pencil"></i>
                            </a>

                            <div class="tooltip-container" style="position: relative; display: inline-block;">
                                <button type="button" class="btn delete-btn" style="cursor: not-allowed;" disabled>
                                    <i class="fa fa-trash" aria-hidden="true" style="color: gray;"></i>
                                </button>
                                <span class="tooltip-text" style="
                                    position: absolute;
                                    top: -35px;
                                    left: 50%;
                                    transform: translateX(-50%);
                                    background-color: #333;
                                    color: #fff;
                                    padding: 6px 10px;
                                    border-radius: 5px;
                                    font-size: 13px;
                                    white-space: nowrap;
                                    display: none;
                                    z-index: 10;
                                ">
                                    Cannot delete parent. Linked to student. Delete student first.
                                </span>
                            </div>
                        </td>

                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="4" class="no-data">No parents found.</td>
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

// tooltip for trash delete
document.addEventListener("DOMContentLoaded", () => {
  const deleteBtn = document.querySelector('.delete-btn');
  const tooltip = document.querySelector('.tooltip-text');

  if (deleteBtn && tooltip) {
    deleteBtn.addEventListener('mouseover', () => {
      tooltip.style.display = 'block';
    });

    deleteBtn.addEventListener('mouseout', () => {
      tooltip.style.display = 'none';
    });
  }
});

</script>

<style>
.tooltip-container:hover .tooltip-text {
    display: block;
}
</style>

<script>
function searchCards() {
    const searchInput = document.getElementById("searchBar").value.toLowerCase();
    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        const teacherId = row.children[0].textContent.toLowerCase();
        const name = row.children[1].textContent.toLowerCase();

        if (teacherId.includes(searchInput) || name.includes(searchInput)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>

<script>
let sortNewestFirst = true;

function sortTableByNewest() {
    const tbody = document.querySelector("table tbody");
    const rows = Array.from(tbody.querySelectorAll("tr")).filter(row => row.querySelector("td")); // Skip no-data row

    rows.sort((a, b) => {
        const dateA = new Date(a.getAttribute("data-date"));
        const dateB = new Date(b.getAttribute("data-date"));

        return sortNewestFirst ? dateB - dateA : dateA - dateB;
    });

    rows.forEach(row => tbody.appendChild(row));

    // Toggle sort direction for next click
    sortNewestFirst = !sortNewestFirst;

    // Update button text
    document.querySelector(".sort-btn").innerText = sortNewestFirst ? "Newest ⬇" : "Oldest ⬆";
}
</script>
