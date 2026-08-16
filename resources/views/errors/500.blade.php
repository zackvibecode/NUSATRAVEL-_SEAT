@extends('layouts.app')

@section('title', 'Server Error')

@section('content')
    <div class="max-w-xl mx-auto py-16 text-center">
        <div class="w-20 h-20 rounded-3xl bg-brand-soft flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <p class="text-6xl font-black tracking-tight mb-2">500</p>
        <h2 class="text-2xl font-black tracking-tight mb-2">Something went wrong</h2>
        <p class="text-charcoal text-sm mb-8">An unexpected error occurred. Our team has been notified. Please try again.</p>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/>
            </svg>
            Back to Dashboard
        </a>
    </div>
@endsection
