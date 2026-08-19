@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="mb-5">
        <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Edit User</h2>
        <p class="text-sm text-charcoal mt-2">Update account details, role, or PIC name.</p>
    </div>

    <form method="POST" action="{{ route('users.update', $user) }}" class="bg-surface rounded-lg border border-line p-4 sm:p-6 space-y-6 max-w-2xl">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-semibold text-ink mb-2">Name *</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
                class="w-full rounded-xl border @error('name') border-red-400 @else border-line @enderror bg-surface px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
            >
            @include('partials.field-error', ['field' => 'name'])
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-ink mb-2">Email *</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
                class="w-full rounded-xl border @error('email') border-red-400 @else border-line @enderror bg-surface px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
            >
            @include('partials.field-error', ['field' => 'email'])
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-ink mb-2">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                autocomplete="new-password"
                class="w-full rounded-xl border @error('password') border-red-400 @else border-line @enderror bg-surface px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
            >
            <p class="text-xs text-charcoal mt-1.5 font-medium">Leave blank to keep the current password.</p>
            @include('partials.field-error', ['field' => 'password'])
        </div>

        <div>
            <label for="role" class="block text-sm font-semibold text-ink mb-2">Role *</label>
            <select
                id="role"
                name="role"
                required
                class="w-full rounded-xl border @error('role') border-red-400 @else border-line @enderror bg-surface px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
            >
                <option value="sales" @selected(old('role', $user->role) === 'sales')>Sales — dashboard + trips (view only)</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin — full access</option>
            </select>
            @include('partials.field-error', ['field' => 'role'])
        </div>

        <div>
            <label for="pic_name" class="block text-sm font-semibold text-ink mb-2">PIC Name</label>
            <input
                type="text"
                id="pic_name"
                name="pic_name"
                value="{{ old('pic_name', $user->pic_name) }}"
                placeholder="e.g. the PIC name used in invoices"
                class="w-full rounded-xl border border-line bg-surface px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
            >
            <p class="text-xs text-charcoal mt-1.5 font-medium">
                For sales users: matches Payment Alert records where this person is PIC Utama or PIC In House. Leave empty to use their full name.
            </p>
            @include('partials.field-error', ['field' => 'pic_name'])
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="bg-ink hover:bg-black text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors duration-150">
                Save Changes
            </button>
            <a href="{{ route('users.index') }}" class="text-sm font-bold text-charcoal hover:text-ink">Cancel</a>
        </div>
    </form>
@endsection
