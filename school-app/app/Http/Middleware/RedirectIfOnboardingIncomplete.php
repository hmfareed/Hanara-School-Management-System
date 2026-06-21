<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfOnboardingIncomplete
{
    /**
     * Redirect ClassTeacher/SubjectTeacher users who have NO assignments
     * to the onboarding setup page. Excludes onboarding routes, logout, and
     * password change to prevent redirect loops.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Only applies to ClassTeacher and SubjectTeacher
        if (!$user->hasAnyRole(['ClassTeacher', 'SubjectTeacher'])) {
            return $next($request);
        }

        // Check if staff profile is still pending approval
        if ($user->userable_type === \App\Models\Staff::class && $user->userable?->status === 'pending') {
            // Pending staff shouldn't be logged in — force logout
            auth()->logout();
            return redirect()->route('login')
                ->with('warning', 'Your account is pending approval by the school administration.');
        }

        // Skip onboarding check for allowed routes
        $allowedRoutes = [
            'onboarding.teacher',
            'onboarding.teacher.submit',
            'logout',
            'password.change',
            'password.change.update',
        ];

        if ($request->routeIs(...$allowedRoutes)) {
            return $next($request);
        }

        // If teacher has no assignments, redirect to onboarding
        if ($user->teacherAssignments()->count() === 0) {
            return redirect()->route('onboarding.teacher');
        }

        return $next($request);
    }
}
