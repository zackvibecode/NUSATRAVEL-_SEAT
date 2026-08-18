@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Users</h2>
            <p class="text-sm text-charcoal mt-2">Manage admin and sales team accounts.</p>
        </div>
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center gap-2 bg-ink hover:bg-black text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New User
        </a>
    </div>

    <div class="bg-white rounded-lg border border-line overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-charcoal border-b border-line bg-fog/50">
                        <th class="px-6 py-4 font-semibold">Name</th>
                        <th class="px-6 py-4 font-semibold">Email</th>
                        <th class="px-6 py-4 font-semibold">Role</th>
                        <th class="px-6 py-4 font-semibold">PIC Name</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($users as $user)
                        <tr class="transition-colors hover:bg-fog/50">
                            <td class="px-6 py-4 font-bold">
                                {{ $user->name }}
                                @if ($user->id === auth()->id())
                                    <span class="ml-2 text-xs font-semibold text-charcoal">(you)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-charcoal font-medium">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @if ($user->isAdmin())
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-ink text-white">Admin</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-brand-soft text-brand">Sales</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-charcoal font-medium">
                                @if ($user->isAdmin())
                                    <span class="text-charcoal/60">—</span>
                                @else
                                    {{ $user->pic_name ?: $user->name }}
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end">
                                    @if ($user->id === auth()->id())
                                        <span class="text-xs text-charcoal font-medium">Current account</span>
                                    @else
                                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                                              onsubmit="return confirm('Delete user {{ $user->name }}? They will no longer be able to log in.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 text-sm font-bold rounded-full px-4 py-2 transition-all duration-150 border border-red-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-charcoal font-medium">
                                No users yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
