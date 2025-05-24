<section class="home-section">
    <div class="text">Enrollment</div>

    <div class="teacher-container">
        <!-- HEADER CONTROLS -->
        <div class="table-header">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
            </div>
            <div class="button-group">
                <button class="btn sort-btn">Newest ⬇</button>
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
                    @php
                        $form = $enrollee['enrollment_form'] ?? [];
                        $fullName = $form['first_name'] . ' ' . $form['last_name'];
                        $status = $enrollee['enroll_status'] ?? 'Pending';
                    @endphp
                    <tr>
                        <td>{{ $enrollee['ID'] ?? $id }}</td>
                        <td>{{ $fullName }}</td>
                        <td>{{ $status }}</td>

                        <td class="action-icons">
                           <a href="{{ route('mio.view-enrollee', ['id' => $id]) }}">
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
