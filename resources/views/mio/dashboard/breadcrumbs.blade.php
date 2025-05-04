@php
    $breadcrumbs = [
        'mio.subject' => [
            ['label' => 'Subject', 'route' => 'mio.subject']
        ],
        'mio.subject.scores' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Scores', 'route' => null]
        ],
        'mio.subject.assignment' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Assignment', 'route' => 'mio.subject.assignment']
        ],
        'mio.subject.assignment-content' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Assignment', 'route' => 'mio.subject.assignment'],
            ['label' => $subjectTitle ?? 'Sample 1', 'route' => null]
        ],
        'mio.subject.announcement' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Announcement', 'route' => 'mio.subject.announcement']
        ],
        'mio.subject.announcement-body' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Announcement', 'route' => 'mio.subject.announcement'],
            ['label' => $announcementTitle ?? 'Details', 'route' => null]
        ],
        'mio.subject.module' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Module', 'route' => null]
        ],
        'mio.subject.module-body' => [
            ['label' => 'Subject', 'route' => 'mio.subject'],
            ['label' => 'Module', 'route' => 'mio.subject.module'],
            ['label' => $moduleTitle ?? 'Module 1', 'route' => null]
        ],
        'mio.ViewSubject' => [
            ['label' => 'Subjects', 'route' => 'mio.subject'],
            ['label' => $gradeLevel['name'] ?? 'Grade', 'route' => null]
        ],
        'mio.AddSubject' => [
            ['label' => 'Subjects', 'route' => 'mio.subject'],
            ['label' => $gradeLevel['name'] ?? 'Grade', 'route' => 'ViewSubject'],
            ['label' => 'Add Subject', 'route' => null]
        ],
        'mio.EditSubject' => [
            ['label' => 'Subjects', 'route' => 'subjects'],
            ['label' => $gradeLevel['name'] ?? 'Grade', 'route' => 'ViewSubject'],
            ['label' => 'Edit Subject', 'route' => null]
        ],
        // Other breadcrumbs untouched...
    ];


    $routeName = Route::currentRouteName();
    $currentBreadcrumbs = $breadcrumbs[$routeName] ?? [];

    // Define $isParentPage properly
    $lastCrumb = end($currentBreadcrumbs);
    $isParentPage = isset($lastCrumb['route']) && $lastCrumb['route'] === $routeName;
@endphp


<div class="text">
    @if ($isParentPage)
        {{-- Show only the page title --}}
        <span>{{ end($currentBreadcrumbs)['label'] }}</span>
    @else
        {{-- Show full breadcrumb trail --}}
        @foreach ($currentBreadcrumbs as $index => $crumb)
            @if ($crumb['route'])
            <a href="{{ is_array($crumb['route']) ? route($crumb['route'][0], array_slice($crumb['route'], 1)) : route($crumb['route']) }}">
                {{ $crumb['label'] }}
            </a>

            @else
                <span>{{ $crumb['label'] }}</span>
            @endif
            @if ($index < count($currentBreadcrumbs) - 1)
                &nbsp;&gt;&nbsp;
            @endif
        @endforeach
    @endif
</div>
