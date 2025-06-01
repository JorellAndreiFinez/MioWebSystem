
<!-- Modal overlay -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Delete Question</span>
    </div>
    <div class="modal-body">
      <p id="confirmMessage">Are you sure you want to remove this question?</p>
    </div>
    <div class="modal-footer">
      <button class="btn cancel-btn" onclick="closeModal()">Cancel</button>

     <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn confirm-btn" style="background-color: #e74c3c; color: #fff; border: none; padding: 8px 16px; border-radius: 4px;">Delete</button>
      </form>

    </div>
  </div>
</div>

<section class="home-section">
    <div class="text">
        <div class="breadcrumb-item">
            <a href="{{ route('mio.enrollment') }}">
                Enrollment
            </a>
        </div>
        <div class="breadcrumb-item active">
            Edit Assessment - Physical
        </div>
    </div>

    <div class="teacher-container">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <main class="main-content">
            @php
                $levels = ['kinder' => 'Kinder', 'elementary' => 'Elementary', 'highschool' => 'High School', 'seniorhigh' => 'Senior High School'];
            @endphp
            <!-- Add Question Button -->
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn primary-btn" data-toggle="modal" data-target="#addQuestionModal">
                    + Add Question
                </button>
            </div>

            @foreach($levels as $levelKey => $levelLabel)
            <h4 class="mt-4">{{ $levelLabel }}</h4>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Question</th>
                        <th>Level</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                   @if(!empty($mcqs[$levelKey]) && count($mcqs[$levelKey]) > 0)
                        @foreach($mcqs[$levelKey] as $itemID => $item)
                            <tr data-key="{{ $itemID }}">
                                <td>{{ $itemID }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $item['type'] ?? '')) }}</td>
                                <td>
                                    <strong>{{ $item['question'] ?? '' }}</strong>
                                    @if(isset($item['type']) && strpos($item['type'], 'multiple') !== false)
                                        <ul style="padding-left: 15px; margin-top:5px;">
                                            @foreach($item['options'] ?? [] as $key => $option)
                                                <li @if($item['correct'] == $key || (is_array($item['correct']) && in_array($key, $item['correct']))) style="font-weight: bold; color: green;" @endif>
                                                    {{ $key }}. {{ $option }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @elseif($item['type'] == 'fill_blank')
                                        <div style="margin-top:5px;"><em>Answer:</em> {{ $item['correct'] ?? '' }}</div>
                                    @endif
                                </td>
                                <td>{{ ucfirst($item['level'] ?? '') }}</td>
                                <td>
                                    <button
                                        class="btn btn-primary btn-sm"
                                        data-question='@json($item)'
                                        data-update-url="{{ route('mio.update-question', ['type' => $type, 'id' => $itemID]) }}"
                                        onclick="handleEditQuestion(this)">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="5" class="text-center">No questions for {{ $levelLabel }}.</td></tr>
                    @endif

                </tbody>
            </table>
            <hr class="my-5">

        @endforeach

        </main>
    </div>
</section>

<!-- Edit Question Modal -->
<div class="modal fade" id="editQuestionModal" tabindex="-1" role="dialog" aria-labelledby="editQuestionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form method="POST" action="" enctype="multipart/form-data" id="editQuestionForm">
      @csrf
      @method('PUT')  <!-- Use PUT for update -->
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Question</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#editQuestionModal').modal('hide')">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">

          <div class="form-group">
            <label for="editQuestionTypeSelect">Question Type</label>
            <select name="edit_mcq[type]" id="editQuestionTypeSelect" class="form-control" required>
                <option value="" disabled selected hidden>Select a type</option>
                <option value="multiple_single">Multiple Choice (Single Answer)</option>
                <option value="multiple_multiple">Multiple Choice (Multiple Answers)</option>
                <option value="fill_blank">Fill in the Blank</option>
            </select>
          </div>

          <div class="form-group">
            <label for="editQuestionText">Question</label>
            <textarea name="edit_mcq[question]" id="editQuestionText" class="form-control" rows="4" required style="resize: none;"></textarea>
          </div>

          <div class="form-group">
            <label>Optional Image for Question</label>
            <input type="file" name="edit_mcq[image]" accept="image/*" class="form-control-file">
            <div id="currentImagePreview" class="mt-2"></div>
          </div>

          <!-- Multiple Choice (Single or Multiple) -->
          <div id="editMultipleFields" class="question-type">
            <label>Options</label>
            <div id="editOptionContainer">
                <!-- Options will be dynamically injected -->
            </div>
            <button type="button" class="btn btn-sm btn-success mt-2" id="editAddOptionBtn">+ Add Option</button>

            <div class="mt-3" id="editCorrectAnswerSelection">
                <label class="form-label">Correct Answer</label>
                <div id="editCorrectAnswerOptions" class="form-group d-flex flex-wrap gap-3"></div>
            </div>
          </div>

          <!-- Fill in the Blank -->
          <div id="editFillBlankFields" class="question-type d-none">
            <label for="editFillBlankCorrect">Correct Answer</label>
            <input type="text" name="edit_mcq[correct]" id="editFillBlankCorrect" class="form-control" placeholder="Correct word or phrase" required>
          </div>

          <div class="form-group mt-3">
            <label>Level</label>
            <select name="edit_mcq[level]" id="editLevelSelect" class="form-control" required>
                @foreach($levels as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn primary-btn">Update Question</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#editQuestionModal').modal('hide')">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<script>
    function handleEditQuestion(button) {
        const question = JSON.parse(button.getAttribute('data-question'));
        const updateUrl = button.getAttribute('data-update-url');

        // Elements inside edit modal
        const modal = $('#editQuestionModal');
        const form = document.getElementById('editQuestionForm');
        const questionTypeSelect = document.getElementById('editQuestionTypeSelect');
        const questionText = document.getElementById('editQuestionText');
        const currentImagePreview = document.getElementById('currentImagePreview');
        const optionContainer = document.getElementById('editOptionContainer');
        const correctAnswerOptions = document.getElementById('editCorrectAnswerOptions');
        const multipleFields = document.getElementById('editMultipleFields');
        const fillBlankFields = document.getElementById('editFillBlankFields');
        const fillBlankInput = document.getElementById('editFillBlankCorrect');
        const levelSelect = document.getElementById('editLevelSelect');

        // Clear previous options and correct answers
        optionContainer.innerHTML = '';
        correctAnswerOptions.innerHTML = '';
        currentImagePreview.innerHTML = '';

        // Set form action
        form.action = updateUrl;

        // Set question type
        questionTypeSelect.value = question.type || '';

        // Set question text
        questionText.value = question.question || '';

        // Set level
        if (question.level) {
            levelSelect.value = question.level;
        } else {
            levelSelect.selectedIndex = 0;
        }

        // Show/hide fields based on type
        function showFieldsForType(type) {
            if (type === 'fill_blank') {
                multipleFields.classList.add('d-none');
                fillBlankFields.classList.remove('d-none');
                fillBlankInput.required = true;
                fillBlankInput.disabled = false;

                // Disable multiple choice correct inputs
                correctAnswerOptions.innerHTML = '';
                optionContainer.innerHTML = '';

            } else if (type === 'multiple_single' || type === 'multiple_multiple') {
                multipleFields.classList.remove('d-none');
                fillBlankFields.classList.add('d-none');
                fillBlankInput.required = false;
                fillBlankInput.disabled = true;

                // Enable add option button
                document.getElementById('editAddOptionBtn').disabled = false;
            }
        }

        showFieldsForType(questionTypeSelect.value);

        // Load options if multiple choice
        if (question.type === 'multiple_single' || question.type === 'multiple_multiple') {
            const optionsObj = question.options || {};
            const options = Object.values(optionsObj);

            const correct = question.correct;

            options.forEach((opt, index) => {
                const optionKey = String.fromCharCode(65 + index);

                // Create option input wrapper
                const optionDiv = document.createElement('div');
                optionDiv.classList.add('form-row', 'mb-2', 'option-item');



                optionDiv.innerHTML = `
                    <div class="col">
                        <input type="text" name="edit_mcq[options][]" class="form-control option-input" placeholder="Option ${optionKey}" required value="${opt}">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-danger btn-sm remove-option">×</button>
                    </div>
                `;
                optionContainer.appendChild(optionDiv);
            });

            // Generate correct answer inputs (radio or checkbox)
            correctAnswerOptions.innerHTML = '';

            const isMultipleCorrect = question.type === 'multiple_multiple';
            const correctValues = Array.isArray(correct) ? correct : [correct];

            options.forEach((opt, index) => {
                const optionKey = String.fromCharCode(65 + index);

                if (!opt.trim()) return;

                const wrapper = document.createElement('div');
                wrapper.classList.add('form-check', 'mr-3');

                const inputEl = document.createElement('input');
                inputEl.type = isMultipleCorrect ? 'checkbox' : 'radio';
                inputEl.name = isMultipleCorrect ? 'edit_mcq[correct][]' : 'edit_mcq[correct]';
                inputEl.value = optionKey;
                inputEl.id = `edit_correct_${optionKey}`;
                inputEl.classList.add('form-check-input');

                if (correctValues.includes(optionKey)) {
                    inputEl.checked = true;
                }

                const labelEl = document.createElement('label');
                labelEl.htmlFor = inputEl.id;
                labelEl.textContent = `${optionKey}. ${opt}`;

                wrapper.appendChild(inputEl);
                wrapper.appendChild(labelEl);
                correctAnswerOptions.appendChild(wrapper);
            });

            // Attach remove option button listener (delegated)
            optionContainer.querySelectorAll('.remove-option').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.target.closest('.option-item').remove();
                    // Trigger update of correct answers UI if needed here
                });
            });

        } else if (question.type === 'fill_blank') {
            fillBlankInput.value = question.correct || '';
        }

        // Show image preview if exists
        if (question.image_url) {
            currentImagePreview.innerHTML = `
                <img src="${question.image_url}" alt="Current Question Image" style="max-width: 100%; max-height: 150px; border: 1px solid #ddd; padding: 3px; border-radius: 3px;" />
                <br>
                <small class="text-muted">Current image</small>
            `;
        }

        // Finally, show the modal
        modal.modal('show');
    }

    // Also add listener for the Add Option button inside the edit modal:
   document.getElementById('editAddOptionBtn').addEventListener('click', () => {
    const optionContainer = document.getElementById('editOptionContainer');
    const correctAnswerOptions = document.getElementById('editCorrectAnswerOptions');
    const optionCount = optionContainer.querySelectorAll('.option-item').length;

    if (optionCount >= 6) return; // max 6 options

    const newOptionKey = String.fromCharCode(65 + optionCount);
    const questionType = document.getElementById('editQuestionTypeSelect').value;
    const isMultipleCorrect = questionType === 'multiple_multiple';

    // Create new option input
    const newOption = document.createElement('div');
    newOption.classList.add('form-row', 'mb-2', 'option-item');
    const inputId = `edit_correct_${newOptionKey}`;

    newOption.innerHTML = `
        <div class="col">
            <input type="text" name="edit_mcq[options][]" class="form-control option-input" placeholder="Option ${newOptionKey}" required oninput="document.getElementById('${inputId}_label').textContent = '${newOptionKey}. ' + this.value;">
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-danger btn-sm remove-option">×</button>
        </div>
    `;
    optionContainer.appendChild(newOption);

    // Create matching correct answer input
    const correctWrapper = document.createElement('div');
    correctWrapper.classList.add('form-check', 'mr-3');

    const inputEl = document.createElement('input');
    inputEl.type = isMultipleCorrect ? 'checkbox' : 'radio';
    inputEl.name = isMultipleCorrect ? 'edit_mcq[correct][]' : 'edit_mcq[correct]';
    inputEl.value = newOptionKey;
    inputEl.id = inputId;
    inputEl.classList.add('form-check-input');

    const labelEl = document.createElement('label');
    labelEl.htmlFor = inputEl.id;
    labelEl.id = `${inputId}_label`;
    labelEl.textContent = `${newOptionKey}. `;

    correctWrapper.appendChild(inputEl);
    correctWrapper.appendChild(labelEl);
    correctAnswerOptions.appendChild(correctWrapper);

    // Attach remove event
    newOption.querySelector('.remove-option').addEventListener('click', e => {
        const optionItem = e.target.closest('.option-item');
        const index = [...optionContainer.children].indexOf(optionItem);

        // Remove the option and the corresponding correct answer input
        optionItem.remove();
        correctAnswerOptions.removeChild(correctAnswerOptions.children[index]);

        // Re-index keys (optional: to update A, B, C... letters)
        updateOptionKeys(optionContainer, correctAnswerOptions, questionType);
        });
    });

    // Helper function to update letters (optional, but improves UX if options are removed)
    function updateOptionKeys(optionContainer, correctAnswerOptions, questionType) {
        const isMultipleCorrect = questionType === 'multiple_multiple';
        const options = optionContainer.querySelectorAll('.option-item');

        options.forEach((item, index) => {
            const key = String.fromCharCode(65 + index);
            const input = item.querySelector('.option-input');
            input.placeholder = `Option ${key}`;

            const id = `edit_correct_${key}`;
            const labelId = `${id}_label`;
            input.setAttribute('oninput', `document.getElementById('${labelId}').textContent = '${key}. ' + this.value;`);

            // Also update the corresponding correct answer input
            const correctInput = correctAnswerOptions.children[index].querySelector('input');
            const correctLabel = correctAnswerOptions.children[index].querySelector('label');

            correctInput.value = key;
            correctInput.id = id;
            correctInput.name = isMultipleCorrect ? 'edit_mcq[correct][]' : 'edit_mcq[correct]';
            correctLabel.setAttribute('for', id);
            correctLabel.id = labelId;
            correctLabel.textContent = `${key}. ${input.value}`;
        });
    }

</script>


<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" role="dialog" aria-labelledby="addQuestionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form method="POST" action="{{ route('mio.save-question', $type) }}" enctype="multipart/form-data" id="addQuestionForm">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Question</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">

          <div class="form-group">
            <label for="questionTypeSelect">Question Type</label>
            <select name="new_mcq[type]" id="questionTypeSelect" class="form-control" required>
                <option value="" disabled selected hidden>Select a type</option>
                <option value="multiple_single">Multiple Choice (Single Answer)</option>
                <option value="multiple_multiple">Multiple Choice (Multiple Answers)</option>
                <option value="fill_blank">Fill in the Blank</option>
                {{-- Removed Connect the Answer (match_pair) option --}}
            </select>
          </div>

          <div class="form-group">
            <label for="questionText">Question</label>
            <textarea name="new_mcq[question]" id="questionText" class="form-control" rows="4" required style="resize: none;"></textarea>
          </div>

          <div class="form-group">
            <label>Optional Image for Question</label>
            <input type="file" name="new_mcq[image]" accept="image/*" class="form-control-file">
            <small class="form-text text-muted">Upload an optional image to accompany the question.</small>
          </div>

          <!-- Multiple Choice (Single or Multiple) -->
          <div id="multipleFields" class="question-type">
            <label>Options</label>
            <div id="optionContainer">
                <!-- Initial options -->
                <div class="form-row mb-2 option-item">
                    <div class="col">
                        <input type="text" name="new_mcq[options][]" class="form-control option-input" placeholder="Option A" required>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-danger btn-sm remove-option">×</button>
                    </div>
                </div>
                <div class="form-row mb-2 option-item">
                    <div class="col">
                        <input type="text" name="new_mcq[options][]" class="form-control option-input" placeholder="Option B" required>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-danger btn-sm remove-option">×</button>
                    </div>
                </div>
                <div class="form-row mb-2 option-item">
                    <div class="col">
                        <input type="text" name="new_mcq[options][]" class="form-control option-input" placeholder="Option C" required>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-danger btn-sm remove-option">×</button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-success mt-2" id="addOptionBtn">+ Add Option</button>

            <div class="mt-3" id="correctAnswerSelection">
                <label class="form-label">Correct Answer</label>
                <div id="correctAnswerOptions" class="form-group d-flex flex-wrap gap-3"></div>
            </div>
          </div>

          <!-- Fill in the Blank -->
          <div id="fillBlankFields" class="question-type d-none">
            <label for="fillBlankCorrect">Correct Answer</label>
            <input type="text" name="new_mcq[correct]" id="fillBlankCorrect" class="form-control" placeholder="Correct word or phrase" required>
          </div>

          {{-- Removed Connect the Answer fields --}}

          <div class="form-group mt-3">
            <label>Level</label>
            <select name="new_mcq[level]" class="form-control" required>
                @foreach($levels as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn primary-btn">Save Question</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ADD MODAL -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const questionTypeSelect = document.getElementById('questionTypeSelect');
        const multipleFields = document.getElementById('multipleFields');
        const fillBlankFields = document.getElementById('fillBlankFields');
        const correctAnswerOptions = document.getElementById('correctAnswerOptions');
        const optionContainer = document.getElementById('optionContainer');
        const addOptionBtn = document.getElementById('addOptionBtn');

        function updateCorrectAnswerInputs() {
            correctAnswerOptions.innerHTML = '';
            const options = optionContainer.querySelectorAll('.option-input');
            const type = questionTypeSelect.value;

            options.forEach((input, index) => {
                const optionKey = String.fromCharCode(65 + index); // A, B, C, ...
                if (!input.value.trim()) return;

                const wrapper = document.createElement('div');
                wrapper.classList.add('form-check', 'mr-3');

                const inputEl = document.createElement('input');
                inputEl.type = type === 'multiple_multiple' ? 'checkbox' : 'radio';
                inputEl.name = 'new_mcq[correct]' + (type === 'multiple_multiple' ? '[]' : '');
                inputEl.value = optionKey;
                inputEl.id = `correct_${optionKey}`;
                inputEl.classList.add('form-check-input');
                if (index === 0 && type !== 'multiple_multiple') inputEl.checked = true;

                const labelEl = document.createElement('label');
                labelEl.htmlFor = `correct_${optionKey}`;
                labelEl.textContent = `${optionKey}. ${input.value}`;

                wrapper.appendChild(inputEl);
                wrapper.appendChild(labelEl);
                correctAnswerOptions.appendChild(wrapper);
            });
        }

    questionTypeSelect.addEventListener('change', () => {
        const selected = questionTypeSelect.value;
        if (selected === 'fill_blank') {
            multipleFields.classList.add('d-none');
            fillBlankFields.classList.remove('d-none');

            // Enable required for fillBlankCorrect and enable input
            const fillBlankInput = document.getElementById('fillBlankCorrect');
            fillBlankInput.required = true;
            fillBlankInput.disabled = false;

            // Disable multiple choice correct inputs (radio/checkbox)
            document.querySelectorAll('#correctAnswerOptions input').forEach(input => {
                input.required = false;
                input.disabled = true;
            });

            // Disable and remove required from options inputs
            document.querySelectorAll('#optionContainer input.option-input').forEach(input => {
                input.required = false;
                input.disabled = true;
            });

            // Also disable the "Add Option" button so no new options can be added
            addOptionBtn.disabled = true;

        } else {
            multipleFields.classList.remove('d-none');
            fillBlankFields.classList.add('d-none');

            // Disable fillBlankCorrect required & disable input
            const fillBlankInput = document.getElementById('fillBlankCorrect');
            fillBlankInput.required = false;
            fillBlankInput.disabled = true;

            // Enable multiple choice correct inputs
            document.querySelectorAll('#correctAnswerOptions input').forEach(input => {
                input.required = true;
                input.disabled = false;
            });

            // Enable options inputs and add required
            document.querySelectorAll('#optionContainer input.option-input').forEach(input => {
                input.required = true;
                input.disabled = false;
            });

            // Enable the Add Option button
            addOptionBtn.disabled = false;
        }
    });

        addOptionBtn.addEventListener('click', () => {
            const optionCount = optionContainer.querySelectorAll('.option-item').length;
            if (optionCount >= 6) return; // Max 6 options
            const newOption = document.createElement('div');
            newOption.classList.add('form-row', 'mb-2', 'option-item');
            newOption.innerHTML = `
            <div class="col">
                <input type="text" name="new_mcq[options][]" class="form-control option-input" placeholder="Option ${String.fromCharCode(65 + optionCount)}" required>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-danger btn-sm remove-option">×</button>
            </div>
            `;
            optionContainer.appendChild(newOption);
            updateCorrectAnswerInputs();
        });

        optionContainer.addEventListener('input', (e) => {
            if (e.target.classList.contains('option-input')) {
                updateCorrectAnswerInputs();
            }
        });

        optionContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-option')) {
                const optionItem = e.target.closest('.option-item');
                optionItem.remove();
                updateCorrectAnswerInputs();
            }
        });

        // Initialize form state
        questionTypeSelect.dispatchEvent(new Event('change'));
    });
</script>



<script>
function openModal(deleteUrl, itemName = 'this item', itemType = 'item') {
    const modal = document.getElementById("confirmModal");
    modal.style.display = "flex";

    // Set delete URL
    document.getElementById("deleteForm").action = deleteUrl;

    // Set message and title
    document.getElementById("confirmMessage").textContent = `Are you sure you want to delete "${itemName}" from your ${itemType}?`;
    document.getElementById("modalTitle").textContent = `Delete ${capitalizeFirstLetter(itemType)}`;
}

function closeModal() {
    document.getElementById("confirmModal").style.display = "none";
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
</script>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <!-- Bootstrap 4 JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


