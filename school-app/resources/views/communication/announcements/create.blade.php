@extends('layouts.app')
@section('title', 'Create Announcement')
@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <a href="{{ route('communication.announcements.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md text-label-md mb-2 hover:underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to Announcements
        </a>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">Create Announcement</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">Compose and publish a school announcement.</p>
    </div>
</div>

<div class="card p-6 max-w-2xl">
    <form action="{{ route('communication.announcements.store') }}" method="POST" id="form-announcement">
        @csrf

        {{-- Title --}}
        <div class="mb-5">
            <label for="title" class="block font-label-md text-label-md text-on-surface-variant mb-1.5">Title *</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255"
                class="w-full px-4 py-2.5 border border-outline-variant rounded-lg bg-surface text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                placeholder="e.g. End of Term Exam Timetable">
            @error('title') <p class="text-error font-label-md mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Type --}}
        <div class="mb-5">
            <label for="type" class="block font-label-md text-label-md text-on-surface-variant mb-1.5">Type *</label>
            <select name="type" id="type" required
                class="w-full px-4 py-2.5 border border-outline-variant rounded-lg bg-surface text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                <option value="general" {{ old('type') === 'general' ? 'selected' : '' }}>General</option>
                <option value="academic" {{ old('type') === 'academic' ? 'selected' : '' }}>Academic</option>
                <option value="financial" {{ old('type') === 'financial' ? 'selected' : '' }}>Financial</option>
                <option value="emergency" {{ old('type') === 'emergency' ? 'selected' : '' }}>Emergency</option>
            </select>
        </div>

        {{-- Target Audience --}}
        <div class="mb-5" x-data="{ audience: '{{ old('target_audience', 'all') }}' }">
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1.5">Target Audience *</label>
            <div class="flex flex-wrap gap-2">
                @foreach(['all' => 'Everyone', 'parents' => 'Parents Only', 'staff' => 'Staff Only', 'class' => 'Specific Class'] as $value => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="target_audience" value="{{ $value }}" x-model="audience" class="sr-only peer" {{ old('target_audience', 'all') === $value ? 'checked' : '' }}>
                    <span class="inline-block px-4 py-2 rounded-full font-label-md text-label-md border border-outline-variant peer-checked:bg-primary peer-checked:text-on-primary peer-checked:border-primary transition-colors">
                        {{ $label }}
                    </span>
                </label>
                @endforeach
            </div>

            <div x-show="audience === 'class'" x-transition class="mt-3">
                <select name="target_class_id" class="w-full px-4 py-2.5 border border-outline-variant rounded-lg bg-surface text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                    <option value="">Select a class...</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Body --}}
        <div class="mb-5">
            <label for="body" class="block font-label-md text-label-md text-on-surface-variant mb-1.5">Message *</label>
            <textarea name="body" id="body" rows="6" required
                class="w-full px-4 py-2.5 border border-outline-variant rounded-lg bg-surface text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                placeholder="Type your announcement...">{{ old('body') }}</textarea>
            @error('body') <p class="text-error font-label-md mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Options --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
            <div>
                <label for="expires_at" class="block font-label-md text-label-md text-on-surface-variant mb-1.5">Expires At (optional)</label>
                <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at') }}"
                    class="w-full px-4 py-2.5 border border-outline-variant rounded-lg bg-surface text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
            </div>
            <div class="flex flex-col justify-end gap-3">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/20">
                    <span class="font-body-md text-body-md text-on-surface">Pin to top</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="send_sms" value="1" {{ old('send_sms') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/20">
                    <span class="font-body-md text-body-md text-on-surface flex items-center gap-1">
                        Also send via SMS
                        <span class="material-symbols-outlined text-on-surface-variant text-[16px]">sms</span>
                    </span>
                </label>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-3 pt-4 border-t border-outline-variant">
            <button type="submit" class="btn-primary py-2.5 px-6 flex items-center gap-2" id="btn-publish">
                <span class="material-symbols-outlined text-[18px]">send</span>
                Publish Announcement
            </button>
            <a href="{{ route('communication.announcements.index') }}" class="btn-outlined py-2.5 px-6">Cancel</a>
        </div>
    </form>
</div>
@endsection
