<section class="home-section">
    <div class="text">Enrollment</div>

    <div class="teacher-container">
        <!-- HEADER CONTROLS -->
         <div class="table-header" style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">

        <!-- Search -->
        <div class="search-container" style="display: flex; align-items: center;">
            <i class="fas fa-search" style="margin-right: 0.5rem;"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()" class="form-control" style="width: 200px;">
        </div>

        <!-- Sort Button -->
        <div class="button-group">
            <button class="btn sort-btn">Newest ⬇</button>
        </div>

        <!-- Edit Assessment -->
        <div class="edit-assessment-section" style="display: flex; align-items: center; gap: 1rem;">
            <label for="assessmentType" style="margin-bottom: 0; font-weight: bold;">Edit Assessment:</label>
            <select id="assessmentType" class="form-control" style="width: 200px;">
                <option value="physical">Physical Evaluation</option>
                <option value="written">Written Evaluation</option>
            </select>
            <button onclick="goToEditAssessment()" class="btn btn-success">Edit</button>
        </div>
    </div>


        <!-- ENROLLEE TABLE -->
        <div class="table-container">
            @include('mio.dashboard.status-message')

            <table>
                <thead>
                    <tr>
                        <th>Enrollment ID</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($enrollees as $id => $enrollee)

                    <tr>
                        <td>{{ $enrollee['ID'] ?? $id }}</td>
                        <td>{{ isset($enrollee['enrollment_form']['first_name']) ? $enrollee['enrollment_form']['first_name'] : 'N/A' }}</td>
                        <td>{{ $enrollee['enroll_status'] }}</td>

                        <td class="action-icons">
                           <a href="{{ route('mio.view-enrollee', ['id' => $enrollee['ID']]) }}">
                                <i class="fa fa-eye"></i>
                            </a>


                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="no-data">No enrollees found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>

</section>

<script>
    function goToEditAssessment() {
        const type = document.getElementById('assessmentType').value;

        // Laravel-style route redirect without href
        const url = `{{ url('mio/admin/enrollment/assessment') }}/${type}/edit`;
        window.location.href = url;
    }
</script>


