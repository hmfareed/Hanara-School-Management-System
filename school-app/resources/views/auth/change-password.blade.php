@extends('layouts.guest')

@section('title', 'Change Password - Hanara Schools')

@section('content')
<div class="flex min-h-screen items-center justify-center bg-background px-4">
    <div class="w-full max-w-md">
        <div class="card p-8">
            <div class="flex flex-col items-center mb-8">
                <div class="h-14 w-14 bg-primary rounded-xl flex items-center justify-center mb-4 shadow-sm">
                    <span class="text-on-primary font-bold text-2xl">H</span>
                </div>
                <h1 class="font-headline-md text-headline-md text-on-surface mb-2">Change Your Password</h1>
                <p class="font-body-md text-body-md text-on-surface-variant text-center">
                    For security, please set a new password before continuing.
                </p>
            </div>

            @if($errors->any())
                <div class="mb-6 p-3 bg-error-container text-on-error-container rounded-xl text-body-md">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.change.update') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="form-label" for="current_password">Current Password</label>
                    <input class="form-input-custom" id="current_password" name="current_password" type="password" required/>
                </div>

                <div>
                    <label class="form-label" for="password">New Password</label>
                    <input class="form-input-custom" id="password" name="password" type="password" required/>
                </div>

                <div>
                    <label class="form-label" for="password_confirmation">Confirm New Password</label>
                    <input class="form-input-custom" id="password_confirmation" name="password_confirmation" type="password" required/>
                </div>

                <button class="w-full btn-accent py-3 text-title-lg rounded-xl" type="submit" id="btn-change-password">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
