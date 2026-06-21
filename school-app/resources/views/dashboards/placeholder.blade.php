@extends('layouts.app')
@section('title', '{{ $title }} Dashboard')
@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">{{ $title ?? 'Dashboard' }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">{{ $subtitle ?? 'Welcome to your dashboard.' }}</p>
    </div>
</div>

<div class="card p-8 text-center">
    <span class="material-symbols-outlined text-outline mb-4" style="font-size: 64px;">{{ $icon ?? 'dashboard' }}</span>
    <h3 class="font-title-lg text-title-lg text-on-surface mb-2">{{ $title }} Dashboard</h3>
    <p class="font-body-md text-body-md text-on-surface-variant max-w-md mx-auto">
        This dashboard will be populated with relevant data in upcoming phases. The layout, sidebar, and navigation are fully functional.
    </p>
</div>
@endsection
