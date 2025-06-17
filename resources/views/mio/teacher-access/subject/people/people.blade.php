<section class="home-section">
    <!-- BREADCRUMBS -->
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.subject-teacher.show-subject', ['subjectId' => $subject['subject_id']]) }}">
                {{ $subject['title'] }}
            </a>
        </div>

        <div class="breadcrumb-item active" style="font-size: 1.3rem;">People</div>
    </div>

    <div class="teacher-container">

        <!-- HEADER CONTROLS -->
        <div class="table-header">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
            </div>
        </div>

        <!-- TEACHER TABLE -->
        <div class="table-container">
            <table id="peopleTable">
                <thead>
                    <tr>
                        <th>Person ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($people as $personId => $person)
                        <tr>
                            <td>{{ $personId }}</td> <!-- Display the student ID -->
                            <td>{{ ucwords(strtolower($person['first_name'])) }}</td> <!-- Capitalize first name -->
                            <td>{{ ucwords(strtolower($person['last_name'])) }}</td> <!-- Capitalize last name -->
                            <td>{{ ucfirst($person['role']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="no-data">No people found under this subject.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
    function searchCards() {
        let input = document.getElementById('searchBar');
        let filter = input.value.toLowerCase(); // Convert to lowercase for case-insensitive search
        let table = document.getElementById("peopleTable");
        let rows = table.getElementsByTagName("tr");

        // Loop through all table rows, except for the header
        for (let i = 1; i < rows.length; i++) {
            let row = rows[i];
            let cells = row.getElementsByTagName("td");
            let match = false;

            // Loop through all cells in the row to check if any matches the search query
            for (let j = 0; j < cells.length; j++) {
                let cell = cells[j];
                if (cell) {
                    if (cell.textContent.toLowerCase().includes(filter)) {
                        match = true;
                        break; // Stop searching if one of the cells matches
                    }
                }
            }

            // Show the row if there is a match, hide it otherwise
            if (match) {
                row.style.display = "";
            } else {
                row.style.display = "none";

            }
        }
    }
</script>
