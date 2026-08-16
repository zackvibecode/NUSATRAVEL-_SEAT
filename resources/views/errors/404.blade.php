@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
    <div class="max-w-xl mx-auto py-16 text-center">
        <div class="w-20 h-20 rounded-3xl bg-brand-soft flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-6xl font-black tracking-tight mb-2">404</p>
        <h2 class="text-2xl font-black tracking-tight mb-2">Page not found</h2>
        <p class="text-charcoal text-sm mb-8">The page you are looking for doesn't exist or has been moved.</p>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/>
            </svg>
            Back to Dashboard
        </a>
    </div>
@endsection
