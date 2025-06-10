<section class="home-section">
  <div class="text">Add New Department</div>

  <div class="teacher-container">
    @include('mio.dashboard.status-message')

    <form action="{{ route('mio.UpdateDepartment', ['id' => $editdata['departmentid']]) }}" method="POST">
      @csrf
      @method('PUT')

      <!-- HEADER CONTROLS -->
      <div class="table-header">
        <div class="search-container" style="background: transparent;"></div>
        <div class="button-group">
          <button type="button" class="btn cancel-btn"><a href="{{ route("mio.ViewDepartment") }}">Cancel</a></button>
          <button type="submit" class="btn add-btn">
            <span class="icon">+</span> Add Department
          </button>
        </div>
      </div>

      <div class="form-container">
        <!-- Department Information -->
        <div class="section-header">Department Information</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-group">
              <label>Department ID <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="departmentid" id="departmentID" value="{{ $editdata['departmentid'] }}" required />
            </div>
            <div class="form-group">
              <label>Department Name <span style="color: red; font-weight:700">*</span></label>
              <input type="text" name="department_name" value="{{ $editdata['department_name'] }}"  required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
                <label>Department Code <span style="color: red; font-weight:700">*</span></label>
                <input type="text" name="department_code" value="{{ $editdata['department_code'] }}"  required />
            </div>

            <div class="form-group">
                <label>Department Type <span style="color: red; font-weight:700">*</span></label>
                <select name="department_type" required>
                    <option value="" disabled selected>Select Type</option>
                    <option value="academic" {{ $editdata['department_type'] == 'academic' ? 'selected' : '' }} >Academics</option>
                    <option value="specialized" {{ $editdata['department_type'] == 'specialized' ? 'selected' : '' }}>Specialized</option>
                    <option value="admin_support" {{ $editdata['department_type'] == 'admin_support' ? 'selected' : '' }}>Administrative and Support</option>
                </select>
            </div>

            <div class="form-group">
              <label>Status <span style="color: red; font-weight:700">*</span></label>
              <select name="status" required>
                <option value="" disabled>Select Status</option>
                <option value="active" {{ $editdata['status'] == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $editdata['status'] == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            </div>

          </div>

          <div class="form-row">
            <div class="form-group" style="flex: 1;">
                <label>Description</label>
                <textarea
                name="description"
                rows="3"
                placeholder="Describe the department's purpose or scope..."
                style="resize: none; height: 100px;"
                >{{ $editdata['description'] }}
            </textarea>
            </div>
            </div>


            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label>Head Teacher <span style="color: red; font-weight:700">*</span></label>
                    <select name="teacherid">
                        <option value="" disabled selected>Select a Teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher['teacherid'] }}"  {{ (isset($editdata['teacherid']) && $editdata['teacherid'] === $teacher['teacherid']) ? 'selected' : '' }}>
                                {{ $teacher['name'] }}
                                ({{ $teacher['departmentname'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>


        </div>
      </div>
    </form>
  </div>
</section>
