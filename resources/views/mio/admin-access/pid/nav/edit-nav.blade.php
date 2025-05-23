<section class="home-section">
    <h2>Edit Components for {{ $key }}</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('mio.cms.homepage.update') }}" id="cmsForm">
        @csrf
        <input type="hidden" name="key" value="{{ $key }}">

        <div id="componentsContainer">
            @foreach($cms['components'] as $index => $component)
                <div class="component-block" data-index="{{ $index }}">
                    <h3>Component #{{ $index + 1 }}: {{ ucfirst($component['type']) }} - {{ $component['name'] }}</h3>

                    {{-- Edit content based on type --}}
                    @if($component['type'] === 'carousel')
                        <h4>Slides:</h4>
                        @foreach($component['content'] as $slideIndex => $slide)
                            <div class="slide-block" data-slide-index="{{ $slideIndex }}">
                                <label>Title:
                                    <input type="text" name="components[{{ $index }}][content][{{ $slideIndex }}][title]" value="{{ $slide['title'] }}">
                                </label>
                                <label>Description:
                                    <textarea name="components[{{ $index }}][content][{{ $slideIndex }}][description]">{{ $slide['description'] }}</textarea>
                                </label>
                                <label>Image URL:
                                    <input type="text" name="components[{{ $index }}][content][{{ $slideIndex }}][image]" value="{{ $slide['image'] }}">
                                </label>
                                <hr>
                            </div>
                        @endforeach

                    @elseif($component['type'] === 'welcome')
                        <label>Heading:
                            <input type="text" name="components[{{ $index }}][content][heading]" value="{{ $component['content']['heading'] }}">
                        </label>
                        <label>Paragraph:
                            <textarea name="components[{{ $index }}][content][paragraph]">{{ $component['content']['paragraph'] }}</textarea>
                        </label>
                        <label>Image URL:
                            <input type="text" name="components[{{ $index }}][content][image]" value="{{ $component['content']['image'] }}">
                        </label>

                    @elseif($component['type'] === 'reasons')
                        <h4>Reasons:</h4>
                        @foreach($component['content'] as $reasonIndex => $reason)
                            <div class="reason-block" data-reason-index="{{ $reasonIndex }}">
                                <label>Title:
                                    <input type="text" name="components[{{ $index }}][content][{{ $reasonIndex }}][title]" value="{{ $reason['title'] }}">
                                </label>
                                <label>Description:
                                    <textarea name="components[{{ $index }}][content][{{ $reasonIndex }}][description]">{{ $reason['description'] }}</textarea>
                                </label>
                                <label>Icon URL:
                                    <input type="text" name="components[{{ $index }}][content][{{ $reasonIndex }}][icon]" value="{{ $reason['icon'] }}">
                                </label>
                                <hr>
                            </div>
                        @endforeach
                    @endif

                    {{-- Optionally add a remove button for component --}}
                </div>
                <hr>
            @endforeach
        </div>

        <button type="submit">Save All Components</button>
    </form>
</section>
