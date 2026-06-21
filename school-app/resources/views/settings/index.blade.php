@extends('layouts.app')

@section('title', 'School Settings')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ activeTab: 'school' }">
    <!-- Page Header -->
    <div class="mb-section-gap flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">System Settings</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Configure school profile, academic calendar, grading structure, and integrations.</p>
        </div>
    </div>

    <!-- Error Summary -->
    @if ($errors->any())
        <div class="mb-6 p-4 bg-error-container text-on-error-container rounded-xl flex flex-col gap-1 border border-error/20" id="errors-summary">
            <div class="flex items-center gap-2 font-medium">
                <span class="material-symbols-outlined">error</span>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc list-inside text-sm pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="flex border-b border-outline-variant mb-6 overflow-x-auto gap-2">
        <button @click="activeTab = 'school'" 
                :class="activeTab === 'school' ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                class="pb-3 px-4 border-b-2 font-body-md text-body-md transition-colors whitespace-nowrap">
            School Profile
        </button>
        <button @click="activeTab = 'grading'" 
                :class="activeTab === 'grading' ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                class="pb-3 px-4 border-b-2 font-body-md text-body-md transition-colors whitespace-nowrap">
            Grading System
        </button>
        <button @click="activeTab = 'calendar'" 
                :class="activeTab === 'calendar' ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                class="pb-3 px-4 border-b-2 font-body-md text-body-md transition-colors whitespace-nowrap">
            Academic Calendar
        </button>
        <button @click="activeTab = 'sms'" 
                :class="activeTab === 'sms' ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                class="pb-3 px-4 border-b-2 font-body-md text-body-md transition-colors whitespace-nowrap">
            SMS Gateway
        </button>
        <button @click="activeTab = 'audit'" 
                :class="activeTab === 'audit' ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                class="pb-3 px-4 border-b-2 font-body-md text-body-md transition-colors whitespace-nowrap">
            Audit Trail
        </button>
    </div>

    <!-- Settings Form -->
    <form action="{{ route('settings.update') }}" method="POST">
        @csrf

        <!-- Tab: School Profile -->
        <div x-show="activeTab === 'school'" class="card p-6 md:p-8 space-y-6">
            <h3 class="font-title-lg text-title-lg text-on-surface font-semibold mb-4">School Profile</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="form-label" for="school_name">School Name</label>
                    <input class="form-input-custom" type="text" name="settings[school_name]" id="school_name" 
                           value="{{ old('settings.school_name', \App\Models\Setting::get('school_name')) }}">
                </div>
                <div>
                    <label class="form-label" for="school_motto">School Motto</label>
                    <input class="form-input-custom" type="text" name="settings[school_motto]" id="school_motto" 
                           value="{{ old('settings.school_motto', \App\Models\Setting::get('school_motto')) }}">
                </div>
                <div>
                    <label class="form-label" for="school_phone">Phone Number</label>
                    <input class="form-input-custom" type="text" name="settings[school_phone]" id="school_phone" 
                           value="{{ old('settings.school_phone', \App\Models\Setting::get('school_phone')) }}">
                </div>
                <div>
                    <label class="form-label" for="school_email">School Email</label>
                    <input class="form-input-custom" type="email" name="settings[school_email]" id="school_email" 
                           value="{{ old('settings.school_email', \App\Models\Setting::get('school_email')) }}">
                </div>
                <div class="md:col-span-2">
                    <label class="form-label" for="school_address">Physical Address</label>
                    <textarea class="form-input-custom h-24" name="settings[school_address]" id="school_address">{{ old('settings.school_address', \App\Models\Setting::get('school_address')) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    Save Profile Settings
                </button>
            </div>
        </div>

        <!-- Tab: Grading System -->
        <div x-show="activeTab === 'grading'" class="card p-6 md:p-8 space-y-6" x-data="{ ca: {{ old('settings.ca_weight', \App\Models\Setting::get('ca_weight', 30)) }}, exam: {{ old('settings.exam_weight', \App\Models\Setting::get('exam_weight', 70)) }} }">
            <h3 class="font-title-lg text-title-lg text-on-surface font-semibold mb-4">Grading & Assessment Weights</h3>
            
            <div class="p-4 bg-primary-container/20 border border-primary/10 text-on-primary-container rounded-xl flex items-start gap-3 mb-6">
                <span class="material-symbols-outlined text-primary mt-0.5">info</span>
                <p class="font-body-sm text-body-sm">
                    In accordance with GES standards, assessment weights are split between Class Assessment (CA) and End of Term Exam. Their sum must equal exactly 100%.
                </p>
            </div>

            <div class="space-y-6">
                <!-- CA Weight Slider -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="form-label mb-0" for="ca_weight">Continuous Assessment (CA) Weight</label>
                        <span class="font-title-md text-title-md font-semibold text-primary" x-text="ca + '%'"></span>
                    </div>
                    <input class="w-full accent-primary h-2 bg-outline-variant rounded-lg cursor-pointer" type="range" name="settings[ca_weight]" id="ca_weight" min="0" max="100" step="5" x-model="ca" @input="exam = 100 - ca">
                </div>

                <!-- Exam Weight Slider -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="form-label mb-0" for="exam_weight">End of Term Exam Weight</label>
                        <span class="font-title-md text-title-md font-semibold text-primary" x-text="exam + '%'"></span>
                    </div>
                    <input class="w-full accent-primary h-2 bg-outline-variant rounded-lg cursor-pointer" type="range" name="settings[exam_weight]" id="exam_weight" min="0" max="100" step="5" x-model="exam" @input="ca = 100 - exam">
                </div>

                <div class="border-t border-outline-variant pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label" for="bece_at_risk_threshold">BECE At-Risk Cutoff Score</label>
                        <p class="font-body-xs text-body-xs text-on-surface-variant mb-2">Students scoring below this cumulative raw score will be marked at-risk on dashboards.</p>
                        <input class="form-input-custom" type="number" name="settings[bece_at_risk_threshold]" id="bece_at_risk_threshold" 
                               value="{{ old('settings.bece_at_risk_threshold', \App\Models\Setting::get('bece_at_risk_threshold', 36)) }}">
                    </div>
                    <div>
                        <label class="form-label" for="primary_jhs_grading_type">Grading Metric Type</label>
                        <p class="font-body-xs text-body-xs text-on-surface-variant mb-2">Select the assessment metric applied for grading output.</p>
                        <select class="form-input-custom" name="settings[primary_jhs_grading_type]" id="primary_jhs_grading_type">
                            <option value="numeric" {{ old('settings.primary_jhs_grading_type', \App\Models\Setting::get('primary_jhs_grading_type')) === 'numeric' ? 'selected' : '' }}>Numeric (0-100 & Stanine 1-9)</option>
                            <option value="letter" {{ old('settings.primary_jhs_grading_type', \App\Models\Setting::get('primary_jhs_grading_type')) === 'letter' ? 'selected' : '' }}>Letter Grades (A, B, C, D, F)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    Save Grading Weights
                </button>
            </div>
        </div>

        <!-- Tab: Academic Calendar -->
        <div x-show="activeTab === 'calendar'" class="card p-6 md:p-8 space-y-6">
            <h3 class="font-title-lg text-title-lg text-on-surface font-semibold mb-4">Academic Calendar State</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @php
                    $academicYears = \App\Models\AcademicYear::orderBy('start_date', 'desc')->get();
                    $terms = \App\Models\Term::orderBy('start_date', 'desc')->get();
                @endphp
                <div>
                    <label class="form-label" for="current_academic_year_id">Current Academic Year</label>
                    <select class="form-input-custom" name="settings[current_academic_year_id]" id="current_academic_year_id">
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" 
                                    {{ old('settings.current_academic_year_id', \App\Models\Setting::get('current_academic_year_id')) == $year->id ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->is_current ? '(Active)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="current_term_id">Current Term</label>
                    <select class="form-input-custom" name="settings[current_term_id]" id="current_term_id">
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" 
                                    {{ old('settings.current_term_id', \App\Models\Setting::get('current_term_id')) == $term->id ? 'selected' : '' }}>
                                {{ $term->academicYear->name }} - {{ $term->name }} {{ $term->is_current ? '(Active)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    Update Active Term
                </button>
            </div>
        </div>

        <!-- Tab: SMS Gateway -->
        <div x-show="activeTab === 'sms'" class="card p-6 md:p-8 space-y-6">
            <h3 class="font-title-lg text-title-lg text-on-surface font-semibold mb-4">Arkesel SMS Gateway Integration</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="form-label" for="sms_provider">SMS Service Provider</label>
                    <input class="form-input-custom bg-surface-container-low" type="text" name="settings[sms_provider]" id="sms_provider" 
                           value="{{ old('settings.sms_provider', \App\Models\Setting::get('sms_provider', 'arkesel')) }}" readonly>
                </div>
                <div>
                    <label class="form-label" for="sms_sender_id">SMS Sender ID</label>
                    <input class="form-input-custom" type="text" name="settings[sms_sender_id]" id="sms_sender_id" max="11" 
                           value="{{ old('settings.sms_sender_id', \App\Models\Setting::get('sms_sender_id', 'HANARA')) }}">
                </div>
                <div class="md:col-span-2">
                    <label class="form-label" for="sms_api_key">Arkesel API Key</label>
                    <input class="form-input-custom font-mono" type="password" name="settings[sms_api_key]" id="sms_api_key" placeholder="••••••••••••••••••••••••••••••••"
                           value="{{ old('settings.sms_api_key', \App\Models\Setting::get('sms_api_key')) }}">
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    Save Integration Credentials
                </button>
            </div>
        </div>
    </form>

    <!-- Tab: Audit Trail (ReadOnly) -->
    <div x-show="activeTab === 'audit'" class="card p-6 md:p-8 space-y-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">System Audit Trail</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Immutable logs recording configuration updates and changes.</p>
            </div>
        </div>

        @php
            $auditLogs = \App\Models\AuditLog::with('user')->orderBy('created_at', 'desc')->take(10)->get();
        @endphp

        <div class="overflow-x-auto border border-outline-variant rounded-xl">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface font-label-md text-label-md border-b border-outline-variant">
                        <th class="p-4">User</th>
                        <th class="p-4">Action</th>
                        <th class="p-4">Target Setting</th>
                        <th class="p-4">Change Log</th>
                        <th class="p-4">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant font-body-sm text-body-sm">
                    @forelse ($auditLogs as $log)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="p-4 font-medium">{{ $log->user ? $log->user->name : 'System' }}</td>
                            <td class="p-4">
                                <span class="badge badge-info uppercase">{{ $log->action }}</span>
                            </td>
                            <td class="p-4 font-mono text-xs">{{ basename(str_replace('\\', '/', $log->auditable_type)) }} (#{{ $log->auditable_id }})</td>
                            <td class="p-4">
                                @if ($log->old_values && $log->new_values)
                                    <div class="flex flex-col gap-1">
                                        @foreach($log->new_values as $k => $val)
                                            <span class="text-xs">
                                                Updated <span class="font-semibold">{{ $k }}</span>: 
                                                <span class="text-error line-through">{{ is_scalar($log->old_values[$k] ?? '') ? $log->old_values[$k] : json_encode($log->old_values[$k]) }}</span> 
                                                → 
                                                <span class="text-success">{{ is_scalar($val) ? $val : json_encode($val) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-on-surface-variant text-xs">No detail recorded</span>
                                @endif
                            </td>
                            <td class="p-4 text-on-surface-variant">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-on-surface-variant font-body-md">
                                No audit log records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
