<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="font-headline-md text-on-background">Timetable Builder</h2>
            <p class="font-body-md text-on-surface-variant">Design weekly schedules and manage classes, rooms, and teachers.</p>
        </div>

        <div class="w-full md:w-64">
            <label class="font-label-md text-on-surface-variant mb-1 block">Select Class</label>
            <select wire:model.live="selectedClassId" class="input-field w-full">
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->schoolClass->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($selectedClassId)
        <div class="grid grid-cols-1 {{ auth()->user()->hasAnyRole(['Proprietor', 'HeadTeacher']) ? 'lg:grid-cols-3' : '' }} gap-6">
            {{-- Add Slot Form (Only for Admins) --}}
            @if(auth()->user()->hasAnyRole(['Proprietor', 'HeadTeacher']))
                <div class="card p-6 h-fit space-y-4">
                    <h3 class="font-title-md text-on-surface border-b border-outline-variant pb-2">Add Timetable Slot</h3>

                    @if (session()->has('success'))
                        <div class="p-3 rounded bg-[#d1fae5] text-[#065f46] font-body-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="addSlot" class="space-y-4">
                        <div>
                            <label class="font-label-md text-on-surface-variant mb-1 block">Day of the Week</label>
                            <select wire:model="day" class="input-field w-full">
                                @foreach($days as $d)
                                    <option value="{{ $d }}">{{ ucfirst($d) }}</option>
                                @endforeach
                            </select>
                            @error('day') <span class="text-error font-label-md mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="font-label-md text-on-surface-variant mb-1 block">Subject</label>
                            <select wire:model="subjectId" class="input-field w-full">
                                <option value="">Select subject...</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code }})</option>
                                @endforeach
                            </select>
                            @error('subjectId') <span class="text-error font-label-md mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="font-label-md text-on-surface-variant mb-1 block">Start Time</label>
                                <input type="time" wire:model="startTime" class="input-field w-full">
                                @error('startTime') <span class="text-error font-label-md mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="font-label-md text-on-surface-variant mb-1 block">End Time</label>
                                <input type="time" wire:model="endTime" class="input-field w-full">
                                @error('endTime') <span class="text-error font-label-md mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="font-label-md text-on-surface-variant mb-1 block">Room / Location</label>
                            <input type="text" wire:model="room" placeholder="e.g. Room 101, Lab A" class="input-field w-full">
                            @error('room') <span class="text-error font-label-md mt-1">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="button-primary w-full justify-center">
                            <span class="material-symbols-outlined text-[20px]">add</span>
                            Add Schedule Slot
                        </button>
                    </form>
                </div>
            @endif

            {{-- Timetable Preview --}}
            <div class="card p-6 {{ auth()->user()->hasAnyRole(['Proprietor', 'HeadTeacher']) ? 'lg:col-span-2' : '' }} space-y-6">
                <h3 class="font-title-md text-on-surface border-b border-outline-variant pb-2">Weekly Schedule View</h3>

                @if($slots->count() > 0)
                    <div class="space-y-6">
                        @foreach($days as $d)
                            @php
                                $daySlots = $slots->filter(fn($slot) => $slot->day_of_week === $d);
                            @endphp
                            <div class="border border-outline-variant rounded-lg overflow-hidden">
                                <div class="bg-surface-container-low px-4 py-2 font-title-sm text-on-surface font-semibold flex justify-between items-center">
                                    <span>{{ ucfirst($d) }}</span>
                                    <span class="bg-primary-container text-on-primary-container px-2 py-0.5 rounded text-[12px] font-medium">
                                        {{ $daySlots->count() }} {{ Str::plural('slot', $daySlots->count()) }}
                                    </span>
                                </div>
                                @if($daySlots->count() > 0)
                                    <div class="divide-y divide-outline-variant">
                                        @foreach($daySlots as $slot)
                                            <div class="p-4 flex items-center justify-between hover:bg-surface-container-lowest transition-colors">
                                                <div class="flex items-start gap-3">
                                                    <span class="material-symbols-outlined text-primary mt-0.5">schedule</span>
                                                    <div>
                                                        <div class="font-body-md font-medium text-on-surface">
                                                            {{ $slot->subject->name }}
                                                        </div>
                                                        <div class="font-label-md text-on-surface-variant flex flex-wrap gap-x-4 gap-y-1 mt-1">
                                                            <span class="flex items-center gap-1">
                                                                <span class="material-symbols-outlined text-[14px]">person</span>
                                                                {{ $slot->teacher->full_name }}
                                                            </span>
                                                            <span class="flex items-center gap-1">
                                                                <span class="material-symbols-outlined text-[14px]">meeting_room</span>
                                                                {{ $slot->room ?: 'No room assigned' }}
                                                            </span>
                                                            <span class="flex items-center gap-1 text-primary">
                                                                <span class="material-symbols-outlined text-[14px]">alarm</span>
                                                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if(auth()->user()->hasAnyRole(['Proprietor', 'HeadTeacher']))
                                                    <button wire:click="deleteSlot({{ $slot->id }})" class="p-1 text-outline hover:text-error transition-colors rounded-full hover:bg-surface-container-high" title="Delete slot">
                                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-4 text-center text-outline font-body-sm">
                                        No scheduled lessons for this day.
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center border-2 border-dashed border-outline-variant rounded-lg">
                        <span class="material-symbols-outlined text-outline text-[48px] mb-4">calendar_today</span>
                        <p class="font-body-lg text-on-surface-variant">No slots scheduled yet for this class.</p>
                        <p class="font-body-sm text-outline mt-1">Use the form on the left to add your first lesson.</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card p-12 text-center">
            <span class="material-symbols-outlined text-outline text-[48px] mb-4">calendar_today</span>
            <p class="font-body-lg text-on-surface-variant">Please select a class to build its weekly timetable.</p>
        </div>
    @endif

    {{-- Loading indicator --}}
    <div wire:loading class="fixed bottom-4 right-4 bg-primary text-on-primary px-4 py-2 rounded-full shadow-lg font-label-md flex items-center gap-2 z-50">
        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        Processing...
    </div>
</div>
