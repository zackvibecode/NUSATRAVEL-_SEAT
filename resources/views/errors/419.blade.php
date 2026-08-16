@extends('layouts.app')

@section('title', 'Session Expired')

@section('content')
    <div class="max-w-xl mx-auto py-16 text-center">
        <div class="w-20 h-20 rounded-3xl bg-warning-soft flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-warning" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-6xl font-black tracking-tight mb-2">419</p>
        <h2 class="text-2xl font-black tracking-tight mb-2">Session expired</h2>
        <p class="text-charcoal text-sm mb-8">Your session has expired. Please log in again to continue.</p>
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Log in again
        </a>
    </div>
@endsection
