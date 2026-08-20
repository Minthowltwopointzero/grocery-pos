<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'in:admin,cashier'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        AuditLogger::log('user_created', "User account created: {$user->name} ({$user->username}) — role: {$user->role}");

        return redirect()->route('users.index')->with('success', 'Account created successfully.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'role' => ['required', 'in:admin,cashier'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        // Safety: don't let an admin lock themselves out by deactivating
        // their own account or demoting themselves from admin.
        if ($user->id === $request->user()->id) {
            $validated['is_active'] = true;
            $validated['role'] = 'admin';
        } else {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->role = $validated['role'];
        $user->is_active = $validated['is_active'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        AuditLogger::log('user_updated', "User account updated: {$user->name} ({$user->username}) — role: {$user->role}, active: " . ($user->is_active ? 'yes' : 'no'));

        return redirect()->route('users.index')->with('success', 'Account updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account while logged in.']);
        }

        $name = $user->name;
        $username = $user->username;

        $user->delete();

        AuditLogger::log('user_deleted', "User account deleted: {$name} ({$username})");

        return redirect()->route('users.index')->with('success', 'Account deleted.');
    }
}
