<section class="home-section">
    <div class="text">Edit Content for Component: {{ $component['name'] }}</div>

    <form action="{{ route('mio.cms.update-component', ['key' => $key, 'component' => $componentIndex]) }}" method="POST">
        @csrf

        @foreach($component['fields'] as $fieldKey => $field)
            <label for="{{ $fieldKey }}">{{ $field['label'] }}</label>

            @if($field['type'] === 'text')
                <input type="text" name="{{ $fieldKey }}" id="{{ $fieldKey }}" value="{{ old($fieldKey, $field['value']) }}">
            @elseif($field['type'] === 'textarea')
                <textarea name="{{ $fieldKey }}" id="{{ $fieldKey }}">{{ old($fieldKey, $field['value']) }}</textarea>
            @elseif($field['type'] === 'image')
                <input type="file" name="{{ $fieldKey }}" id="{{ $fieldKey }}">
                @if($field['value'])
                    <img src="{{ asset($field['value']) }}" alt="Current Image" style="max-width:150px; display:block; margin-top:10px;">
                @endif
            @endif
        @endforeach

        <button type="submit">Save Changes</button>
    </form>

    <a href="{{ route('mio.cms.edit-nav', ['key' => $key]) }}">Back to Components List</a>
</section>
