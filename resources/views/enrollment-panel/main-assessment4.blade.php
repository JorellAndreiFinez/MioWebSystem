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
                    @if(!empty($qdata['image_url']))
                        <img src="{{ $qdata['image_url'] }}" alt="Question Image" style="max-width: 100%; height: auto; margin-bottom: 1rem;">
                    @endif

                    {{-- Multiple Choice --}}
                    @if($qdata['type'] === 'multiple_choice')
                        <label><strong>Question {{ $qnum }}:</strong> {{ $qdata['sentence'] }}</label>
                        <div class="choices mb-2" style="display: flex; gap: 10px; flex-wrap: wrap;">
                            @foreach($qdata['choices'] as $choice)
                                <div class="choice-item"
                                    data-question="{{ $qnum }}"
                                    data-choice="{{ $choice }}"
                                    style="padding: 10px 15px; border: 1px solid #ccc; border-radius: 5px; cursor: pointer; user-select: none;">
                                    {{ $choice }}
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="selected_choices[{{ $qnum }}]" id="selected-choice-{{ $qnum }}">

                    {{-- Fill in the Blank --}}
                    @elseif($qdata['type'] === 'fill_in_blank')
                        <label><strong>Question {{ $qnum }}:</strong> {{ $qdata['sentence'] }}</label>
                        <input type="text" name="fill_in_blanks[{{ $qnum }}]" placeholder="{{ $qdata['answer_placeholder'] ?? 'Enter answer' }}"
                            style="width: 100%; padding: 8px; margin-top: 10px;">

                   {{-- For syntax type (sentence ordering) --}}
                    @elseif($qdata['type'] === 'sentence_order')
                        <label><strong>Question {{ $qnum }}:</strong> {{ $qdata['sentence_hint'] }}</label>

                        <div style="display: flex; align-items: center; gap: 1rem; margin-top: 10px;">
                            @foreach($qdata['words'] as $position => $word)
                                <div style="text-align: center;">
                                    {{-- Show position number (optional) --}}
                                    <div style="font-weight: bold; margin-bottom: 5px;">
                                        Position {{ $position + 1 }}
                                    </div>

                                    {{-- Select input to pick word for this position --}}
                                    <select name="sentence_order[{{ $qnum }}][{{ $position }}]" style="min-width: 100px; padding: 5px;">
                                        <option value="">-- Select word --</option>
                                        @foreach($qdata['words'] as $optionWord)
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
    const startTimeInput = document.getElementById('start_time');
    startTimeInput.value = new Date().toISOString();
    console.log('Start time set to:', startTimeInput.value);

    document.querySelectorAll('.choice-item').forEach(choiceDiv => {
        choiceDiv.addEventListener('click', () => {
            const qnum = choiceDiv.dataset.question;
            const choice = choiceDiv.dataset.choice;
            console.log(`Choice clicked: question ${qnum}, choice: ${choice}`);

            const hiddenInput = document.getElementById(`selected-choice-${qnum}`);
            if (hiddenInput) {
                hiddenInput.value = choice;
                console.log(`Hidden input updated: selected-choice-${qnum} = ${choice}`);
            } else {
                console.warn(`Hidden input not found for question ${qnum}`);
            }

            document.querySelectorAll(`.choice-item[data-question="${qnum}"]`).forEach(el => {
                el.style.backgroundColor = '';
                el.style.color = '';
                el.style.fontWeight = 'normal';
            });

            choiceDiv.style.backgroundColor = '#4CAF50';
            choiceDiv.style.color = '#fff';
            choiceDiv.style.fontWeight = 'bold';
        });
    });

    // Optional: on form submit, dump all form data in console for debug
    const form = document.getElementById('sentence-structure-form');
    form.addEventListener('submit', (e) => {
        // Dump form data
        const formData = new FormData(form);
        const entries = {};
        formData.forEach((value, key) => {
            entries[key] = value;
        });
        console.log('Submitting form data:', entries);
    });
});
</script>

