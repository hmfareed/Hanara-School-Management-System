<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Dashboard') - Hanara Schools</title>
    <meta name="description" content="Hanara Schools Management System">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-background text-on-background font-body-md h-screen flex overflow-hidden" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 z-40 md:hidden"
         @click="sidebarOpen = false">
    </div>

    <!-- Sidebar Navigation -->
    <nav :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
         class="fixed left-0 top-0 h-screen w-64 border-r border-neutral-800 bg-[#121212] flex-col py-6 px-4 z-50 transition-transform duration-300 md:translate-x-0 md:flex"
         id="sidebar-nav">
        <!-- Brand -->
        <div class="mb-8 px-2 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center text-on-primary font-bold" id="brand-logo">H</div>
            <div>
                <h1 class="font-title-lg text-title-lg font-bold text-white">Hanara Schools</h1>
                <p class="font-label-md text-label-md text-neutral-400">Admin Portal</p>
            </div>
        </div>

        <!-- New Admission CTA -->
        @can('create', App\Models\Student::class)
        <a href="{{ route('admissions.apply') }}" class="mb-6 w-full py-2.5 px-4 bg-secondary-container text-on-secondary-container font-label-md text-label-md rounded-lg flex items-center justify-center gap-2 hover:opacity-90 transition-opacity shadow-level-1" id="btn-new-admission">
            <span class="material-symbols-outlined text-[20px]">add</span>
            New Admission
        </a>
        @endcan

        <!-- Nav Items -->
        <div class="flex-1 flex flex-col gap-1 overflow-y-auto">
            @php
                $user = auth()->user();
                $isParent = $user->hasRole('Parent');
                $isStudent = $user->hasRole('Student');

                if ($isParent) {
                    $navItems = [
                        ['route' => 'dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard', 'active' => request()->routeIs('dashboard.*')],
                        ['route' => 'communication.announcements.index', 'icon' => 'campaign', 'label' => 'Announcements', 'active' => request()->routeIs('communication.*')],
                    ];
                } elseif ($isStudent) {
                    $navItems = [
                        ['route' => 'dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard', 'active' => request()->routeIs('dashboard.*')],
                    ];
                } else {
                    // Build sidebar dynamically per role
                    $navItems = [
                        ['route' => 'dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard', 'active' => request()->routeIs('dashboard.*')],
                    ];

                    // Students: all staff roles except pure Accountant (who only sees billing-related student data via Finance)
                    if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor', 'ClassTeacher', 'SubjectTeacher', 'FrontDesk', 'Accounts'])) {
                        $navItems[] = ['route' => 'students.index', 'icon' => 'group', 'label' => 'Students', 'active' => request()->routeIs('students.*')];
                    }

                    // Admissions: FrontDesk, HeadTeacher, Proprietor, Supervisor
                    if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor', 'FrontDesk'])) {
                        $navItems[] = ['route' => 'admissions.index', 'icon' => 'how_to_reg', 'label' => 'Admissions', 'active' => request()->routeIs('admissions.*')];
                    }

                    // Attendance: ClassTeacher, HeadTeacher, Proprietor, Supervisor
                    if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor', 'ClassTeacher'])) {
                        $navItems[] = ['route' => 'attendance.mark', 'icon' => 'calendar_today', 'label' => 'Attendance', 'active' => request()->routeIs('attendance.*')];
                    }

                    // Academics: ClassTeacher, SubjectTeacher, HeadTeacher, Proprietor, Supervisor
                    if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor', 'ClassTeacher', 'SubjectTeacher'])) {
                        $navItems[] = ['route' => 'academics.gradebook', 'icon' => 'school', 'label' => 'Academics', 'active' => request()->routeIs('academics.*')];
                    }

                    // Finance: Accounts, Proprietor, Supervisor
                    if ($user->hasAnyRole(['Proprietor', 'Accounts', 'Supervisor'])) {
                        $navItems[] = ['route' => 'billing.invoices', 'icon' => 'payments', 'label' => 'Finance', 'active' => request()->routeIs('billing.*')];
                    }

                    // Communication: HeadTeacher, Proprietor, Supervisor, ClassTeacher, SubjectTeacher
                    if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor', 'ClassTeacher', 'SubjectTeacher'])) {
                        $navItems[] = ['route' => 'communication.announcements.index', 'icon' => 'chat', 'label' => 'Communication', 'active' => request()->routeIs('communication.*')];
                    }

                    // Staff: HeadTeacher, Proprietor, Supervisor
                    if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
                        $navItems[] = ['route' => 'staff.index', 'icon' => 'badge', 'label' => 'Staff', 'active' => request()->routeIs('staff.*') && !request()->routeIs('admin.*')];
                    }

                    // My Leaves (for all staff)
                    if ($user->userable_type === 'App\Models\Staff') {
                        $navItems[] = ['route' => 'staff.leaves.index', 'icon' => 'time_to_leave', 'label' => 'My Leaves', 'active' => request()->routeIs('staff.leaves.*')];
                    }

                    // Admin Leaves Approval & Staff Attendance Monitor
                    if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
                        $navItems[] = ['route' => 'admin.staff-attendance.index', 'icon' => 'co_present', 'label' => 'Staff Attendance', 'active' => request()->routeIs('admin.staff-attendance.*')];
                    }
                    if ($user->hasAnyRole(['Proprietor', 'HeadTeacher'])) {
                        $navItems[] = ['route' => 'admin.leaves.index', 'icon' => 'pending_actions', 'label' => 'Leave Requests', 'active' => request()->routeIs('admin.leaves.*')];
                    }

                    // Staff Roles & Waitlist: Proprietor & HeadTeacher only
                    if ($user->hasAnyRole(['Proprietor', 'HeadTeacher'])) {
                        $pendingStaffCount = \App\Models\User::whereHas('roles', function ($q) {
                            $q->whereIn('name', ['HeadTeacher', 'ClassTeacher', 'SubjectTeacher', 'Accounts', 'Supervisor']);
                        })->whereHasMorph('userable', [\App\Models\Staff::class], function ($q) {
                            $q->where('status', 'pending');
                        })->count();

                        $navItems[] = ['route' => 'staff-roles.index', 'icon' => 'admin_panel_settings', 'label' => 'Staff Roles', 'active' => request()->routeIs('staff-roles.*'), 'badge' => $pendingStaffCount];
                    }

                    // Settings: Proprietor only
                    if ($user->hasRole('Proprietor')) {
                        $navItems[] = ['route' => 'settings.index', 'icon' => 'settings', 'label' => 'Settings', 'active' => request()->routeIs('settings.*')];
                    }
                }
            @endphp

            @foreach($navItems as $item)
                @if($item['active'])
                    <a class="nav-item-active" href="{{ $item['route'] !== '#' ? route($item['route']) : '#' }}" id="nav-{{ Str::slug($item['label']) }}">
                        <span class="material-symbols-outlined fill text-[20px]">{{ $item['icon'] }}</span>
                        <span class="font-body-md text-body-md">{{ $item['label'] }}</span>
                        @if(!empty($item['badge']) && $item['badge'] > 0)
                            <span class="ml-auto bg-error text-on-error text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @else
                    <a class="nav-item" href="{{ $item['route'] !== '#' ? route($item['route']) : '#' }}" id="nav-{{ Str::slug($item['label']) }}">
                        <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
                        <span class="font-body-md text-body-md">{{ $item['label'] }}</span>
                        @if(!empty($item['badge']) && $item['badge'] > 0)
                            <span class="ml-auto bg-error text-on-error text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>

        <!-- Bottom Section -->
        <div class="mt-auto pt-4 border-t border-neutral-800 flex flex-col gap-1">
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-neutral-300 hover:bg-white/10 hover:text-white transition-colors" href="#">
                <span class="material-symbols-outlined text-[20px]">event_note</span>
                <span class="font-body-md text-body-md">Academic Term</span>
            </a>
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-neutral-300 hover:bg-white/10 hover:text-white transition-colors" href="#">
                <span class="material-symbols-outlined text-[20px]">account_circle</span>
                <span class="font-body-md text-body-md">Profile</span>
            </a>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col md:ml-64 w-full bg-background min-h-screen">
        <!-- Top App Bar -->
        <header class="bg-surface border-b border-outline-variant sticky top-0 z-40" id="top-app-bar">
            <div class="flex justify-between items-center w-full px-gutter py-stack-md h-[72px]">
                <div class="flex items-center gap-4 flex-1">
                    <!-- Mobile Menu Button -->
                    <button class="md:hidden text-on-surface-variant hover:bg-surface-container rounded-full p-2 transition-colors"
                            @click="sidebarOpen = !sidebarOpen" id="btn-mobile-menu">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <!-- Search Bar -->
                    <div class="hidden md:flex relative max-w-md w-full">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
                        <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                               placeholder="Search students, staff, or tasks..." type="text" id="search-global"/>
                    </div>
                    <!-- Mobile Brand -->
                    <h2 class="md:hidden font-headline-md text-headline-md font-semibold text-primary">Hanara</h2>
                </div>
                <div class="flex items-center gap-2 md:gap-4">
                    <button class="text-on-surface-variant hover:bg-surface-container rounded-full p-2 transition-colors relative" id="btn-notifications">
                        <span class="material-symbols-outlined">notifications</span>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
                    </button>
                    @if(auth()->user()->userable_type === 'App\Models\Staff' && auth()->user()->userable)
                        @php
                            $todayAttendance = \App\Models\StaffAttendance::where('staff_id', auth()->user()->userable_id)
                                ->whereDate('date', \Carbon\Carbon::today()->toDateString())
                                ->first();
                        @endphp
                        <div class="flex items-center gap-2 mr-2">
                            @if(!$todayAttendance)
                                <form method="POST" action="{{ route('staff.clock-in') }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-success hover:bg-success/90 text-white rounded-lg text-xs font-semibold flex items-center gap-1 transition-all" id="btn-clock-in">
                                        <span class="material-symbols-outlined text-[16px]">login</span>
                                        Clock In
                                    </button>
                                </form>
                            @elseif(!$todayAttendance->clock_out)
                                <form method="POST" action="{{ route('staff.clock-out') }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-error hover:bg-error/90 text-white rounded-lg text-xs font-semibold flex items-center gap-1 transition-all" id="btn-clock-out">
                                        <span class="material-symbols-outlined text-[16px]">logout</span>
                                        Clock Out
                                    </button>
                                </form>
                            @else
                                <span class="px-3 py-1.5 bg-surface-container-high text-on-surface-variant rounded-lg text-xs font-medium flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-success">check_circle</span>
                                    Clocked Out
                                </span>
                            @endif
                        </div>
                    @endif

                    <!-- User Avatar & Logout -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="w-8 h-8 rounded-full bg-primary-container border border-outline-variant overflow-hidden cursor-pointer flex items-center justify-center text-on-primary-container text-sm font-bold" id="btn-user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </button>
                        <div x-show="open" @click.away="open = false"
                             x-transition
                             class="absolute right-0 mt-2 w-48 bg-surface-container-lowest rounded-xl shadow-level-2 border border-outline-variant py-2 z-50">
                            <div class="px-4 py-2 border-b border-outline-variant">
                                <p class="font-body-md text-body-md font-medium text-on-surface">{{ auth()->user()->name }}</p>
                                <p class="font-label-md text-label-md text-on-surface-variant">{{ auth()->user()->roles->first()?->name }}</p>
                                @if(auth()->user()->userable_type === 'App\Models\Staff' && auth()->user()->userable && auth()->user()->userable->personal_code)
                                    <p class="font-label-md text-label-md text-primary font-semibold mt-1">Staff Code: {{ auth()->user()->userable->personal_code }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-body-md text-error hover:bg-error-container/50 transition-colors">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mx-4 mt-4 p-4 bg-success-container text-on-success-container rounded-xl flex items-center gap-3" id="flash-success">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="mx-4 mt-4 p-4 bg-warning-container text-warning rounded-xl flex items-center gap-3" id="flash-warning">
                <span class="material-symbols-outlined">warning</span>
                {{ session('warning') }}
            </div>
        @endif

        <!-- Main Canvas -->
        <main class="flex-1 overflow-y-auto p-4 md:p-container-margin" id="main-content">
            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>
</html>
