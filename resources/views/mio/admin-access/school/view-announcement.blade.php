<section class="home-section">
<div class="text">View Announcement</div>
<div class="teacher-container">
<!-- HEADER CONTROLS -->
<div class="table-header">
            <div class="search-container" style="background: transparent;">
            </div>

            <div class="button-group">
            <button type="button" class="btn cancel-btn"><a href="{{ url()->previous() }}">Exit Announcement</a>
            </button>
            </div>

        </div>
    <div class="form-container">
           <!-- Personal Information Section -->
         <div class="section-header">Announcement Information</div>
         <div class="section-content">
            <div class="form-row">
                <div class="form-group">
                    <label>Event Title</label>
                    <input type="text" value="{{ $announcement['title'] }}" disabled/>
                </div>
                <div class="form-group">
                    <label>People</label>
                    <input type="text" value="{{ ucfirst($announcement['people']) }}" disabled/>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group wide">
                    <label>Date</label>
                    <input type="text" value="{{ \Carbon\Carbon::parse($announcement['date'])->format('F d, Y') }}" disabled/>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group wide">
                    <label>Event Description</label>
                    <textarea readonly disabled style="resize: none;">{{ $announcement['description'] }}</textarea>
                </div>
            </div>
        </div>

    </div>
</div>

</section>


