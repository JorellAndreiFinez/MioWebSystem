<section class="home-section">
    <div class="text">Profile</div>
    <div class="grid-container">

        <!-- Begin Main-->
        <main class="main">

            <div class="profile-container">

                <!-- Main Content -->
                <div class="main-content">

                <div class="profile-card">

                    <div class="profile-picture">
                        <img src="{{ 'https://ui-avatars.com/api/?name='.$name }}" alt="Profile Picture" />

                        <button class="edit-button">✎</button>
                    </div>
                    <button class="new-message-btn">
                    ✎ <span class="btn-text">Edit Profile</span>
                    </button>

                    <div class="profile-info">

                       @php
                            $teacher_name = ($teacher['fname'] ?? '') . ' ' . ($teacher['lname'] ?? '');
                        @endphp

                        <h2>{{ $teacher_name ?: 'No Name' }}</h2>
                        <div class="section">
                            <h3>Biography</h3>
                            <p>{{ $teacher['bio'] ?? 'No biography available.' }}</p>
                        </div>
                        <div class="section">
                            <h3>Contact</h3>
                            <p><p>{{ $teacher['email'] ?? 'No email' }}</p></p>
                        </div>
                        <div class="section">
                            <h3>Social Links</h3>
                            <p>Facebook: @leokenter</p>
                        </div>
                    </div>
                </div>

                </div>

            </div>

            <div class="form-container">
                <!-- Student Category Section -->
                <div class="section-header">Teacher Category</div>
                <div class="section-content">{{$teacher['category']}}</div>

                <!-- Personal Information Section -->
                <div class="section-header">Personal Information</div>
                <div class="section-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" value="{{$teacher['fname']}} {{$teacher['lname']}}" readonly />
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <input type="text" value="Female" readonly />
                        </div>
                        <div class="form-group">
                            <label>Age</label>
                            <input type="text" value="17" readonly />
                        </div>
                        <div class="form-group">
                            <label>Birthday</label>
                            <input type="text" placeholder="MM/DD/YYYY" readonly />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group wide">
                            <label>Address</label>
                            <input type="text" value="13 Blk Lot 8, Camella Homes, Valenzuela City" readonly />
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" value="09053622382" readonly />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group wide">
                            <label>Emergency Contact Number</label>
                            <input type="text" value="09053622382" readonly />
                        </div>
                    </div>
                </div>

                <!-- Academic Information Section -->
                <div class="section-header">Academic Information

                </div>
                <div class="section-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Student ID</label>
                            <input type="text" value="Tatiana Donin" readonly />
                        </div>
                        <div class="form-group">
                            <label>Grade Level</label>
                            <input type="text" value="Female" readonly />
                        </div>
                        <div class="form-group">
                            <label>Section</label>
                            <input type="text" value="17" readonly />
                        </div>

                    </div>

                </div>

                <!-- Health Information Section -->
                <div class="section-header">Academic Information

                </div>
                <div class="section-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Medical History</label>
                            <input type="text" value="Tatiana Donin" readonly />
                        </div>
                        <div class="form-group">
                            <label>Type of Disability</label>
                            <input type="text" value="Female" readonly />
                        </div>
                        <div class="form-group">
                            <label>Type of Hearing Loss (If Applicable)</label>
                            <input type="text" value="17" readonly />
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Hearing Aid / Cochlear Implant</label>
                            <input type="text" value="Tatiana Donin" readonly />
                        </div>

                        <div class="form-group">
                            <label>Speech Therapy History</label>
                            <input type="text" value="17" readonly />
                        </div>

                    </div>

                </div>

            </div>
        </main>
        <!-- End Main -->
    </div>


</section>



