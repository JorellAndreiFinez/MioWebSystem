<section class="home-section">
    <div class="text">Subjects</div>
    <section class="grade-section">
        <div class="grade-header">Grade Levels</div>
        <div class="grade-grid-wrapper">

            {{-- Kinder --}}
            <div class="grade-group" style="margin-bottom: 3rem;">
                <h3>Kinder</h3>
                <div class="grade-grid">
                    @foreach($gradeLevels as $key => $gradeLevel)
                        @if($gradeLevel['value'] == 0)
                            <a href="{{ route('mio.ViewSubject', ['grade' => $key]) }}">
                                <div class="grade-card">
                                    <span class="icon"></span>
                                    <p>{{ $gradeLevel['name'] }}</p>
                                    <span class="arrow">&rsaquo;</span>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Elementary --}}
            <div class="grade-group" style="margin-bottom: 3rem;">
                <h3>Elementary</h3>
                <div class="grade-grid">
                    @foreach($gradeLevels as $key => $gradeLevel)
                        @if($gradeLevel['value'] >= 1 && $gradeLevel['value'] <= 6)
                            <a href="{{ route('mio.ViewSubject', ['grade' => $key]) }}">
                                <div class="grade-card">
                                    <span class="icon"></span>
                                    <p>{{ $gradeLevel['name'] }}</p>
                                    <span class="arrow">&rsaquo;</span>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Junior High School --}}
            <div class="grade-group" style="margin-bottom: 3rem;">
                <h3>Junior High School</h3>
                <div class="grade-grid">
                    @foreach($gradeLevels as $key => $gradeLevel)
                        @if($gradeLevel['value'] >= 7 && $gradeLevel['value'] <= 10)
                            <a href="{{ route('mio.ViewSubject', ['grade' => $key]) }}">
                                <div class="grade-card">
                                    <span class="icon"></span>
                                    <p>{{ $gradeLevel['name'] }}</p>
                                    <span class="arrow">&rsaquo;</span>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Senior High School --}}
            <div class="grade-group" style="margin-bottom: 3rem;">
                <h3>Senior High School</h3>
                <div class="grade-grid">
                    @foreach($gradeLevels as $key => $gradeLevel)
                        @if($gradeLevel['value'] >= 11 && $gradeLevel['value'] <= 12)
                            <a href="{{ route('mio.ViewSubject', ['grade' => $key]) }}">
                                <div class="grade-card">
                                    <span class="icon"></span>
                                    <p>{{ $gradeLevel['name'] }}</p>
                                    <span class="arrow">&rsaquo;</span>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

        </div>
    </section>
</section>
