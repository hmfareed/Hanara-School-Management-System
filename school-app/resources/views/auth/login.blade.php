@extends('layouts.guest')

@section('title', 'Login - Hanara Schools')

@section('content')
<div class="flex min-h-screen w-full">
    <!-- Left: Login Form -->
    <div class="flex flex-1 flex-col justify-center px-6 py-12 lg:flex-none lg:w-[480px] xl:w-[560px] bg-surface relative z-10 shadow-[20px_0_25px_-5px_rgba(0,0,0,0.05)]">
        <div class="mx-auto w-full max-w-sm lg:w-[380px]">
            <!-- Brand -->
            <div class="flex flex-col items-center lg:items-start text-center lg:text-left mb-10">
                <div class="h-16 w-16 bg-primary rounded-xl flex items-center justify-center mb-6 shadow-sm" id="login-logo">
                    <span class="text-on-primary font-bold text-3xl">H</span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2" id="login-title">Hanara Schools</h1>
                <p class="font-title-lg text-title-lg text-on-surface-variant font-normal">Admin Portal</p>
            </div>

            <!-- Login Form -->
            <form action="{{ route('login.attempt') }}" class="space-y-6" method="POST" id="login-form">
                @csrf

                @if($errors->any())
                    <div class="p-3 bg-error-container text-on-error-container rounded-xl text-body-md" id="login-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="p-3 bg-success-container text-on-success-container rounded-xl text-body-md" id="login-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="p-3 bg-warning-container text-warning rounded-xl text-body-md" id="login-warning">
                        {{ session('warning') }}
                    </div>
                @endif

                @if(session('status'))
                    <div class="p-3 bg-info-container text-info rounded-xl text-body-md" id="login-status">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Email -->
                <div>
                    <label class="form-label" for="email">Email address</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-outline">
                            <span class="material-symbols-outlined" style="font-size: 20px;">mail</span>
                        </span>
                        <input autocomplete="email"
                               class="block w-full pl-10 pr-3 py-3 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
                               id="email" name="email" placeholder="admin@hanara.edu"
                               required type="email" value="{{ old('email') }}"/>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="form-label" for="password">Password</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-outline">
                            <span class="material-symbols-outlined" style="font-size: 20px;">lock</span>
                        </span>
                        <input autocomplete="current-password"
                               class="block w-full pl-10 pr-3 py-3 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
                               id="password" name="password" placeholder="••••••••"
                               required type="password"/>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center">
                        <input class="h-4 w-4 text-primary focus:ring-primary border-outline-variant rounded bg-surface"
                               id="remember-me" name="remember" type="checkbox"/>
                        <label class="ml-2 block font-body-md text-body-md text-on-surface-variant" for="remember-me">
                            Remember me
                        </label>
                    </div>
                    <div class="text-sm">
                        <a class="font-label-md text-label-md text-primary hover:text-primary-container transition-colors" href="#">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm font-title-lg text-title-lg text-on-secondary-container bg-secondary-container hover:bg-secondary-fixed transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-container"
                            type="submit" id="btn-login">
                        Log In
                    </button>
                </div>

                <div class="text-center mt-6">
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Don't have an account? 
                        <a href="{{ route('register') }}" class="font-semibold text-primary hover:underline">
                            Register here
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Illustration Panel -->
    <div class="hidden lg:flex flex-1 bg-surface-container-low relative items-center justify-center p-12 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-transparent to-surface-variant/30 pointer-events-none"></div>
        <div class="relative w-full max-w-lg flex flex-col items-center justify-center text-center">
            <!-- School illustration placeholder -->
            <div class="w-48 h-48 bg-primary-container rounded-3xl flex items-center justify-center mb-8 shadow-level-1">
                <span class="material-symbols-outlined text-on-primary-container" style="font-size: 96px;">school</span>
            </div>
            <h2 class="font-headline-md text-headline-md text-on-surface mb-3">Welcome to Hanara Schools</h2>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-sm">
                Empowering academic excellence through modern school management.
            </p>
        </div>
    </div>
</div>
@endsection
