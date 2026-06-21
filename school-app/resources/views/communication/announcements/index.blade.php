@extends('layouts.app')
@section('title', 'Announcements')
@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">Announcements</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">School-wide notices, updates, and alerts.</p>
    </div>
    @unless(auth()->user()->hasRole('Supervisor'))
    <div class="flex gap-3">
        <a href="{{ route('communication.sms.compose') }}" class="btn-outlined py-2 px-4 flex items-center gap-2" id="btn-sms-compose">
            <span class="material-symbols-outlined text-[18px]">sms</span>
            Send SMS
        </a>
        <a href="{{ route('communication.announcements.create') }}" class="btn-primary py-2 px-4 flex items-center gap-2" id="btn-new-announcement">
            <span class="material-symbols-outlined text-[18px]">add</span>
            New Announcement
        </a>
    </div>
    @endunless
</div>

{{-- Type Filter Chips --}}
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('communication.announcements.index') }}" class="px-4 py-1.5 rounded-full font-label-md text-label-md transition-colors {{ !request('type') ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}">All</a>
    @foreach(['general', 'academic', 'financial', 'emergency'] as $type)
    <a href="{{ route('communication.announcements.index', ['type' => $type]) }}" class="px-4 py-1.5 rounded-full font-label-md text-label-md transition-colors {{ request('type') === $type ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}">
        {{ ucfirst($type) }}
    </a>
    @endforeach
</div>

{{-- Emergency Broadcast --}}
@unless(auth()->user()->hasRole('Supervisor'))
<div class="card p-4 mb-6 border-l-4 border-error bg-error-container/10">
    <form action="{{ route('communication.emergency-broadcast') }}" method="POST" onsubmit="return confirm('This will send an SMS to ALL parents immediately. Are you sure?');">
        @csrf
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-error mt-1" style="font-size: 28px;">emergency</span>
            <div class="flex-1">
                <h3 class="font-title-md text-title-md font-semibold text-error mb-2">Emergency Broadcast</h3>
                <textarea name="message" rows="2" maxlength="640" placeholder="Type your emergency message... (e.g. School closes early today due to weather)" class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface text-body-md focus:outline-none focus:border-error focus:ring-2 focus:ring-error/20" required></textarea>
                <div class="flex justify-end mt-2">
                    <button type="submit" class="py-2 px-4 bg-error text-on-error font-label-md text-label-md rounded-lg hover:opacity-90 transition-opacity flex items-center gap-1.5" id="btn-emergency-send">
                        <span class="material-symbols-outlined text-[16px]">send</span>
                        Send to All Parents
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endunless

{{-- Announcements List --}}
@forelse($announcements as $announcement)
<div class="card p-5 mb-4 {{ $announcement->type === 'emergency' ? 'border-l-4 border-error' : '' }} {{ $announcement->is_pinned ? 'border-l-4 border-primary' : '' }}">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-2 flex-wrap">
                @if($announcement->is_pinned)
                    <span class="material-symbols-outlined text-primary text-[18px]">push_pin</span>
                @endif
                <span class="badge {{ match($announcement->type) { 'academic' => 'badge-info', 'financial' => 'badge-warning', 'emergency' => 'badge-error', default => 'badge-primary' } }} font-label-md">
                    {{ ucfirst($announcement->type) }}
                </span>
                <span class="badge {{ match($announcement->target_audience) { 'parents' => 'badge-secondary', 'staff' => 'badge-info', 'class' => 'badge-warning', default => 'badge-primary' } }} font-label-md">
                    {{ $announcement->target_audience === 'class' ? ($announcement->targetClass?->name ?? 'Class') : ucfirst($announcement->target_audience) }}
                </span>
                @if($announcement->sms_sent)
                    <span class="badge badge-success font-label-md flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">sms</span> SMS Sent
                    </span>
                @endif
            </div>
            <h3 class="font-title-lg text-title-lg font-semibold text-on-surface mb-1">{{ $announcement->title }}</h3>
            <p class="font-body-md text-body-md text-on-surface-variant whitespace-pre-line">{{ $announcement->body }}</p>
        </div>
    </div>
    <div class="mt-3 pt-3 border-t border-outline-variant flex items-center justify-between text-sm">
        <span class="font-label-md text-label-md text-outline flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">person</span>
            {{ $announcement->publisher?->name ?? 'System' }}
        </span>
        <span class="font-label-md text-label-md text-outline">
            {{ $announcement->published_at?->format('d M Y, h:i A') }}
        </span>
    </div>
</div>
@empty
<div class="card p-8 text-center">
    <span class="material-symbols-outlined text-outline mb-4" style="font-size: 64px;">campaign</span>
    <h3 class="font-title-lg text-title-lg text-on-surface mb-2">No Announcements Yet</h3>
    <p class="font-body-md text-body-md text-on-surface-variant mb-4">No active announcements at the moment.</p>
    @unless(auth()->user()->hasRole('Supervisor'))
    <a href="{{ route('communication.announcements.create') }}" class="btn-primary inline-flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Create Announcement
    </a>
    @endunless
</div>
@endforelse

{{ $announcements->links() }}
@endsection
