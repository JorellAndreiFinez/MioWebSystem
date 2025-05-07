
<section class="home-section">
      <div class="text">Home Page</div>
        <!-- FIRST ROW - GRID -->
        <div class="grid-row">
            <div class="grade-header">Navigation Menu</div>

            <section class="grade-section">
            <div class="grade-grid">
                @foreach($pidcms as $key => $cms)
                    <a href="{{ route('#', ['grade' => $key]) }}">
                        <div class="grade-card">
                            <span class="icon"></span>
                            <p>{{ $cms['name'] }}</p>
                            <span class="arrow">&rsaquo;</span>
                        </div>
                    </a>
                @endforeach
            </div>
            </section>


        </div>



  </section>
