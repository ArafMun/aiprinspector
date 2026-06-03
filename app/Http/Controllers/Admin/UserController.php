<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'role' => $request->get('role'),
            'search' => $request->get('search'),
        ];

        $users = $this->userService->getFilteredUsers($filters);
        $roles = $this->userService->getActiveRoles();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = $this->userService->getActiveRoles();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
            'is_admin' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->userService->createUser([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'roles' => $request->roles,
            'is_admin' => $request->boolean('is_admin', false),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roles = $this->userService->getActiveRoles();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $filteredRequest = $request->all();
        if (isset($filteredRequest['roles']) && is_array($filteredRequest['roles'])) {
            $filteredRequest['roles'] = array_filter($filteredRequest['roles'], function ($value) {
                return $value !== '' && $value !== null;
            });
            $filteredRequest['roles'] = array_values($filteredRequest['roles']);
        }

        $validator = Validator::make($filteredRequest, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
            'is_admin' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->userService->updateUser($user, [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'roles' => $filteredRequest['roles'],
            'is_admin' => $request->boolean('is_admin', false),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (! $this->userService->deleteUser($user, auth()->id())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
