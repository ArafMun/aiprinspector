<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        \Log::info('CheckPermission middleware: Processing request', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'required_permission' => $permission,
            'ip' => $request->ip(),
        ]);

        // Check if user is authenticated
        if (!Auth::check()) {
            \Log::warning('CheckPermission middleware: User not authenticated', [
                'url' => $request->fullUrl(),
                'required_permission' => $permission,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return redirect()->route('login')
                ->with('error', 'You must be logged in to access this page.');
        }

        $user = Auth::user();

        \Log::info('CheckPermission middleware: User authenticated', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'required_permission' => $permission,
            'user_permissions' => $user->getPermissions(),
        ]);

        // Check if user has the required permission
        $hasPermission = $user->hasPermission($permission);

        \Log::info('CheckPermission middleware: Permission check result', [
            'user_id' => $user->id,
            'required_permission' => $permission,
            'has_permission' => $hasPermission,
            'user_permissions' => $user->getPermissions(),
        ]);

        if (!$hasPermission) {
            \Log::warning('CheckPermission middleware: Permission denied', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'required_permission' => $permission,
                'user_permissions' => $user->getPermissions(),
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden - Insufficient permissions'], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('error', "You don't have permission to access this page. Required: {$permission}");
        }

        \Log::info('CheckPermission middleware: Permission granted', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'required_permission' => $permission,
            'url' => $request->fullUrl(),
        ]);

        return $next($request);
    }
}
