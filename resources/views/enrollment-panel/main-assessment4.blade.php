<section class="home-section">
    <div class="text">Written Evaluation</div>

    <div class="evaluation-section" style="padding: 2rem; max-width: 800px; margin: auto;">
        <h3>Grammar & Sentence Test</h3>
        <p class="mb-4">
            This test measures language comprehension, vocabulary, and sentence formation skills.
            You will complete sentences by choosing the most appropriate word or phrase.
        </p>
        @if (session('status'))
            <div class="alert alert-success" role="alert" style="margin-bottom: 1.5rem;">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert" style="margin-bottom: 1.5rem;">
                {{ session('error') }}
            </div>
        @endif

       @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif




        <form id="sentence-structure-form" action="{{ route('assessment.written.submit') }}" method="POST">
            @csrf

            <input type="hidden" name="start_time" id="start_time" value="{{ now()->toDateTimeString() }}">


            @foreach($questions as $qnum => $qdata)
                <div class="card mb-4 p-3">
                    {{-- Optional Image --}}
                    {{-- Multiple Choice --}}
                    @if($qdata['type'] === 'multiple_single')
                        <label><strong>Question {{ $qnum }} (Single Answer):</strong> {{ $qdata['question'] }}</label>
                        <p class="text-muted mb-2">Choose only one correct answer.</p>

                        @if (!empty($qdata['image_url']))
                            <img src="{{ $qdata['image_url'] }}" alt="Question Image"
                                style="width: 200px; height: 200px; object-fit: cover; margin-bottom: 1rem;">
                        @endif

                        <div class="choices mb-2" style="display: flex; gap: 10px; flex-wrap: wrap;">
                            @foreach($qdata['options'] as $key => $choice)
                                <div class="choice-item"
                                    data-question="{{ $qnum }}"
                                    data-choice="{{ $key }}"
                                    data-type="{{ $qdata['type'] }}"
                                    style="padding: 10px 15px; border: 1px solid #ccc; border-radius: 5px; cursor: pointer; user-select: none;">
                                    {{ $choice }}
                                </div>
                            @endforeach
                        </div>

                        <input type="hidden" name="selected_choices[{{ $qnum }}]" id="selected-choice-{{ $qnum }}">

                    @elseif($qdata['type'] === 'multiple_multiple')
                        <label><strong>Question {{ $qnum }} (Multiple Answers):</strong> {{ $qdata['question'] }}</label>
                        <p class="text-muted mb-2">Choose all that apply.</p>

                        @if (!empty($qdata['image_url']))
                            <img src="{{ $qdata['image_url'] }}" alt="Question Image"
                                style="width: 200px; height: 200px; object-fit: cover; margin-bottom: 1rem;">
                        @endif

                        <div class="choices mb-2" style="display: flex; gap: 10px; flex-wrap: wrap;">
                            @foreach($qdata['options'] as $key => $choice)
                                <div class="choice-item"
                                    data-question="{{ $qnum }}"
                                    data-choice="{{ $key }}"
                                    data-type="{{ $qdata['type'] }}"
                                    style="padding: 10px 15px; border: 1px solid #ccc; border-radius: 5px; cursor: pointer; user-select: none;">
                                     {{ $choice }}
                                </div>
                            @endforeach
                        </div>

                        <input type="hidden" name="selected_choices[{{ $qnum }}]" id="selected-choice-{{ $qnum }}">


                    {{-- Fill in the Blank --}}
                    @elseif($qdata['type'] === 'fill_in_blank')
                        <label><strong>Question {{ $qnum }}:</strong> {{ $qdata['sentence'] }}</label>
                        @if (!empty($qdata['image_url']))
                            <img src="{{ $qdata['image_url'] }}" alt="Question Image" style="max-width: 100%; height: auto; margin-bottom: 1rem;">

                        @endif
                        <input type="text" name="fill_in_blanks[{{ $qnum }}]" placeholder="{{ $qdata['answer_placeholder'] ?? 'Enter answer' }}"
                            style="width: 100%; padding: 8px; margin-top: 10px;">

                   {{-- For syntax type (sentence ordering) --}}
                    @elseif($qdata['type'] === 'sentence_order')
                        <label><strong>Question {{ $qnum }}:</strong> {{ $qdata['sentence_hint'] }}</label>

                        @if (!empty($qdata['image_url']))
                            <img src="{{ $qdata['image_url'] }}" alt="Question Image" style="max-width: 100%; height: auto; margin-bottom: 1rem;">
                        @endif

                        <div style="display: flex; align-items: center; gap: 1rem; margin-top: 10px;">
                            @foreach($qdata['words'] as $position => $word)
                                @php
                                    $shuffledWords = $qdata['words'];
                                    shuffle($shuffledWords); // 🔀 Randomize the options
                                @endphp
                                <div style="text-align: center;">
                                    <div style="font-weight: bold; margin-bottom: 5px;">
                                        Position {{ $position + 1 }}
                                    </div>
                                    <select name="sentence_order[{{ $qnum }}][{{ $position }}]" style="min-width: 100px; padding: 5px;">
                                        <option value="">-- Select word --</option>
                                        @foreach($shuffledWords as $optionWord)
                                            <option value="{{ $optionWord }}">{{ ucfirst($optionWord) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            <div style="text-align: right; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Submit Answers</button>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const storageKey = 'written_eval_answers';

    // Load previous answers from localStorage
    const savedAnswers = JSON.parse(localStorage.getItem(storageKey) || '{}');

    // Apply saved answers to the form
    Object.entries(savedAnswers).forEach(([key, value]) => {
        const hiddenInput = document.getElementById(`selected-choice-${key}`);
        if (hiddenInput) {
            hiddenInput.value = value;

            const selected = value.split(',');
            document.querySelectorAll(`.choice-item[data-question="${key}"]`).forEach(choiceDiv => {
                if (selected.includes(choiceDiv.dataset.choice)) {
                    choiceDiv.style.backgroundColor = '#4CAF50';
                    choiceDiv.style.color = '#fff';
                    choiceDiv.style.fontWeight = 'bold';
                }
            });
        }

        // Fill-in-the-blank
        const fillInput = document.querySelector(`input[name="fill_in_blanks[${key}]"]`);
        if (fillInput && typeof value === 'string') {
            fillInput.value = value;
        }

        // Sentence Order
        if (typeof value === 'object') {
            Object.entries(value).forEach(([pos, word]) => {
                const select = document.querySelector(`select[name="sentence_order[${key}][${pos}]"]`);
                if (select) select.value = word;
            });
        }
    });

    // Choice selection handling
    document.querySelectorAll('.choice-item').forEach(choiceDiv => {
        choiceDiv.addEventListener('click', () => {
            const qnum = choiceDiv.dataset.question;
            const type = choiceDiv.dataset.type;
            const choice = choiceDiv.dataset.choice;
            const hiddenInput = document.getElementById(`selected-choice-${qnum}`);

            if (type === 'multiple_single') {
                document.querySelectorAll(`.choice-item[data-question="${qnum}"]`).forEach(el => {
                    el.style.backgroundColor = '';
                    el.style.color = '';
                    el.style.fontWeight = 'normal';
                });

                choiceDiv.style.backgroundColor = '#4CAF50';
                choiceDiv.style.color = '#fff';
                choiceDiv.style.fontWeight = 'bold';

                hiddenInput.value = choice;
                savedAnswers[qnum] = choice;

            } else if (type === 'multiple_multiple') {
                const selected = hiddenInput.value ? hiddenInput.value.split(',') : [];

                if (selected.includes(choice)) {
                    const index = selected.indexOf(choice);
                    selected.splice(index, 1);
                    choiceDiv.style.backgroundColor = '';
                    choiceDiv.style.color = '';
                    choiceDiv.style.fontWeight = 'normal';
                } else {
                    selected.push(choice);
                    choiceDiv.style.backgroundColor = '#4CAF50';
                    choiceDiv.style.color = '#fff';
                    choiceDiv.style.fontWeight = 'bold';
                }

                hiddenInput.value = selected.join(',');
                savedAnswers[qnum] = selected.join(',');
            }

            localStorage.setItem(storageKey, JSON.stringify(savedAnswers));
        });
    });

    // Save fill-in-the-blank inputs
    document.querySelectorAll('input[name^="fill_in_blanks["]').forEach(input => {
        input.addEventListener('input', () => {
            const name = input.name.match(/fill_in_blanks\[(\d+)\]/)[1];
            savedAnswers[name] = input.value;
            localStorage.setItem(storageKey, JSON.stringify(savedAnswers));
        });
    });

    // Save sentence order selects
    document.querySelectorAll('select[name^="sentence_order["]').forEach(select => {
        select.addEventListener('change', () => {
            const match = select.name.match(/sentence_order\[(\d+)\]\[(\d+)\]/);
            const qnum = match[1];
            const pos = match[2];

            if (!savedAnswers[qnum]) savedAnswers[qnum] = {};
            savedAnswers[qnum][pos] = select.value;

            localStorage.setItem(storageKey, JSON.stringify(savedAnswers));
        });
    });

    // Optional: clear saved answers on submit
    const form = document.getElementById('sentence-structure-form');
    form.addEventListener('submit', () => {
        localStorage.removeItem(storageKey);
    });
});
</script>



