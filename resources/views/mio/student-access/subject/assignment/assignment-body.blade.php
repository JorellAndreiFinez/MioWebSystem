<section class="home-section">
    <!-- 🟦 Header Banner -->
    <main class="main-banner">
        <div class="welcome-banner">
            <div class="banner">
                <div class="content">
                    <h5>Assignments</h5>
                </div>
            </div>
        </div>
    </main>

    <!-- 📄 Assignment Cards List -->
    <main class="main-assignment-content">
       <div class="assignment-card">
            <div class="activity-info">
                <h1>{{ $assignment['title'] }}</h1>
            </div>

            <div class="details">
                 <div>
                    <span>Publish at</span>
                        <strong>{{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($assignment['published_at'])->format('Y-m-d') . ' ' . ($assignment['availability']['start'] ?? '00:00'))->format('F j, Y g:i A') }}</strong>
                </div>
                <div>
                    <span>Deadline</span>
                    <strong>
                        {{ \Carbon\Carbon::parse(\Carbon\Carbon::parse($assignment['created_at'])->format('Y-m-d') . ' ' . ($assignment['availability']['end'] ?? '00:00'))->format('F j, Y g:i A')}}

                    </strong>
                </div>
                <div>
                    <span>Points</span>
                    <strong>{{ $assignment['points']['earned'] }} / {{ $assignment['points']['total']}}</strong>
                </div>
                <div>
                    <span>Attempts</span>
                    <strong>{{ $assignment['attempts'] }}</strong>
                </div>
            </div>

            <!-- Assignment Description -->
            @if (!empty($assignment['description']))
                <div class="assignment-description" style="margin-top: 15px;">
                    <h4>Description</h4>
                    <p>{{ $assignment['description'] }}</p>
                </div>
            @endif

            <!-- Assignment Attachments -->
            @if (!empty($assignment['attachments']))
                <div class="assignment-attachments" style="margin-top: 15px;">
                    <h4>Attachments</h4>
                    <ul>
                        @foreach ($assignment['attachments'] as $attachment)
                            @if (!empty($attachment['file']))
                                <li>
                                    📎 <a href="{{ $attachment['file'] }}" target="_blank">{{ basename($attachment['file']) }}</a>
                                </li>
                            @endif
                            @if (!empty($attachment['link']))
                                <li>
                                    🔗 <a href="{{ $attachment['link'] }}" target="_blank">{{ $attachment['link'] }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

            </div>

    </main>
</section>
