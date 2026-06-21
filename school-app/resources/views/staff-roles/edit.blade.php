@extends('layouts.app')

@section('title', 'Edit Staff - ' . $user->name)

@section('content')
<div x-data="staffEditForm()">
    <!-- Header with breadcrumb -->
    <div class="mb-section-gap">
        <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-2">
            <a href="{{ route('staff-roles.index') }}" class="hover:text-primary transition-colors">Staff Roles</a>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-on-surface font-medium">{{ $user->name }}</span>
        </div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background" id="page-title">
            Manage Assignments
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Update role, form class, and subject teaching assignments for <strong>{{ $user->name }}</strong>.
        </p>
    </div>

    <!-- Profile Summary Card -->
    <div class="card p-5 mb-6 border border-outline-variant bg-surface flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-primary-container text-on-primary-container text-lg font-bold flex items-center justify-center border border-primary/10">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="flex-1">
            <h3 class="font-title-lg text-title-lg font-bold text-on-surface">{{ $user->name }}</h3>
            <p class="text-sm text-on-surface-variant">{{ $user->email }}</p>
            @if($user->userable && $user->userable instanceof \App\Models\Staff)
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-xs text-on-surface-variant">Staff ID: <span class="font-mono font-bold">{{ $user->userable->staff_id_number }}</span></span>
                    @php
                        $statusBadge = $user->userable->status === 'active'
                            ? 'bg-success-container text-on-success-container'
                            : 'bg-warning-container text-warning';
                    @endphp
                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold {{ $statusBadge }}">
                        {{ ucfirst($user->userable->status) }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <form action="{{ route('staff-roles.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div class="p-4 bg-error-container text-on-error-container rounded-xl text-body-md">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Section 1: Role Selection -->
        <div class="card p-6 border border-primary/20 bg-surface shadow-level-1">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-primary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-primary-container text-[22px]">admin_panel_settings</span>
                </div>
                <div>
                    <h3 class="font-title-lg text-title-lg font-bold text-on-surface">Role Assignment</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Select the staff member's system role</p>
                </div>
            </div>

            <select name="role" id="role-select" x-model="selectedRole" @change="onRoleChange()"
                    class="block w-full px-4 py-3 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                @foreach($availableRoles as $role)
                    <option value="{{ $role }}" {{ $user->roles->first()?->name === $role ? 'selected' : '' }}>
                        {{ match($role) {
                            'HeadTeacher' => '🎓 Head Teacher / Principal',
                            'ClassTeacher' => '📋 Form Master / Class Teacher',
                            'SubjectTeacher' => '📖 Subject Teacher',
                            'Accounts' => '💰 Accountant / Bursar',
                            'Supervisor' => '👁 Supervisor',
                            default => $role,
                        } }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Section 2: Form Class (only visible for ClassTeacher) -->
        <div x-show="selectedRole === 'ClassTeacher'" x-transition class="card p-6 border border-secondary/20 bg-surface shadow-level-1">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-secondary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-secondary-container text-[22px]">class</span>
                </div>
                <div>
                    <h3 class="font-title-lg text-title-lg font-bold text-on-surface">Form Class Assignment</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Select the class this teacher is form master of</p>
                </div>
            </div>

            <select name="form_class_id" id="form_class_id"
                    class="block w-full px-4 py-3 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                <option value="">-- Select Form Class --</option>
                @foreach($classes as $class)
                    @php
                        $isCurrentFormClass = $user->teacherAssignments
                            ->where('is_form_teacher', true)
                            ->where('class_id', $class->id)
                            ->count() > 0;
                    @endphp
                    <option value="{{ $class->id }}" {{ $isCurrentFormClass ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Section 3: Subject Assignments -->
        <div x-show="selectedRole === 'ClassTeacher' || selectedRole === 'SubjectTeacher'" x-transition
             class="card p-6 border border-tertiary/20 bg-surface shadow-level-1">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-tertiary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-tertiary-container text-[22px]">menu_book</span>
                </div>
                <div>
                    <h3 class="font-title-lg text-title-lg font-bold text-on-surface">Subject Assignments</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Configure which subjects and classes this teacher teaches (all combinations will be assigned)</p>
                </div>
            </div>

            @php
                $assignedClassIds = $user->teacherAssignments->where('is_form_teacher', false)->pluck('class_id')->unique()->toArray();
                $assignedSubjectIds = $user->teacherAssignments->where('is_form_teacher', false)->pluck('subject_id')->unique()->toArray();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Select Classes -->
                <div>
                    <h4 class="font-title-medium text-on-surface font-semibold mb-3">1. Select Class(es)</h4>
                    <div class="space-y-1 max-h-64 overflow-y-auto p-3 border border-outline-variant rounded-xl bg-surface-container-lowest">
                        @foreach($classes as $class)
                            <label class="flex items-center gap-3 p-2 hover:bg-surface-container-low rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" 
                                       {{ in_array($class->id, $assignedClassIds) ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary rounded border-outline-variant focus:ring-primary">
                                <span class="font-body-md text-on-surface">{{ $class->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Select Subjects -->
                <div>
                    <h4 class="font-title-medium text-on-surface font-semibold mb-3">2. Select Subject(s)</h4>
                    <div class="space-y-1 max-h-64 overflow-y-auto p-3 border border-outline-variant rounded-xl bg-surface-container-lowest">
                        @foreach($subjects as $subject)
                            <label class="flex items-center gap-3 p-2 hover:bg-surface-container-low rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" 
                                       {{ in_array($subject->id, $assignedSubjectIds) ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary rounded border-outline-variant focus:ring-primary">
                                <span class="font-body-md text-on-surface">{{ $subject->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('staff-roles.index') }}" class="btn-ghost !py-2.5 px-6 rounded-xl flex items-center gap-2 text-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Back to Staff Roles
            </a>
            <button type="submit"
                    class="btn-primary px-8 py-3 rounded-xl flex items-center gap-2 font-medium text-base shadow-level-1 hover:shadow-level-2 transition-shadow">
                <span class="material-symbols-outlined text-[20px]">save</span>
                Save Changes
            </button>
        </div>
    </form>
</div>

<div class="h-8"></div>

<script>
    function staffEditForm() {
        return {
            selectedRole: '{{ $user->roles->first()?->name ?? "SubjectTeacher" }}',
            onRoleChange() {
                // Toggling logic handled by Tailwind via x-show
            }
        };
    }
</script>
@endsection
