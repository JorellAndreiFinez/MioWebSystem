<section class="home-section">
<div class="text">Edit Announcement</div>
<div class="teacher-container">
<form action="{{ route('mio.UpdateAnnouncement', ['id' => $editdata['firebase_key']]) }}" method="POST">
    @method('PUT') <!-- Specifies a PUT request -->
    @csrf

    <!-- HEADER CONTROLS -->
    <div class="table-header">
            <div class="search-container" style="background: transparent;">
            </div>

            <div class="button-group">
            <button type="button" class="btn cancel-btn"><a href="{{ url()->previous() }}">Cancel</a>
            </button>
            <button class="btn add-btn">
                <span class="icon">✔</span> Save Announcement
            </button>
            </div>

        </div>
        <div class="form-container">
           <!-- Personal Information Section -->
           <div class="section-header">Announcement Information</div>
                    <div class="section-content">
                        <!--  -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Event Title</label>
                                <input type="text" placeholder="Title..." name="announce_title" value="{{ $editdata['title'] }}" required/>
                            </div>
                            <div class="form-group">
                                <label for="people">People</label>
                                <select id="people" name="announce_people" required>
                                    <option value="all" {{ $editdata['people'] == 'all' ? 'selected' : '' }}>All</option>
                                    <option value="students" {{ $editdata['people'] == 'students' ? 'selected' : '' }}>Students</option>
                                    <option value="teachers" {{ $editdata['people'] == 'teachers' ? 'selected' : '' }}>Teachers</option>
                                    <option value="parents" {{ $editdata['people'] == 'parents' ? 'selected' : '' }}>Parents</option>
                                    <option value="admin" {{ $editdata['people'] == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </div>

                        </div>
                        <!--  -->
                        <div class="form-row">
                        <div class="form-group wide">
                            <label>Date</label>
                            <input type="date" name="announce_date" required min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" value="{{ \Carbon\Carbon::parse($editdata['date'])->format('Y-m-d') }}"/>

                            </div>

                        </div>

                        <div class="form-row">
                        <div class="form-group wide">
                                <label>Event Description</label>
                                <textarea style="resize: none; " placeholder="Description..." name="announce_description" required>{{ $editdata['description'] }}</textarea>
                            </div>

                        </div>
                        <!--  -->
                    </div>

        </div>
</form>

</div>

</section>


