<section class="home-section">
<div class="text">View Announcement</div>
<div class="teacher-container">
 <form action="#" method="POST" enctype="multipart/form-data">
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
           <div class="section-header">Personal Information</div>
                    <div class="section-content">
                        <!--  -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Event Title</label>
                                <input type="text" value="Jorell Andrei" disabled/>
                            </div>
                            <div class="form-group">
                                <label>People</label>
                                <input type="text" value="Students"  disabled/>
                            </div>


                        </div>
                        <!--  -->
                        <div class="form-row">
                        <div class="form-group wide">
                            <label>Date</label>
                            <input type="text" value="January 23, 2025"  disabled/>
                            </div>

                        </div>

                        <div class="form-row">
                        <div class="form-group wide">
                                <label>Event Description</label>
                                <textarea readonly disabled style="resize: none;"> Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                                </textarea>
                            </div>

                        </div>
                        <!--  -->
                    </div>

    </div>
 </form>
</div>

</section>


