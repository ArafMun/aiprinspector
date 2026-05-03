<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        \Log::info('AdminAuth middleware: Processing request', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Check if user is authenticated
        if (!Auth::check()) {
            \Log::warning('AdminAuth middleware: User not authenticated', [
                'url' => $request->fullUrl(),
                'session_id' => session()->getId(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return redirect()->route('login')
                ->with('error', 'You must be logged in to access this page.');
        }

        $user = Auth::user();

        \Log::info('AdminAuth middleware: User authenticated', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
            'is_admin_legacy' => $user->is_admin,
            'roles' => $user->roles->pluck('name')->toArray(),
            'permissions' => $user->getPermissions(),
        ]);

        // Check if user has admin access (legacy is_admin, admin role, or admin permissions)
        $isAdminLegacy = $user->isAdmin();
        $hasDashboardPermission = $user->hasPermission('admin.dashboard');

        \Log::info('AdminAuth middleware: Checking admin access', [
            'user_id' => $user->id,
            'is_admin_legacy' => $isAdminLegacy,
            'has_dashboard_permission' => $hasDashboardPermission,
            'access_granted' => $isAdminLegacy || $hasDashboardPermission,
        ]);

        if (!$isAdminLegacy && !$hasDashboardPermission) {
            \Log::warning('AdminAuth middleware: Admin access denied', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'is_admin_legacy' => $isAdminLegacy,
                'has_dashboard_permission' => $hasDashboardPermission,
                'roles' => $user->roles->pluck('name')->toArray(),
                'permissions' => $user->getPermissions(),
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized - Admin access required'], 403);
            }

            return redirect()->route('login')
                ->with('error', 'You must be an administrator to access this page.');
        }

        \Log::info('AdminAuth middleware: Admin access granted', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'url' => $request->fullUrl(),
        ]);

        return $next($request);
    }
}
