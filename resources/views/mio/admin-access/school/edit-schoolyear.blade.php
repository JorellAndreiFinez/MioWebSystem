<section class="home-section">
    <div class="text">Edit School Year</div>
    <div class="teacher-container">
        <form action="{{ route('mio.UpdateSchoolYear', ['id' => $editdata['schoolyearid']]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <!-- HEADER CONTROLS -->
            <div class="table-header">
                <div class="search-container" style="background: transparent;"></div>
                <div class="button-group">
                    <button type="button" class="btn cancel-btn" onclick="window.history.back()">Cancel</button>
                    <button type="submit" class="btn add-btn">
                        <span class="icon">✔</span> Save Changes
                    </button>
                </div>
            </div>

            <div class="form-container">
                <!-- School Year Section -->
                <div class="section-header">School Year Information</div>
                <div class="section-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label>School Year ID <span style="color: red; font-weight:700">*</span></label>
                            <input type="text" name="schoolyearid" value="{{ $editdata['schoolyearid'] }}" required id="schoolyearID" readonly />
                        </div>
                        <div class="form-group">
                            <label>Status <span style="color: red; font-weight:700">*</span></label>
                            <select name="status" required>
                                <option value="active" {{ $editdata['status'] === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $editdata['status'] === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section (Optional) -->
                <div class="section-header">Additional Information</div>
                <div class="section-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Start of School Year <span style="color: red; font-weight:700">*</span></label>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <select name="start_month" required>
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                        <option value="{{ $month }}" {{ $editdata['start_month'] === $month ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                                <span>{{ $editdata['start_year'] ?? date('Y') }}</span>
                            </div>
                            @error('start_month')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>End of School Year <span style="color: red; font-weight:700">*</span></label>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <select name="end_month" required>
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                        <option value="{{ $month }}" {{ $editdata['end_month'] === $month ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                                <span>{{ $editdata['end_year'] ?? date('Y', strtotime('+1 year')) }}</span>
                            </div>
                            @error('end_month')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
