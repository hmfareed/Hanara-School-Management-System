@extends('layouts.app')

@section('title', 'Bulk Student Import')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('students.index') }}" class="btn-ghost !p-2 rounded-full hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined text-[24px]">arrow_back</span>
        </a>
        <div>
            <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Bulk Student Import</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Import multiple students and automatically link guardians using CSV templates.</p>
        </div>
    </div>

    <!-- Error Logs (if any) -->
    @if ($errors->any())
        <div class="card border-l-4 border-error bg-error-container/10 p-6 space-y-3">
            <div class="flex items-center gap-2 text-error font-semibold font-title-medium text-title-medium">
                <span class="material-symbols-outlined text-[24px]">error_outline</span>
                Import Validation Failed
            </div>
            <p class="font-body-sm text-body-sm text-on-surface-variant">No records were imported. Please fix the errors listed below in your CSV file and try again:</p>
            <ul class="list-disc list-inside space-y-1 font-body-sm text-body-sm text-error overflow-y-auto max-h-60 p-2 bg-surface rounded-lg border border-outline-variant">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Upload Form Column -->
        <div class="md:col-span-2 space-y-6">
            <div class="card p-6 space-y-6">
                <h3 class="font-title-lg text-title-lg font-semibold text-on-surface">Upload Roster</h3>
                <form action="{{ route('students.import.post') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- File Dropzone/Input -->
                    <div class="border-2 border-dashed border-outline-variant rounded-2xl p-8 text-center bg-surface-container-lowest hover:border-primary/50 transition-colors cursor-pointer relative"
                         onclick="document.getElementById('roster_file').click()">
                        <span class="material-symbols-outlined text-outline text-5xl mb-4">upload_file</span>
                        <div class="font-body-md text-body-md text-on-surface font-medium mb-1">Click to select CSV roster file</div>
                        <div class="font-label-md text-label-md text-on-surface-variant mb-4">Max size: 5 MB (CSV files only)</div>
                        
                        <input type="file" name="roster_file" id="roster_file" class="hidden" accept=".csv,text/csv,text/plain" required
                               onchange="document.getElementById('file-chosen-name').innerText = this.files[0] ? this.files[0].name : ''">
                        
                        <div class="text-xs font-mono font-medium text-primary mt-2" id="file-chosen-name"></div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-outline-variant pt-4">
                        <a href="{{ route('students.index') }}" class="btn-ghost !py-2 px-4 text-xs">Cancel</a>
                        <button type="submit" class="btn-primary !py-2 px-6 text-xs flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">file_upload</span>
                            Process File
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions Column -->
        <div class="space-y-6">
            <div class="card p-6 bg-surface-container-low border border-outline-variant space-y-4">
                <h3 class="font-title-medium text-title-medium font-semibold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-primary">info</span>
                    Requirements
                </h3>
                <ul class="space-y-3 font-body-sm text-body-sm text-on-surface-variant">
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] text-success mt-0.5">check_circle</span>
                        <span>First row must contain the exact column headers.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] text-success mt-0.5">check_circle</span>
                        <span><strong>Class</strong> column must match active class names (e.g. <code>P1</code>, <code>JHS1</code>).</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] text-success mt-0.5">check_circle</span>
                        <span><strong>Gender</strong> must be lowercase: <code>male</code> or <code>female</code>.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] text-success mt-0.5">check_circle</span>
                        <span><strong>Date of Birth</strong> should be format <code>YYYY-MM-DD</code>.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] text-success mt-0.5">check_circle</span>
                        <span><strong>Guardian Phone</strong>: Sibling links are created by matching this phone number.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- CSV Headers Mapping reference -->
    <div class="card p-6 space-y-4">
        <h3 class="font-title-medium text-title-medium font-semibold text-on-surface">Required CSV Header Columns</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-outline-variant font-body-sm text-body-sm text-left">
                <thead>
                    <tr class="bg-surface-container text-on-surface font-semibold">
                        <th class="p-3 border border-outline-variant">Column Header</th>
                        <th class="p-3 border border-outline-variant">Description</th>
                        <th class="p-3 border border-outline-variant">Example Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    <tr>
                        <td class="p-3 border border-outline-variant font-mono text-xs font-semibold">first_name</td>
                        <td class="p-3 border border-outline-variant">Student's first name</td>
                        <td class="p-3 border border-outline-variant">Kwame</td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-outline-variant font-mono text-xs font-semibold">last_name</td>
                        <td class="p-3 border border-outline-variant">Student's last name / surname</td>
                        <td class="p-3 border border-outline-variant">Mensah</td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-outline-variant font-mono text-xs font-semibold">other_names</td>
                        <td class="p-3 border border-outline-variant">Optional other names (nullable)</td>
                        <td class="p-3 border border-outline-variant">Osei</td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-outline-variant font-mono text-xs font-semibold">date_of_birth</td>
                        <td class="p-3 border border-outline-variant">Date of birth (YYYY-MM-DD)</td>
                        <td class="p-3 border border-outline-variant">2016-04-12</td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-outline-variant font-mono text-xs font-semibold">gender</td>
                        <td class="p-3 border border-outline-variant">Gender (male or female)</td>
                        <td class="p-3 border border-outline-variant">male</td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-outline-variant font-mono text-xs font-semibold">class</td>
                        <td class="p-3 border border-outline-variant">Active School Class Code/Name</td>
                        <td class="p-3 border border-outline-variant">P3</td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-outline-variant font-mono text-xs font-semibold">guardian_first_name</td>
                        <td class="p-3 border border-outline-variant">First name of primary guardian</td>
                        <td class="p-3 border border-outline-variant">Emmanuel</td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-outline-variant font-mono text-xs font-semibold">guardian_last_name</td>
                        <td class="p-3 border border-outline-variant">Last name of primary guardian</td>
                        <td class="p-3 border border-outline-variant">Mensah</td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-outline-variant font-mono text-xs font-semibold">guardian_phone</td>
                        <td class="p-3 border border-outline-variant">Guardian contact phone (Ghana format)</td>
                        <td class="p-3 border border-outline-variant">+233240000000</td>
                    </tr>
                    <tr>
                        <td class="p-3 border border-outline-variant font-mono text-xs font-semibold">guardian_relationship</td>
                        <td class="p-3 border border-outline-variant">Relationship to the student</td>
                        <td class="p-3 border border-outline-variant">Father</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
