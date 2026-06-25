<div class="space-y-6">
    <style>
        .table-container::-webkit-scrollbar { height: 8px; width: 8px; }
        .table-container::-webkit-scrollbar-track { background: #f1f0f7; border-radius: 4px; }
        .table-container::-webkit-scrollbar-thumb { background: #c5c5d3; border-radius: 4px; }
        .table-container::-webkit-scrollbar-thumb:hover { background: #757682; }

        .sticky-col-1 { position: sticky; left: 0; z-index: 10; background: inherit; }
        .sticky-col-2 { position: sticky; left: 44px; z-index: 10; background: inherit; }
        
        .grade-row:hover td { background-color: #f4f3fa !important; }
        .grade-row:hover .sticky-col-1,
        .grade-row:hover .sticky-col-2 { background-color: #f4f3fa !important; }

        .score-input-cell {
            width: 52px;
            text-align: center;
            border: 1px solid #c5c5d3;
            border-radius: 6px;
            padding: 3px 2px;
            font-size: 13px;
            color: #1a1b21;
            transition: all 0.15s ease;
        }
        .score-input-cell:focus {
            outline: none;
            border-color: #00236f;
            box-shadow: 0 0 0 3px rgba(0, 35, 111, 0.12);
            background-color: #faf8ff;
        }
        .score-input-cell.is-error {
            border-color: #ba1a1a;
            background-color: #fff5f5;
            color: #ba1a1a;
        }
        .computed-cell {
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }
        
        .pulse-dot {
            animation: pulse-dot 1.4s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
    </style>

    {{-- Filter Bar --}}
    <div class="card p-6">
        <div class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <label class="font-label-md text-on-surface-variant mb-1 block">Class</label>
                <select wire:model.live="selectedClassId" class="input-field w-full">
                    <option value="">Select class...</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->schoolClass->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="font-label-md text-on-surface-variant mb-1 block">Subject</label>
                <select wire:model.live="selectedSubjectId" class="input-field w-full">
                    <option value="">Select subject...</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Score Entry Grid --}}
    @if($selectedClassId && $selectedSubjectId && count($students) > 0)
        <div x-data="gradebookData({
            students: {{ Js::from($students) }},
            components: {{ Js::from($components) }},
            gradeScales: {{ Js::from($gradeScales) }},
            initialScores: @entangle('scores')
        })" x-init="init()" class="space-y-6">

            <!-- Stats Row -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="card p-4 bg-surface hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-widest">Students</span>
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-primary-fixed">
                            <span class="material-symbols-outlined text-primary" style="font-size:18px;">group</span>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-on-surface" x-text="students.length"></p>
                    <p id="statEntered" class="text-xs text-on-surface-variant mt-1">0 scores entered</p>
                </div>
                <div class="card p-4 bg-surface hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-widest">Class Avg</span>
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-secondary-fixed">
                            <span class="material-symbols-outlined text-secondary" style="font-size:18px;">analytics</span>
                        </div>
                    </div>
                    <p id="statAvg" class="text-2xl font-bold text-on-surface">—</p>
                    <p id="statAvgGrade" class="text-xs text-on-surface-variant mt-1">Average final score</p>
                </div>
                <div class="card p-4 bg-surface hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-widest">Highest</span>
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-green-100">
                            <span class="material-symbols-outlined text-green-700" style="font-size:18px;">trending_up</span>
                        </div>
                    </div>
                    <p id="statHighest" class="text-2xl font-bold text-on-surface">—</p>
                    <p id="statHighestName" class="text-xs text-on-surface-variant mt-1 truncate">Top student</p>
                </div>
                <div class="card p-4 bg-surface hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-widest">Pass Rate</span>
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-100">
                            <span class="material-symbols-outlined text-blue-800" style="font-size:18px;">verified</span>
                        </div>
                    </div>
                    <p id="statPass" class="text-2xl font-bold text-on-surface">—</p>
                    <p class="text-xs text-on-surface-variant mt-1">Score &ge; 40%</p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="card p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-widest">Entry Progress</span>
                    <span id="progressLabel" class="text-sm font-semibold text-primary">0 of 0 completed</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-surface-container-high overflow-hidden">
                    <div id="progressFill" class="h-full rounded-full transition-all duration-500 bg-gradient-to-r from-primary to-primary-container" style="width:0%;"></div>
                </div>
            </div>

            <!-- Legend & Auto-save Status -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-on-surface-variant">
                <div class="flex flex-wrap gap-3">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-primary-container inline-block"></span>CA (30%)</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-secondary-fixed inline-block"></span>Exam (70%)</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-surface-container-highest inline-block"></span>Results</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined pulse-dot text-primary" style="font-size:14px;">fiber_manual_record</span>
                    <span>Scores auto-save on blur</span>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="card overflow-hidden">
                <div class="table-container w-full overflow-x-auto">
                    <table class="w-full text-left border-collapse" style="min-width: 1200px;">
                        <thead>
                            <!-- Group Headers Row -->
                            <tr class="border-b border-outline-variant bg-surface-container-low text-xs uppercase tracking-widest font-semibold">
                                <th class="sticky-col-1 py-3 px-4 border-r border-outline-variant text-center w-12" style="background:#eeedf4;"></th>
                                <th class="sticky-col-2 py-3 px-4 border-r border-outline-variant w-48" style="background:#eeedf4;"></th>
                                <!-- CA Group -->
                                <th colspan="9" class="text-center py-2 px-3 bg-primary-container text-on-primary-container border-r border-outline-variant/30">
                                    Continuous Assessment (CA) &nbsp;·&nbsp; /70 marks
                                </th>
                                <!-- CA Result -->
                                <th colspan="2" class="text-center py-2 px-3 bg-blue-100 text-blue-900 border-r border-outline-variant/30">
                                    CA Summary
                                </th>
                                <!-- Exam Group -->
                                <th class="text-center py-2 px-3 bg-secondary-fixed text-on-secondary-fixed-variant border-r border-outline-variant/30">
                                    Exam &nbsp;·&nbsp; /100
                               </th>
                                <th class="text-center py-2 px-3 bg-secondary-fixed-dim text-on-secondary-fixed border-r border-outline-variant/30">
                                    Exam Weighted
                                </th>
                                <!-- Results Group -->
                                <th colspan="2" class="text-center py-2 px-3 bg-surface-container-highest text-on-surface">
                                    Final Results
                                </th>
                            </tr>
                            <!-- Column Headers Row -->
                            <tr class="border-b border-outline-variant bg-surface-container-lowest text-[11px] font-bold text-on-surface-variant">
                                <th class="sticky-col-1 p-3 text-center border-r border-outline-variant w-12" style="background:#faf8ff;">#</th>
                                <th class="sticky-col-2 p-3 border-r border-outline-variant w-48" style="background:#faf8ff;">Student Name</th>
                                <!-- CA Columns -->
                                @foreach($components->slice(0, 9) as $comp)
                                    <th class="p-2 text-center border-r border-outline-variant/30 w-16" style="background: rgba(220, 225, 255, 0.15);">
                                        {{ $comp->name }}<br/><span class="text-[10px] font-normal text-outline">/{{ $comp->max_score }}</span>
                                    </th>
                                @endforeach
                                <!-- CA Results -->
                                <th class="p-2 text-center border-r border-outline-variant/30 w-16 bg-blue-50/50">Raw<br/><span class="text-[10px] font-normal text-outline">/70</span></th>
                                <th class="p-2 text-center border-r border-outline-variant w-16 bg-blue-50/80 text-primary font-bold">Score<br/><span class="text-[10px] font-normal text-outline">/30</span></th>
                                <!-- Exam -->
                                <th class="p-2 text-center border-r border-outline-variant/30 w-20" style="background: rgba(255, 221, 184, 0.15);">Score<br/><span class="text-[10px] font-normal text-outline">/100</span></th>
                                <th class="p-2 text-center border-r border-outline-variant w-16 bg-amber-50 text-secondary font-bold">Weighted<br/><span class="text-[10px] font-normal text-outline">/70</span></th>
                                <!-- Final Results -->
                                <th class="p-2 text-center border-r border-outline-variant/30 w-20 bg-surface-variant/40 text-on-surface font-bold">Total<br/><span class="text-[10px] font-normal text-outline">/100</span></th>
                                <th class="p-2 text-center bg-surface-variant/60 text-on-surface font-bold w-16">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <template x-for="(student, index) in students" :key="student.id">
                                <tr class="grade-row text-xs hover:bg-surface-container-low transition-colors" style="background:#ffffff;">
                                    <!-- Index -->
                                    <td class="sticky-col-1 p-3 text-center border-r border-outline-variant font-medium text-outline" style="background:#ffffff;" x-text="index + 1"></td>
                                    <!-- Name -->
                                    <td class="sticky-col-2 p-3 border-r border-outline-variant" style="background:#ffffff;">
                                        <div class="font-medium text-on-surface truncate" style="max-width: 180px;" x-text="student.last_name + ', ' + student.first_name"></div>
                                        <div class="text-[10px] text-outline" x-text="student.student_id_number"></div>
                                    </td>
                                    <!-- CA Inputs -->
                                    <template x-for="comp in components.slice(0, 9)" :key="comp.id">
                                        <td class="p-2 text-center border-r border-outline-variant/20" style="background: rgba(220, 225, 255, 0.05);">
                                            <input type="number"
                                                   step="0.5"
                                                   min="0"
                                                   :max="comp.max_score"
                                                   x-model.number="scores[student.id][comp.id]"
                                                   @blur="$wire.saveScore(student.id, comp.id)"
                                                   :class="{'is-error': parseFloat(scores[student.id][comp.id]) > comp.max_score || parseFloat(scores[student.id][comp.id]) < 0}"
                                                   class="score-input-cell font-medium"
                                                   placeholder="—">
                                        </td>
                                    </template>
                                    <!-- CA Raw -->
                                    <td class="p-2 text-center border-r border-outline-variant/30 font-semibold bg-blue-50/30 text-slate-700" x-text="computed[student.id]?.caRaw"></td>
                                    <!-- CA Score /30 -->
                                    <td class="p-2 text-center border-r border-outline-variant font-bold bg-blue-50/60 text-primary" x-text="computed[student.id]?.caScore"></td>
                                    <!-- Exam Input -->
                                    <td class="p-2 text-center border-r border-outline-variant/20" style="background: rgba(255, 221, 184, 0.05);">
                                        <input type="number"
                                               step="0.5"
                                               min="0"
                                               max="100"
                                               x-model.number="scores[student.id][components[9].id]"
                                               @blur="$wire.saveScore(student.id, components[9].id)"
                                               :class="{'is-error': parseFloat(scores[student.id][components[9].id]) > 100 || parseFloat(scores[student.id][components[9].id]) < 0}"
                                               class="score-input-cell font-medium"
                                               placeholder="—">
                                    </td>
                                    <!-- Exam Weighted /70 -->
                                    <td class="p-2 text-center border-r border-outline-variant font-bold bg-amber-50/60 text-secondary" x-text="computed[student.id]?.examScore"></td>
                                    <!-- Final Score -->
                                    <td class="p-2 text-center border-r border-outline-variant/30 font-bold bg-surface-variant/20 text-on-surface" x-text="computed[student.id]?.finalScore"></td>
                                    <!-- Grade -->
                                    <td class="p-2 text-center bg-surface-variant/40" style="padding: 6px;">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-bold border"
                                              :style="{
                                                  backgroundColor: computed[student.id]?.gradeBg,
                                                  color: computed[student.id]?.gradeFg,
                                                  borderColor: computed[student.id]?.gradeBorder
                                              }"
                                              style="min-width: 36px;"
                                              x-text="computed[student.id]?.grade">
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <script>
            function gradebookData(config) {
                return {
                    students: config.students,
                    components: config.components,
                    gradeScales: config.gradeScales,
                    scores: config.initialScores,
                    computed: {},

                    init() {
                        this.ensureScoresStructure();
                        this.$watch('scores', () => {
                            this.ensureScoresStructure();
                            this.calculateAll();
                        }, { deep: true });
                        this.calculateAll();
                    },

                    ensureScoresStructure() {
                        if (!this.scores) this.scores = {};
                        this.students.forEach(student => {
                            if (!this.scores[student.id]) {
                                this.scores[student.id] = {};
                            }
                            this.components.forEach(comp => {
                                if (this.scores[student.id][comp.id] === undefined) {
                                    this.scores[student.id][comp.id] = '';
                                }
                            });
                        });
                    },

                    calculateAll() {
                        let totalStudents = this.students.length;
                        if (totalStudents === 0) return;

                        let completedCount = 0;
                        let finalScoresSum = 0;
                        let passCount = 0;
                        let highestScore = -1;
                        let highestStudentName = 'N/A';

                        this.students.forEach(student => {
                            let caRaw = 0;
                            let caComplete = true;

                            // Loop through CA components (indices 0 to 8)
                            this.components.slice(0, 9).forEach(comp => {
                                let val = this.scores[student.id]?.[comp.id];
                                if (val === '' || val === null || val === undefined) {
                                    caComplete = false;
                                } else {
                                    caRaw += Math.min(parseFloat(val), comp.max_score);
                                }
                            });

                            let examRaw = this.scores[student.id]?.[this.components[9].id];
                            let hasExam = !(examRaw === '' || examRaw === null || examRaw === undefined);

                            // CA Score out of 30
                            let caScore = caComplete ? (caRaw / 70) * 30 : null;
                            // Exam Score out of 70
                            let examScore = hasExam ? (Math.min(parseFloat(examRaw), 100) / 100) * 70 : null;
                            // Final Score out of 100
                            let finalScore = (caScore !== null && examScore !== null) ? (caScore + examScore) : null;

                            let gradeInfo = this.getGrade(finalScore);

                            this.computed[student.id] = {
                                caRaw: caComplete ? caRaw.toFixed(1) : '—',
                                caScore: caScore !== null ? caScore.toFixed(1) : '—',
                                examScore: examScore !== null ? examScore.toFixed(1) : '—',
                                finalScore: finalScore !== null ? finalScore.toFixed(1) : '—',
                                grade: gradeInfo ? gradeInfo.grade : '—',
                                gradeBg: gradeInfo ? gradeInfo.bg : '#e9e7ef',
                                gradeFg: gradeInfo ? gradeInfo.fg : '#444651',
                                gradeBorder: gradeInfo ? gradeInfo.border : '#c5c5d3',
                            };

                            if (finalScore !== null) {
                                completedCount++;
                                finalScoresSum += finalScore;
                                if (finalScore >= 40) passCount++;
                                if (finalScore > highestScore) {
                                    highestScore = finalScore;
                                    highestStudentName = student.first_name + ' ' + student.last_name;
                                }
                            }
                        });

                        // Update DOM elements for progress and statistics
                        let progressPct = (completedCount / totalStudents) * 100;
                        let fillEl = document.getElementById('progressFill');
                        let labelEl = document.getElementById('progressLabel');
                        let enteredEl = document.getElementById('statEntered');

                        if (fillEl) fillEl.style.width = progressPct + '%';
                        if (labelEl) labelEl.textContent = `${completedCount} of ${totalStudents} completed`;
                        if (enteredEl) enteredEl.textContent = `${completedCount} of ${totalStudents} fully scored`;

                        let avgEl = document.getElementById('statAvg');
                        let avgGradeEl = document.getElementById('statAvgGrade');
                        let highestEl = document.getElementById('statHighest');
                        let highestNameEl = document.getElementById('statHighestName');
                        let passEl = document.getElementById('statPass');

                        if (completedCount > 0) {
                            let avg = finalScoresSum / completedCount;
                            let avgGrade = this.getGrade(avg);
                            if (avgEl) avgEl.textContent = avg.toFixed(1) + '%';
                            if (avgGradeEl) avgGradeEl.textContent = avgGrade ? `Grade: ${avgGrade.grade}` : '—';
                            if (highestEl) highestEl.textContent = highestScore.toFixed(1) + '%';
                            if (highestNameEl) highestNameEl.textContent = highestStudentName;
                            if (passEl) passEl.textContent = Math.round((passCount / completedCount) * 100) + '%';
                        } else {
                            if (avgEl) avgEl.textContent = '—';
                            if (avgGradeEl) avgGradeEl.textContent = 'No data yet';
                            if (highestEl) highestEl.textContent = '—';
                            if (highestNameEl) highestNameEl.textContent = 'No data yet';
                            if (passEl) passEl.textContent = '—';
                        }
                    },

                    getGrade(score) {
                        if (score === null || score === undefined || isNaN(score)) return null;
                        let match = this.gradeScales.find(scale => score >= scale.min_score && score <= scale.max_score);
                        if (match) {
                            let bg = '#E8F5E9', fg = '#1B5E20', border = '#C8E6C9';
                            let g = match.grade;
                            if (g === 'F' || g === '9' || g === 'Beginning') {
                                bg = '#FFDAD6'; fg = '#93000a'; border = '#FFB4AB';
                            } else if (g === 'E' || g === '8' || g === 'Developing') {
                                bg = '#FFF3E0'; fg = '#BF360C'; border = '#FFE0B2';
                            } else if (g === 'D' || g === '7') {
                                bg = '#FFF8E1'; fg = '#F57F17'; border = '#FFECB3';
                            } else if (g === 'C' || g === '6' || g === '5' || g === '4') {
                                bg = '#E0F2F1'; fg = '#004D40'; border = '#B2DFDB';
                            } else if (g === 'B' || g === '3' || g === '2' || g === 'Proficient') {
                                bg = '#E3F2FD'; fg = '#0D47A1'; border = '#BBDEFB';
                            }
                            return { grade: g, bg, fg, border };
                        }
                        return null;
                    }
                };
            }
        </script>

    @elseif($selectedClassId && $selectedSubjectId)
        <div class="card p-12 text-center bg-surface">
            <span class="material-symbols-outlined text-outline text-[48px] mb-4">group_off</span>
            <p class="font-body-lg text-on-surface-variant">No students enrolled in this class.</p>
        </div>
    @else
        <div class="card p-12 text-center bg-surface">
            <span class="material-symbols-outlined text-outline text-[48px] mb-4">edit_note</span>
            <p class="font-body-lg text-on-surface-variant">Select a class and subject to start entering grades.</p>
        </div>
    @endif

    {{-- Saving indicator --}}
    <div wire:loading class="fixed bottom-4 right-4 bg-primary text-on-primary px-4 py-2 rounded-full shadow-lg font-label-md flex items-center gap-2 z-50">
        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        Saving grades...
    </div>
</div>
