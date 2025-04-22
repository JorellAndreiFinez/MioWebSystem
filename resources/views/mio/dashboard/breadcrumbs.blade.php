@php
    $breadcrumbs = [
        'mio.subject' => [
            ['label' => 'Subject', 'route' => 'mio.subject']
        ],
       'mio.subject.scores' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Scores', 'route' => null] // No route means current page = title only
        ],

        'mio.subject.assignment' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Assignment', 'route' => 'mio.subject.assignment']
        ],
        'mio.subject.assignment-content' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Assignment', 'route' => 'mio.subject.assignment'],
            ['label' => 'Sample 1', 'route' => null]
        ],
        'mio.subject.announcement' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Announcement', 'route' => 'mio.subject.announcement']
        ],
        'mio.subject.announcement-body' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Announcement', 'route' => 'mio.subject.announcement'],
            ['label' => 'Details', 'route' => null]
        ],
        'mio.subject.module' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Module', 'route' => null]
        ],
        'mio.subject.module-body' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Module', 'route' => 'mio.subject.module'],
            ['label' => 'Module 1', 'route' => null]
        ],
        // Add more as needed...
    ];

    $routeName = Route::currentRouteName();
    $currentBreadcrumbs = $breadcrumbs[$routeName] ?? [];
    $isParentPage = !empty($currentBreadcrumbs) && end($currentBreadcrumbs)['route'] === $routeName;
@endphp

<div class="text">
    @if ($isParentPage)
        {{-- Show only the page title --}}
        <span>{{ end($currentBreadcrumbs)['label'] }}</span>
    @else
        {{-- Show full breadcrumb trail --}}
        @foreach ($currentBreadcrumbs as $index => $crumb)
            @if ($crumb['route'])
                <a href="{{ route($crumb['route']) }}">{{ $crumb['label'] }}</a>
            @else
                <span>{{ $crumb['label'] }}</span>
            @endif
            @if ($index < count($currentBreadcrumbs) - 1)
                &nbsp;&gt;&nbsp;
            @endif
        @endforeach
    @endif
</div>
