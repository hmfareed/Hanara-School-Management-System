@extends('layouts.app')
@section('title', 'Compose SMS')
@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <a href="{{ route('communication.announcements.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md text-label-md mb-2 hover:underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to Communication
        </a>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">Compose SMS</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">Send bulk SMS to parents via the Arkesel gateway.</p>
    </div>
</div>

<div class="card p-6 max-w-2xl">
    <form action="{{ route('communication.sms.send') }}" method="POST" id="form-sms" onsubmit="return confirm('Send SMS to the selected recipients?');">
        @csrf

        {{-- Recipient Type --}}
        <div class="mb-5" x-data="{ recipientType: '{{ old('recipient_type', 'all_parents') }}' }">
            <label class="block font-label-md text-label-md text-on-surface-variant mb-2">Recipients *</label>
            <div class="space-y-2">
                <label class="flex items-center gap-3 p-3 rounded-lg border border-outline-variant cursor-pointer hover:bg-surface-container-lowest transition-colors" :class="recipientType === 'all_parents' ? 'border-primary bg-primary-container/10' : ''">
                    <input type="radio" name="recipient_type" value="all_parents" x-model="recipientType" class="w-4 h-4 text-primary focus:ring-primary/20">
                    <div>
                        <span class="font-body-md text-body-md font-medium text-on-surface">All Parents</span>
                        <span class="font-label-md text-label-md text-on-surface-variant block">Send to every guardian with a phone number on file</span>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-lg border border-outline-variant cursor-pointer hover:bg-surface-container-lowest transition-colors" :class="recipientType === 'class' ? 'border-primary bg-primary-container/10' : ''">
                    <input type="radio" name="recipient_type" value="class" x-model="recipientType" class="w-4 h-4 text-primary focus:ring-primary/20">
                    <div>
                        <span class="font-body-md text-body-md font-medium text-on-surface">Specific Class</span>
                        <span class="font-label-md text-label-md text-on-surface-variant block">Send to parents of students in one class</span>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-lg border border-outline-variant cursor-pointer hover:bg-surface-container-lowest transition-colors" :class="recipientType === 'custom' ? 'border-primary bg-primary-container/10' : ''">
                    <input type="radio" name="recipient_type" value="custom" x-model="recipientType" class="w-4 h-4 text-primary focus:ring-primary/20">
                    <div>
                        <span class="font-body-md text-body-md font-medium text-on-surface">Custom Numbers</span>
                        <span class="font-label-md text-label-md text-on-surface-variant block">Enter phone numbers manually</span>
                    </div>
                </label>
            </div>

            {{-- Class selector --}}
            <div x-show="recipientType === 'class'" x-transition class="mt-3">
                <select name="class_id" class="w-full px-4 py-2.5 border border-outline-variant rounded-lg bg-surface text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                    <option value="">Select a class...</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Custom numbers --}}
            <div x-show="recipientType === 'custom'" x-transition class="mt-3">
                <textarea name="custom_numbers" rows="3" placeholder="Enter phone numbers, one per line or separated by commas&#10;e.g. 0244123456, 0201234567"
                    class="w-full px-4 py-2.5 border border-outline-variant rounded-lg bg-surface text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">{{ old('custom_numbers') }}</textarea>
            </div>
        </div>

        {{-- Message --}}
        <div class="mb-5" x-data="{ charCount: 0, maxChars: 640 }">
            <label for="message" class="block font-label-md text-label-md text-on-surface-variant mb-1.5">Message *</label>
            <textarea name="message" id="message" rows="5" required maxlength="640"
                x-on:input="charCount = $el.value.length"
                class="w-full px-4 py-2.5 border border-outline-variant rounded-lg bg-surface text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                placeholder="Type your SMS message...">{{ old('message') }}</textarea>
            <div class="flex justify-between mt-1">
                <p class="font-label-md text-label-md text-on-surface-variant">
                    <span class="material-symbols-outlined text-[14px] align-middle">info</span>
                    Standard SMS = 160 chars. Messages over 160 chars are sent as multiple SMS parts.
                </p>
                <span class="font-label-md text-label-md" :class="charCount > 480 ? 'text-error' : 'text-on-surface-variant'" x-text="charCount + '/' + maxChars"></span>
            </div>
            @error('message') <p class="text-error font-label-md mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-3 pt-4 border-t border-outline-variant">
            <button type="submit" class="btn-primary py-2.5 px-6 flex items-center gap-2" id="btn-send-sms">
                <span class="material-symbols-outlined text-[18px]">send</span>
                Send SMS
            </button>
            <a href="{{ route('communication.announcements.index') }}" class="btn-outlined py-2.5 px-6">Cancel</a>
        </div>
    </form>
</div>
@endsection
