<section class="home-section">
    <div class="text">Subjects</div>
    <section class="grade-section">
    <div class="grade-header">Grade Levels</div>
    <div class="grade-grid">
                @foreach($gradeLevels as $key => $gradeLevel)
                    <a href="{{ route('mio.ViewSubject', ['grade' => $key]) }}">
                        <div class="grade-card">
                            <span class="icon"></span>
                            <p>{{ $gradeLevel['name'] }}</p>
                            <span class="arrow">&rsaquo;</span>
                        </div>
                    </a>
                @endforeach
            </div>
</section>


</section>


