<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private ActivityLogger $audit) {}

    public function index(): View
    {
        $users = User::query()->orderBy('name')->get();

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,sales'],
            'pic_name' => ['nullable', 'string', 'max:255'],
        ]);

        User::create($data);

        $this->audit->log('created', $user = User::where('email', $data['email'])->first(), [
            'name' => ['old' => null, 'new' => $data['name']],
            'email' => ['old' => null, 'new' => $data['email']],
            'role' => ['old' => null, 'new' => $data['role']],
            'pic_name' => ['old' => null, 'new' => $data['pic_name'] ?? null],
        ]);

        return redirect()->route('users.index')
            ->with('success', "User \"{$data['name']}\" was created as {$data['role']}.");
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:admin,sales'],
            'pic_name' => ['nullable', 'string', 'max:255'],
        ]);

        // Don't overwrite the existing password when the field is left blank
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $original = $user->getRawOriginal();

        $user->update($data);

        $this->audit->log('updated', $user, $this->audit->diff($original, $user));

        return redirect()->route('users.index')
            ->with('success', "User \"{$data['name']}\" was updated.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;

        $this->audit->log('deleted', $user, [
            'name' => ['old' => $user->name, 'new' => null],
            'email' => ['old' => $user->email, 'new' => null],
            'role' => ['old' => $user->role, 'new' => null],
        ]);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User \"{$name}\" was deleted.");
    }
}
