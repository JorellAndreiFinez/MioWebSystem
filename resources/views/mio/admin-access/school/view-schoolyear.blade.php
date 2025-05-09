<section class="home-section">
    <div class="text">School Year</div>

    <div class="teacher-container">

        <!-- HEADER CONTROLS -->
        <div class="table-header">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
            </div>
            <div class="button-group">
                <button class="btn sort-btn">Newest ⬇</button>
                <button class="btn add-btn">
                    <a href="{{ route('mio.CreateSchoolYear') }}">+ New Year</a>
                </button>
            </div>
        </div>

        <!-- SCHOOL YEAR TABLE -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>SY ID</th>
                        <th>Students</th>
                        <th>Teachers</th>
                        <th>Sections</th>
                        <th>Courses</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schoolyears as $schoolyear)
                        <tr>
                            <td>{{ $schoolyear['schoolyearid'] }}</td>
                            <td>{{ $schoolyear['schoolyear_students'] ?? '0' }}</td>
                            <td>{{ $schoolyear['schoolyear_teachers'] ?? '0'}}</td>
                            <td>{{ $schoolyear['schoolyear_sections'] ?? '0'}}</td>
                            <td>{{ $schoolyear['schoolyear_courses'] ?? '0'}}</td>
                            <td>
                                @if (isset($schoolyear['status']) && $schoolyear['status'] === 'active')
                                    <p class="calendar-active-status">Active</p>
                                @else
                                    <p class="calendar-close-status">Closed</p>
                                @endif
                            </td>
                            <td class="action-icons">
                                <a href="{{ route('mio.EditSchoolYear', ['id' => $schoolyear['schoolyearid']]) }}">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="no-data">No school year found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</section>

