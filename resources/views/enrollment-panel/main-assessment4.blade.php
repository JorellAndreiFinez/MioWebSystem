<section class="home-section">
    <div class="text">Written Evaluation</div>

    <div class="evaluation-section" style="padding: 2rem; max-width: 800px; margin: auto;">
        <h3>Grammar & Sentence Test</h3>
        <p class="mb-4">
            This test measures language comprehension, vocabulary, and sentence formation skills.
            You will complete sentences by choosing the most appropriate word or phrase.
        </p>

        <form id="sentence-structure-form" action="{{ route('assessment.written.submit') }}" method="POST">
    @csrf

    @foreach($questions as $qnum => $qdata)
        <div class="card mb-4 p-3">
            @if($qdata['type'] == 'multiple_choice')
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
                <input type="hidden" name="selected_choices[{{ $qnum }}]" id="selected-choice-{{ $qnum }}" required>

            @elseif($qdata['type'] == 'fill_in_blank')
                <label><strong>Question {{ $qnum }}:</strong> {{ $qdata['sentence'] }}</label>
                <input type="text" name="fill_in_blanks[{{ $qnum }}]" placeholder="{{ $qdata['answer_placeholder'] }}" required
                    style="width: 100%; padding: 8px; margin-top: 10px;">

            @elseif($qdata['type'] == 'sentence_order')
                <label><strong>Question {{ $qnum }}:</strong> {{ $qdata['sentence_hint'] }}</label>
                <div style="margin-top: 10px;">
                    @foreach($qdata['words'] as $index => $word)
                        <label for="order-{{ $qnum }}-{{ $index }}">{{ $word }}</label>
                        <select name="sentence_order[{{ $qnum }}][{{ $index }}]" id="order-{{ $qnum }}-{{ $index }}" required>
                            <option value="">Select position</option>
                            @for($pos=1; $pos <= count($qdata['words']); $pos++)
                                <option value="{{ $pos }}">{{ $pos }}</option>
                            @endfor
                        </select>
                    @endforeach
                </div>

            @elseif($qdata['type'] == 'true_false')
                <label><strong>Question {{ $qnum }}:</strong> {{ $qdata['statement'] }}</label>
                <div style="margin-top: 10px;">
                    <label><input type="radio" name="true_false[{{ $qnum }}]" value="true" required> True</label>
                    <label style="margin-left: 20px;"><input type="radio" name="true_false[{{ $qnum }}]" value="false" required> False</label>
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
    // Handle choice selection
    document.querySelectorAll('.choice-item').forEach(choiceDiv => {
        choiceDiv.addEventListener('click', () => {
            const qnum = choiceDiv.dataset.question;
            const choice = choiceDiv.dataset.choice;

            // Set hidden input value
            document.getElementById(`selected-choice-${qnum}`).value = choice;

            // Remove "selected" style from siblings
            document.querySelectorAll(`.choice-item[data-question="${qnum}"]`).forEach(el => {
                el.style.backgroundColor = '';
                el.style.color = '';
                el.style.fontWeight = 'normal';
            });

            // Highlight selected
            choiceDiv.style.backgroundColor = '#4CAF50';
            choiceDiv.style.color = '#fff';
            choiceDiv.style.fontWeight = 'bold';
        });
    });
</script>
