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

    <main class="main-content">
        <!-- SPEECH FORM -->
        <form method="POST" action="{{ route('mio.save-speech-assessment', $type) }}" enctype="multipart/form-data">
    @csrf

    <h3>Speech and Auditory Test</h3>
    <table class="table table-bordered" id="speech-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Text</th>
            <th>Level</th>
            <th>Image</th>
            <th>Action</th>
        </tr>
        </thead>
    <tbody>
        @if(count($speech) > 0)
            @foreach ($speech as $speechID => $phrase)
            <tr data-key="{{ $speechID }}">
            <td>{{ $speechID }}</td>
            <td class="phrase-text">{{ htmlspecialchars($phrase['text'] ?? '') }}</td>
            <td class="phrase-level">{{ $phrase['level'] ?? '' }}</td>

            <td>
            @if (!empty($phrase['image_url']))
                <a href="#" onclick="showImageModal('{{ $phrase['image_url'] }}'); return false;">
                    <i class="fas fa-image fa-2x text-primary"></i> <!-- Font Awesome icon -->
                </a>
            @endif
            </td>

            <td class="phrase-actions">
                <input type="hidden" name="speech[{{ $speechID }}][_delete]" class="delete-flag" value="0">
                <button type="button" class="btn btn-sm btn-primary" onclick="editSpeechRow(this)">Edit</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="markDelete(this)">Remove</button>
            </td>
            </tr>
            @endforeach
        @else
            <tr>
            <td colspan="4" class="text-center">No speech phrases found. Please add some.</td>
            </tr>
        @endif

        <!-- ADD NEW SPEECH PHRASE ROW (hidden by default) -->
        <tr id="new-speech-row" style="display:none;">
        <td>
            <input type="text" id="new-speech-id" name="new_speech[id]" readonly class="form-control-plaintext">
        </td>
        <td>
            <input type="text" name="new_speech[text]" class="form-control" placeholder="Enter new phrase">
        </td>
        <td>
            <select name="new_speech[level]" class="form-control">
            <option value="Easy">Easy</option>
            <option value="Medium">Medium</option>
            <option value="Hard">Hard</option>
            </select>
        </td>
        <td>
            <input type="file" name="new_speech[image]" class="form-control">
        </td>

        <td>
            <button type="button" class="btn btn-sm btn-success" onclick="saveSpeech()">Add</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="cancelAddSpeech()">Cancel</button>
        </td>
        </tr>
    </tbody>


    </table>

    <button type="button" class="btn btn-secondary mb-3" onclick="showAddSpeech()">+ Add Speech Phrase</button>

    <button type="submit" id="submit-all-btn" class="btn btn-primary" style="display:none;">Submit All Changes</button>
    </form>
    <br>
    <br>

    <!-- AUDITORY FORM -->
    <form method="POST" action="{{ route('mio.save-auditory-assessment', $type) }}">
    @csrf

        <table class="table table-bordered" id="auditory-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Text</th>
            <th>Level</th>
            <th>Action</th>
        </tr>
        </thead>
    <tbody>
        @if(count($auditory) > 0)
            @foreach ($auditory as $auditoryID => $phrase)
            <tr data-key="{{ $auditoryID }}">
            <td>{{ $auditoryID }}</td>
            <td class="phrase-text2">{{ htmlspecialchars($phrase['text'] ?? '') }}</td>
            <td class="phrase-level2">{{ $phrase['level'] ?? '' }}</td>
            <td class="phrase-actions2">
                <input type="hidden" name="auditory[{{ $auditoryID }}][_delete]" class="delete-flag2" value="0">
                <button type="button" class="btn btn-sm btn-primary" onclick="editAuditoryRow(this)">Edit</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="markDelete2(this)">Remove</button>
            </td>
            </tr>
            @endforeach
        @else
            <tr>
            <td colspan="4" class="text-center">No auditory phrases found. Please add some.</td>
            </tr>
        @endif

        <!-- ADD NEW SPEECH PHRASE ROW (hidden by default) -->
        <tr id="new-auditory-row" style="display:none;">
        <td>
            <input type="text" id="new-auditory-id" name="new_auditory[id]" readonly class="form-control-plaintext">
        </td>
        <td>
            <input type="text" name="new_auditory[text]" class="form-control" placeholder="Enter new phrase">
        </td>
        <td>
            <select name="new_auditory[level]" class="form-control">
            <option value="Easy">Easy</option>
            <option value="Medium">Medium</option>
            <option value="Hard">Hard</option>
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-success" onclick="saveAuditory()">Add</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="cancelAddAuditory()">Cancel</button>
        </td>
        </tr>
    </tbody>


    </table>

    <button type="button" class="btn btn-secondary mb-3" onclick="showAddAuditory()">+ Add Auditory Text</button>

    <button type="submit" id="submit-all-btn2" class="btn btn-primary" style="display:none;">Submit All Changes</button>
    </form>

      <hr class="my-5">

    <!-- SENTENCE FORM -->
    <form method="POST" action="{{ route('mio.save-sentence-assessment', $type) }}">
    @csrf

    <h3>Sentence Test</h3>
    <table class="table table-bordered" id="sentence-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Text</th>
            <th>Level</th>
            <th>Action</th>
        </tr>
        </thead>
    <tbody>
        @if(count($sentence) > 0)
            @foreach ($sentence as $sentenceID => $phrase)
            <tr data-key="{{ $sentenceID }}">
            <td>{{ $sentenceID }}</td>
            <td class="phrase-text3">{{ htmlspecialchars($phrase['text'] ?? '') }}</td>
            <td class="phrase-level3">{{ $phrase['level'] ?? '' }}</td>
            <td class="phrase-actions3">
                <input type="hidden" name="sentence[{{ $sentenceID }}][_delete]" class="delete-flag3" value="0">
                <button type="button" class="btn btn-sm btn-primary" onclick="editSentenceRow(this)">Edit</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="markDelete3(this)">Remove</button>
            </td>
            </tr>
            @endforeach
        @else
            <tr>
            <td colspan="4" class="text-center">No sentence test phrases found. Please add some.</td>
            </tr>
        @endif

        <!-- ADD NEW SENTENCE PHRASE ROW (hidden by default) -->
        <tr id="new-sentence-row" style="display:none;">
        <td>
            <input type="text" id="new-sentence-id" name="new_sentence[id]" readonly class="form-control-plaintext">
        </td>
        <td>
            <input type="text" name="new_sentence[text]" class="form-control" placeholder="Enter new sentence">
        </td>
        <td>
            <select name="new_sentence[level]" class="form-control">
            <option value="Easy">Easy</option>
            <option value="Medium">Medium</option>
            <option value="Hard">Hard</option>
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-success" onclick="saveSentence()">Add</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="cancelAddSentence()">Cancel</button>
        </td>
        </tr>
    </tbody>
    </table>

    <button type="button" class="btn btn-secondary mb-3" onclick="showAddSentence()">+ Add Sentence Phrase</button>

    <button type="submit" id="submit-all-btn3" class="btn btn-primary" style="display:none;">Submit All Changes</button>
    </form>

    <hr class="my-5">

    <!-- FILL IN THE BLANK -->
    <form method="POST" action="{{ route('mio.save-fnblank-assessment', $type) }}">
        @csrf

        <h3>Fill in the Blanks Test</h3>
        <table class="table table-bordered" id="fillblanks-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Text</th>
                <th>Correct</th>
                <th>A</th>
                <th>B</th>
                <th>C</th>
                <th>Level</th>
                <th>Action</th>
            </tr>
            </thead>
        <tbody>
            @if(count($fillblanks) > 0)
                @foreach ($fillblanks as $itemID => $item)
                <tr data-key="{{ $itemID }}">
                    <td>{{ $itemID }}</td>
                    <td class="text-blank">{{ $item['text'] ?? '' }}</td>
                    <td class="correct-blank">{{ $item['correct'] ?? '' }}</td>
                    <td class="choiceA">{{ $item['a'] ?? '' }}</td>
                    <td class="choiceB">{{ $item['b'] ?? '' }}</td>
                    <td class="choiceC">{{ $item['c'] ?? '' }}</td>
                    <td class="level-blank">{{ $item['level'] ?? '' }}</td>
                    <td class="actions-blank">
                        <input type="hidden" name="fillblanks[{{ $itemID }}][_delete]" class="delete-flag-blank" value="0">
                        <button type="button" class="btn btn-sm btn-primary" onclick="editBlankRow(this)">Edit</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="markDeleteBlank(this)">Remove</button>
                    </td>
                </tr>
                @endforeach
            @else
                <tr><td colspan="8" class="text-center">No fill-in-the-blank items found.</td></tr>
            @endif

            <!-- Hidden Row for New Entry -->
            <tr id="new-blank-row" style="display:none;">
                <td><input type="text" id="new-blank-id" name="new_blank[id]" readonly class="form-control-plaintext"></td>
                <td><input type="text" name="new_blank[text]" class="form-control" placeholder="e.g. The dog ___ the ball."></td>
                <td>
                    <select name="new_blank[correct]" id="new-blank-correct" class="form-control">
                        <option value="">Select correct answer</option>
                        <option value="A">A: <span id="choiceA-text"></span></option>
                        <option value="B">B: <span id="choiceB-text"></span></option>
                        <option value="C">C: <span id="choiceC-text"></span></option>
                    </select>
                    </td>

                <td><input type="text" name="new_blank[a]" class="form-control" placeholder="Choice A"></td>
                <td><input type="text" name="new_blank[b]" class="form-control" placeholder="Choice B"></td>
                <td><input type="text" name="new_blank[c]" class="form-control" placeholder="Choice C"></td>
                <td>
                    <select name="new_blank[level]" class="form-control">
                        <option value="Easy">Easy</option>
                        <option value="Medium">Medium</option>
                        <option value="Hard">Hard</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-success" onclick="saveBlank()">Add</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="cancelAddBlank()">Cancel</button>
                </td>
            </tr>
        </tbody>
        </table>

        <button type="button" class="btn btn-secondary mb-3" onclick="showAddBlank()">+ Add Fill-in-the-Blank</button>
        <button type="submit" id="submit-all-blank" class="btn btn-primary" style="display:none;">Submit All Changes</button>
        </form>


    </main>
  </div>
</section>

<!-- IMAGE MODAL -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imageModalLabel">Image Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="modalImage" src="" alt="Full Image" class="img-fluid" style="width: 500px; height: 500px; object-fit: cover; border-radius: 10px;">
      </div>
    </div>
  </div>
</div>


<script>
  const csrfToken = '{{ csrf_token() }}';
  const assessmentType = '{{ $type }}'; // pass PHP variable to JS

      function showImageModal(imageUrl) {
    const modalImg = document.getElementById('modalImage');
    modalImg.src = imageUrl;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
</script>

<!-- FILL IN THE BLANK -->

<script>
function showAddBlank() {
    document.getElementById('new-blank-row').style.display = '';
    document.getElementById('new-blank-id').value = generateID('FB');
    document.getElementById('submit-all-blank').style.display = 'inline-block';
}

function cancelAddBlank() {
    document.getElementById('new-blank-row').style.display = 'none';
    ['text', 'correct', 'a', 'b', 'c'].forEach(field =>
        document.querySelector(`input[name="new_blank[${field}]"]`).value = ''
    );
    document.querySelector('select[name="new_blank[level]"]').value = 'Easy';
}

function saveBlank() {
    const id = document.getElementById('new-blank-id').value;
    const text = document.querySelector('input[name="new_blank[text]"]').value.trim();
    const a = document.querySelector('input[name="new_blank[a]"]').value.trim();
    const b = document.querySelector('input[name="new_blank[b]"]').value.trim();
    const c = document.querySelector('input[name="new_blank[c]"]').value.trim();
    const correctLetter = document.querySelector('select[name="new_blank[correct]"]').value;
    let correct = '';
    if (correctLetter === 'A') correct = a;
    if (correctLetter === 'B') correct = b;
    if (correctLetter === 'C') correct = c;
    const level = document.querySelector('select[name="new_blank[level]"]').value;

    if (!text || !correct || !a || !b || !c || !correctLetter) {
        alert('Please complete all fields.');
        return;
    }


    const tableBody = document.querySelector('#fillblanks-table tbody');
    const noDataRow = tableBody.querySelector('tr td[colspan="8"]');
    if (noDataRow) noDataRow.parentElement.remove();

    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>${id}</td>
        <td><input type="text" name="fillblanks[${id}][text]" class="form-control" value="${text}"></td>
        <td>
        <select name="fillblanks[${id}][correct]" class="form-control">
            <option value="A"${correctLetter === 'A' ? ' selected' : ''}>A: ${a}</option>
            <option value="B"${correctLetter === 'B' ? ' selected' : ''}>B: ${b}</option>
            <option value="C"${correctLetter === 'C' ? ' selected' : ''}>C: ${c}</option>
        </select>
        </td>

        <td><input type="text" name="fillblanks[${id}][a]" class="form-control" value="${a}"></td>
        <td><input type="text" name="fillblanks[${id}][b]" class="form-control" value="${b}"></td>
        <td><input type="text" name="fillblanks[${id}][c]" class="form-control" value="${c}"></td>
        <td>
            <select name="fillblanks[${id}][level]" class="form-control">
                <option value="Easy"${level === 'Easy' ? ' selected' : ''}>Easy</option>
                <option value="Medium"${level === 'Medium' ? ' selected' : ''}>Medium</option>
                <option value="Hard"${level === 'Hard' ? ' selected' : ''}>Hard</option>
            </select>
        </td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">Remove</button></td>
    `;
    tableBody.insertBefore(newRow, document.getElementById('new-blank-row'));
    cancelAddBlank();
}

function markDeleteBlank(button) {
    const row = button.closest('tr');
    const input = row.querySelector('.delete-flag-blank');
    if (input) {
        input.value = "1";
        row.style.display = 'none';
        document.getElementById('submit-all-blank').style.display = 'inline-block';
    }
}

function editBlankRow(button) {
    const row = button.closest('tr');
    const key = row.getAttribute('data-key');

    const cells = {
        text: row.querySelector('.text-blank'),
        correct: row.querySelector('.correct-blank'),
        a: row.querySelector('.choiceA'),
        b: row.querySelector('.choiceB'),
        c: row.querySelector('.choiceC'),
        level: row.querySelector('.level-blank'),
        actions: row.querySelector('.actions-blank')
    };

    const current = {
        text: cells.text.textContent.trim(),
        correct: cells.correct.textContent.trim(),
        a: cells.a.textContent.trim(),
        b: cells.b.textContent.trim(),
        c: cells.c.textContent.trim(),
        level: cells.level.textContent.trim()
    };

    cells.text.innerHTML = `<input type="text" name="fillblanks[${key}][text]" class="form-control" value="${current.text}">`;
    cells.correct.innerHTML = `
    <select name="fillblanks[${key}][correct]" class="form-control">
        <option value="A"${current.correct === current.a ? ' selected' : ''}>A: ${current.a}</option>
        <option value="B"${current.correct === current.b ? ' selected' : ''}>B: ${current.b}</option>
        <option value="C"${current.correct === current.c ? ' selected' : ''}>C: ${current.c}</option>
    </select>
    `;

    cells.a.innerHTML = `<input type="text" name="fillblanks[${key}][a]" class="form-control" value="${current.a}">`;
    cells.b.innerHTML = `<input type="text" name="fillblanks[${key}][b]" class="form-control" value="${current.b}">`;
    cells.c.innerHTML = `<input type="text" name="fillblanks[${key}][c]" class="form-control" value="${current.c}">`;
    cells.level.innerHTML = `
        <select name="fillblanks[${key}][level]" class="form-control">
            <option value="Easy" ${current.level === 'Easy' ? 'selected' : ''}>Easy</option>
            <option value="Medium" ${current.level === 'Medium' ? 'selected' : ''}>Medium</option>
            <option value="Hard" ${current.level === 'Hard' ? 'selected' : ''}>Hard</option>
        </select>
    `;
    cells.actions.innerHTML = `<button type="submit" class="btn btn-sm btn-success">Save</button>`;
}

document.querySelector('input[name="new_blank[a]"]').addEventListener('input', e => {
    document.querySelector('#new-blank-correct option[value="A"]').textContent = 'A: ' + e.target.value;
});
document.querySelector('input[name="new_blank[b]"]').addEventListener('input', e => {
    document.querySelector('#new-blank-correct option[value="B"]').textContent = 'B: ' + e.target.value;
});
document.querySelector('input[name="new_blank[c]"]').addEventListener('input', e => {
    document.querySelector('#new-blank-correct option[value="C"]').textContent = 'C: ' + e.target.value;
});

</script>


<!-- SENTENCE ACTIVITY -->
<script>
  // Reuse padNumber and generateID helpers
  function padNumber(num, size) {
    let s = "000" + num;
    return s.substr(s.length - size);
  }

  function generateID(prefix) {
    const now = new Date();
    const year = now.getFullYear();
    const month = padNumber(now.getMonth() + 1, 2);
    const day = padNumber(now.getDate(), 2);
    const randomDigits = padNumber(Math.floor(Math.random() * 1000), 3);
    return prefix + year + month + day + randomDigits;
  }

  // Show submit button for Sentence form
  function showSubmitButton3() {
    document.getElementById('submit-all-btn3').style.display = 'inline-block';
  }

  // Show add sentence row and generate new ID
  function showAddSentence() {
    document.getElementById('new-sentence-row').style.display = '';
    document.getElementById('new-sentence-id').value = generateID('SN');
    showSubmitButton3();
  }

  // Mark a sentence row for deletion (hide it, mark hidden input)
  function markDelete3(button) {
    const row = button.closest('tr');
    const deleteInput = row.querySelector('.delete-flag3');
    if (deleteInput) {
      deleteInput.value = "1"; // mark for deletion
      row.style.display = 'none'; // hide row
    }
    showSubmitButton3();
  }

  // Remove sentence row from table completely (used on new rows before save)
  function removeSentence(btn) {
    btn.closest('tr').remove();
  }

  // Save new sentence (add to table)
  function saveSentence() {
    const id = document.getElementById('new-sentence-id').value;
    const text = document.querySelector('input[name="new_sentence[text]"]').value.trim();
    const level = document.querySelector('select[name="new_sentence[level]"]').value;

    if (!text) {
      alert('Please enter a sentence.');
      return;
    }

    const tableBody = document.querySelector('#sentence-table tbody');
    const noDataRow = tableBody.querySelector('tr td[colspan="4"]');
    if (noDataRow) noDataRow.parentElement.remove();

    const newRow = document.createElement('tr');
    newRow.innerHTML = `
      <td>${id}</td>
      <td><input type="text" name="sentence[${id}][text]" class="form-control" value="${text}"></td>
      <td>
        <select name="sentence[${id}][level]" class="form-control">
          <option value="Easy"${level === 'Easy' ? ' selected' : ''}>Easy</option>
          <option value="Medium"${level === 'Medium' ? ' selected' : ''}>Medium</option>
          <option value="Hard"${level === 'Hard' ? ' selected' : ''}>Hard</option>
        </select>
      </td>
      <td><button type="button" class="btn btn-sm btn-danger" onclick="removeSentence(this)">Remove</button></td>
    `;

    tableBody.insertBefore(newRow, document.getElementById('new-sentence-row'));
    cancelAddSentence();
  }

  // Cancel adding new sentence (hide add row and clear inputs)
  function cancelAddSentence() {
    document.getElementById('new-sentence-row').style.display = 'none';
    document.querySelector('input[name="new_sentence[text]"]').value = '';
    document.querySelector('select[name="new_sentence[level]"]').value = 'Easy';
  }

  // Edit existing sentence row — convert text and level cells to input/select
  function editSentenceRow(button) {
    const row = button.closest('tr');
    const key = row.getAttribute('data-key');

    const textCell = row.querySelector('.phrase-text3');
    const levelCell = row.querySelector('.phrase-level3');
    const actionsCell = row.querySelector('.phrase-actions3');

    const currentText = textCell.textContent.trim();
    const currentLevel = levelCell.textContent.trim();

    textCell.innerHTML = `<input type="text" name="sentence[${key}][text]" class="form-control" value="${currentText}">`;
    levelCell.innerHTML = `
      <select name="sentence[${key}][level]" class="form-control">
        <option value="Easy" ${currentLevel === 'Easy' ? 'selected' : ''}>Easy</option>
        <option value="Medium" ${currentLevel === 'Medium' ? 'selected' : ''}>Medium</option>
        <option value="Hard" ${currentLevel === 'Hard' ? 'selected' : ''}>Hard</option>
      </select>
    `;

    actionsCell.innerHTML = `
      <button type="submit" class="btn btn-sm btn-success" onclick="saveSentenceRow(this)">Save</button>
    `;
  }

  // Save edited sentence row (revert input/select back to text and update display)
  function saveSentenceRow(button) {
    const row = button.closest('tr');
    const key = row.getAttribute('data-key');

    const textInput = row.querySelector(`input[name="sentence[${key}][text]"]`);
    const levelSelect = row.querySelector(`select[name="sentence[${key}][level]"]`);
    const actionsCell = row.querySelector('.phrase-actions3');
    const textCell = row.querySelector('.phrase-text3');
    const levelCell = row.querySelector('.phrase-level3');

    if (!textInput.value.trim()) {
      alert('Please enter a sentence.');
      return;
    }

    // You might want to submit your form here or handle AJAX save

    // Update cells with new values as plain text
    textCell.textContent = textInput.value.trim();
    levelCell.textContent = levelSelect.value;

    // Restore the action buttons (Edit only)
    actionsCell.innerHTML = `
      <button type="button" class="btn btn-sm btn-primary" onclick="editSentenceRow(this)">Edit</button>
    `;
  }
</script>


<!-- SPEECH ACTIVITY  -->
<script>
  function padNumber(num, size) {
    let s = "000" + num;
    return s.substr(s.length - size);
  }



  function generateID(prefix) {
    const now = new Date();
    const year = now.getFullYear();
    const month = padNumber(now.getMonth() + 1, 2);
    const day = padNumber(now.getDate(), 2);
    const randomDigits = padNumber(Math.floor(Math.random() * 1000), 3);
    return prefix + year + month + day + randomDigits;
  }

// ---------- SPEECH FUNCTIONS ----------

  function showSubmitButton() {
  document.getElementById('submit-all-btn').style.display = 'inline-block';
}

function showAddSpeech() {
  document.getElementById('new-speech-row').style.display = '';
  document.getElementById('new-speech-id').value = generateID('SP');
  showSubmitButton();  // Show submit button here
}

function markDelete(button) {
  const row = button.closest('tr');
  const deleteInput = row.querySelector('.delete-flag');
  if (deleteInput) {
    deleteInput.value = "1";  // mark for deletion
    row.style.display = 'none'; // visually hide row from user
  }
  showSubmitButton();  // Show submit button here
}


  // Example remove functions (adjust with your form or AJAX logic)
  function removeSpeech(btn) {
    btn.closest('tr').remove();
  }


  // Example save functions (you'll need to implement real save logic)
  function saveSpeech() {
    const id = document.getElementById('new-speech-id').value;
    const text = document.querySelector('input[name="new_speech[text]"]').value.trim();
    const level = document.querySelector('select[name="new_speech[level]"]').value;
    const imageInput = document.querySelector('input[name="new_speech[image]"]');

    if (!text) {
        alert('Please enter a phrase.');
        return;
    }

    const tableBody = document.querySelector('#speech-table tbody');
    const noDataRow = tableBody.querySelector('tr td[colspan="4"]');
    if (noDataRow) noDataRow.parentElement.remove();

    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>${id}</td>
        <td><input type="text" name="speech[${id}][text]" class="form-control" value="${text}"></td>
        <td>
        <select name="speech[${id}][level]" class="form-control">
            <option value="Easy"${level === 'Easy' ? ' selected' : ''}>Easy</option>
            <option value="Medium"${level === 'Medium' ? ' selected' : ''}>Medium</option>
            <option value="Hard"${level === 'Hard' ? ' selected' : ''}>Hard</option>
        </select>
        </td>
        <td>
        <input type="file" name="speech[${id}][image]" class="form-control">
        </td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeSpeech(this)">Remove</button></td>
    `;

    // Transfer file from temporary input to the new row input
    const newImageInput = newRow.querySelector(`input[name="speech[${id}][image]"]`);
    if (imageInput && imageInput.files.length > 0) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(imageInput.files[0]);
        newImageInput.files = dataTransfer.files;
        // Clear the original file input to avoid duplication or confusion
        imageInput.value = '';
    }

    tableBody.insertBefore(newRow, document.getElementById('new-speech-row'));
    cancelAddSpeech();
}


    function editSpeechRow(button) {
        const row = button.closest('tr');
        const key = row.getAttribute('data-key');

        // Get current phrase text and level
        const textCell = row.querySelector('.phrase-text');
        const levelCell = row.querySelector('.phrase-level');
        const actionsCell = row.querySelector('.phrase-actions');
         const imageCell = row.querySelector('td:nth-child(4)');

        const currentText = textCell.textContent.trim();
        const currentLevel = levelCell.textContent.trim();

        // Replace text with input
        textCell.innerHTML = `<input type="text" name="speech[${key}][text]" class="form-control" value="${currentText}">`;

        // Replace level with select dropdown
        levelCell.innerHTML = `
            <select name="speech[${key}][level]" class="form-control">
            <option value="Easy" ${currentLevel === 'Easy' ? 'selected' : ''}>Easy</option>
            <option value="Medium" ${currentLevel === 'Medium' ? 'selected' : ''}>Medium</option>
            <option value="Hard" ${currentLevel === 'Hard' ? 'selected' : ''}>Hard</option>
            </select>`;

            // Replace image cell content with file input and existing image if any
            let existingImg = '';
            const imgTag = imageCell.querySelector('img');
            if (imgTag) {
                existingImg = `<img src="${imgTag.src}" width="50" alt="Image" style="display:block; margin-bottom:5px;">`;
            }
            imageCell.innerHTML = `
                ${existingImg}
                <input type="file" name="speech[${key}][image]" class="form-control">
            `;

        // Replace action buttons with Save and Delete
        actionsCell.innerHTML = `
            <button type="submit" class="btn btn-sm btn-success" onclick="saveSpeechRow(this)">Save</button>
        `;
    }

    // Save changes for the row, toggle inputs back to text and level
    function saveSpeechRow(button) {
    const row = button.closest('tr');
    const key = row.getAttribute('data-key');

    const textInput = row.querySelector(`input[name="speech[${key}][text]"]`);
    const levelSelect = row.querySelector(`select[name="speech[${key}][level]"]`);
    const actionsCell = row.querySelector('.phrase-actions');
    const textCell = row.querySelector('.phrase-text');
    const levelCell = row.querySelector('.phrase-level');

    if (!textInput.value.trim()) {
        alert('Please enter a phrase.');
        return;
    }

    // Update cells with new values as plain text
    textCell.textContent = textInput.value.trim();
    levelCell.textContent = levelSelect.value;

    // Keep existing image and file input as is (file input remains to allow upload)
    // Or you can reset imageCell content if you want (optional)

    // Restore action buttons
    actionsCell.innerHTML = `
        <input type="hidden" name="speech[${key}][_delete]" class="delete-flag" value="0">
        <button type="button" class="btn btn-sm btn-primary" onclick="editSpeechRow(this)">Edit</button>
        <button type="button" class="btn btn-sm btn-danger" onclick="markDelete(this)">Remove</button>
    `;

    showSubmitButton();
    }

    // Cancel editing, revert to original display
    function cancelEdit(button) {
    const row = button.closest('tr');
    const key = row.getAttribute('data-key');

    // Revert text cell
    const textCell = row.querySelector('.phrase-text');
    const textInput = textCell.querySelector('input');
    const originalText = textInput ? textInput.defaultValue || textInput.value : '';

    // Revert level cell
    const levelCell = row.querySelector('.phrase-level');
    const levelSelect = levelCell.querySelector('select');
    const originalLevel = levelSelect ? levelSelect.querySelector('option[selected]')?.value || levelSelect.value : '';

    // Revert image cell
    const imageCell = row.querySelector('td:nth-child(4)');
    // Check if there is an image preview
    const fileInput = imageCell.querySelector('input[type="file"]');
    let imgUrl = '';
    if (fileInput && fileInput.previousElementSibling && fileInput.previousElementSibling.tagName === 'IMG') {
        imgUrl = fileInput.previousElementSibling.src;
    } else {
        // fallback: try to find img src from old data attribute or something if you store it
    }

    textCell.textContent = originalText;
    levelCell.textContent = originalLevel;
    imageCell.innerHTML = imgUrl ? `<img src="${imgUrl}" width="50" alt="Image"> <input type="file" name="speech[${key}][image]" class="form-control" style="margin-top:5px;">`
                                    : `<input type="file" name="speech[${key}][image]" class="form-control">`;

    // Revert action buttons
    const actionsCell = row.querySelector('.phrase-actions');
    actionsCell.innerHTML = `
        <input type="hidden" name="speech[${key}][_delete]" class="delete-flag" value="0">
        <button type="button" class="btn btn-sm btn-primary" onclick="editSpeechRow(this)">Edit</button>
        <button type="button" class="btn btn-sm btn-danger" onclick="markDelete(this)">Remove</button>
    `;
    }
</script>


<!-- AUDITORY ACTIVITY  -->
<script>
  function padNumber(num, size) {
    let s = "000" + num;
    return s.substr(s.length - size);
  }



  function generateID(prefix) {
    const now = new Date();
    const year = now.getFullYear();
    const month = padNumber(now.getMonth() + 1, 2);
    const day = padNumber(now.getDate(), 2);
    const randomDigits = padNumber(Math.floor(Math.random() * 1000), 3);
    return prefix + year + month + day + randomDigits;
  }

// ---------- AUDITORY FUNCTIONS ----------

function showSubmitButton2() {
  document.getElementById('submit-all-btn2').style.display = 'inline-block';
}

function showAddAuditory() {
  document.getElementById('new-auditory-row').style.display = '';
  document.getElementById('new-auditory-id').value = generateID('AU');
  showSubmitButton2();
}

function markDelete2(button) {
  const row = button.closest('tr');
  const deleteInput = row.querySelector('.delete-flag2');
  if (deleteInput) {
    deleteInput.value = "1"; // mark for deletion
    row.style.display = 'none';
  }
  showSubmitButton2();
}

function removeAuditory(btn) {
  btn.closest('tr').remove();
}

function cancelAddAuditory() {
  document.getElementById('new-auditory-row').style.display = 'none';
  // Clear inputs
  document.querySelector('input[name="new_auditory[text]"]').value = '';
  document.querySelector('select[name="new_auditory[level]"]').selectedIndex = 0;
}

function saveAuditory() {
  const id = document.getElementById('new-auditory-id').value;
  const text = document.querySelector('input[name="new_auditory[text]"]').value.trim();
  const level = document.querySelector('select[name="new_auditory[level]"]').value;

  if (!text) {
    alert('Please enter a phrase.');
    return;
  }

  const tableBody = document.querySelector('#auditory-table tbody');
  const noDataRow = tableBody.querySelector('tr td[colspan="4"]');
  if (noDataRow) noDataRow.parentElement.remove();

  const newRow = document.createElement('tr');
  newRow.innerHTML = `
    <td>${id}</td>
    <td><input type="text" name="auditory[${id}][text]" class="form-control" value="${text}"></td>
    <td>
      <select name="auditory[${id}][level]" class="form-control">
        <option value="Easy"${level === 'Easy' ? ' selected' : ''}>Easy</option>
        <option value="Medium"${level === 'Medium' ? ' selected' : ''}>Medium</option>
        <option value="Hard"${level === 'Hard' ? ' selected' : ''}>Hard</option>
      </select>
    </td>
    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeAuditory(this)">Remove</button></td>
  `;

  tableBody.insertBefore(newRow, document.getElementById('new-auditory-row'));

  cancelAddAuditory();
  showSubmitButton2();
}

function editAuditoryRow(button) {
  const row = button.closest('tr');
  const key = row.getAttribute('data-key');

  // Get current phrase text and level
  const textCell = row.querySelector('.phrase-text2');
  const levelCell = row.querySelector('.phrase-level2');
  const actionsCell = row.querySelector('.phrase-actions2');

  const currentText = textCell.textContent.trim();
  const currentLevel = levelCell.textContent.trim();

  // Replace text with input
  textCell.innerHTML = `<input type="text" name="auditory[${key}][text]" class="form-control" value="${currentText}">`;

  // Replace level with select dropdown
  levelCell.innerHTML = `
    <select name="auditory[${key}][level]" class="form-control">
      <option value="Easy" ${currentLevel === 'Easy' ? 'selected' : ''}>Easy</option>
      <option value="Medium" ${currentLevel === 'Medium' ? 'selected' : ''}>Medium</option>
      <option value="Hard" ${currentLevel === 'Hard' ? 'selected' : ''}>Hard</option>
    </select>`;

  // Replace action buttons with Save button
  actionsCell.innerHTML = `
    <button type="submit" class="btn btn-sm btn-success" onclick="saveAuditoryRow(this)">Save</button>
  `;
}

function saveAuditoryRow(button) {
  const row = button.closest('tr');
  const key = row.getAttribute('data-key');

  const textInput = row.querySelector(`input[name="auditory[${key}][text]"]`);
  const levelSelect = row.querySelector(`select[name="auditory[${key}][level]"]`);
  const actionsCell = row.querySelector('.phrase-actions2');
  const textCell = row.querySelector('.phrase-text2');
  const levelCell = row.querySelector('.phrase-level2');

  if (!textInput.value.trim()) {
    alert('Please enter a phrase.');
    return;
  }

  // Optional: form.submit(); or AJAX submit here if you want immediate saving

  // Update cells with new values as plain text
  textCell.textContent = textInput.value.trim();
  levelCell.textContent = levelSelect.value;

  // Restore the action buttons (Edit only)
  actionsCell.innerHTML = `
    <button type="button" class="btn btn-sm btn-primary" onclick="editAuditoryRow(this)">Edit</button>
    <button type="button" class="btn btn-sm btn-danger" onclick="markDelete2(this)">Remove</button>
  `;

  showSubmitButton2();
}

function cancelAddSpeech() {
    // Hide new row
    document.getElementById('new-speech-row').style.display = 'none';

    // Clear input fields
    document.getElementById('new-speech-id').value = '';
    document.querySelector('input[name="new_speech[text]"]').value = '';
    document.querySelector('select[name="new_speech[level]"]').value = 'Easy';

    // Check if any changes exist before hiding submit button
    maybeHideSubmitButton();
}

function cancelAddAuditory() {
    // Hide new row
    document.getElementById('new-auditory-row').style.display = 'none';

    // Clear input fields
    document.getElementById('new-auditory-id').value = '';
    document.querySelector('input[name="new_auditory[text]"]').value = '';
    document.querySelector('select[name="new_auditory[level]"]').value = 'Easy';

    // Check if any changes exist before hiding submit button
    maybeHideSubmitButton();
}

function maybeHideSubmitButton() {
    const hasSpeechChanges = document.querySelectorAll('input[name^="speech["], select[name^="speech["], input.delete-flag[value="1"]').length > 0;
    const hasAuditoryChanges = document.querySelectorAll('input[name^="auditory["], select[name^="auditory["], input.delete-flag2[value="1"]').length > 0;
    const hasNewSpeech = document.getElementById('new-speech-row').style.display !== 'none';
    const hasNewAuditory = document.getElementById('new-auditory-row').style.display !== 'none';

    // Hide buttons only if nothing is being added or changed
    if (!hasSpeechChanges && !hasAuditoryChanges && !hasNewSpeech && !hasNewAuditory) {
        document.getElementById('submit-all-btn').style.display = 'none';
        document.getElementById('submit-all-btn2').style.display = 'none';
    }
}

</script>

<!-- Bootstrap Bundle includes Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
