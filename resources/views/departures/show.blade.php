@extends('layouts.app')

@section('title', $departure->package->name)

@section('content')
    <!-- Breadcrumb / actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <a href="{{ route('departures.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-charcoal hover:text-ink transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Departures
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('departures.manifest', $departure) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-white shadow-sm border border-line hover:shadow-md text-ink text-sm font-bold rounded-full px-6 py-3 transition-all duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Manifest
            </a>
            <a href="{{ route('departures.edit', $departure) }}"
               class="inline-flex items-center gap-2 bg-white shadow-sm border border-line hover:shadow-md text-ink text-sm font-bold rounded-full px-6 py-3 transition-all duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Trip
            </a>
            <a href="#add-registration"
               class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Registration
            </a>
        </div>
    </div>

    <!-- Trip header -->
    <div class="bg-white rounded-3xl shadow-sm border border-line p-8 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-brand-soft flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-black tracking-tight leading-none">{{ $departure->package->name }}</h2>
                        <p class="text-sm text-charcoal font-medium mt-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $departure->departure_date->format('d M Y') }} → {{ $departure->return_date->format('d M Y') }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-charcoal font-medium">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $departure->package->destination }}
                    </span>
                    @if ($departure->airline)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            {{ $departure->airline }}
                        </span>
                    @endif
                    @if ($departure->price)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            RM {{ number_format($departure->price, 2) }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                @include('partials.status-badge', ['status' => $departure->status_label])
            </div>
        </div>

        @if ($departure->notes)
            <div class="mt-6 bg-fog rounded-2xl px-5 py-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-charcoal flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <p class="text-sm text-charcoal font-medium">{{ $departure->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Seat summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Total Seats</p>
            </div>
            <p class="text-4xl font-black leading-none">{{ $departure->total_seats }}</p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Registered</p>
            </div>
            <p class="text-4xl font-black leading-none">{{ $departure->registered_pax }}</p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl {{ $departure->available_seats <= 0 ? 'bg-brand' : 'bg-positive-soft' }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $departure->available_seats <= 0 ? 'text-white' : 'text-positive' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Available</p>
            </div>
            <p class="text-4xl font-black leading-none {{ $departure->available_seats <= 0 ? 'text-brand' : 'text-positive' }}">{{ $departure->available_seats }}</p>
        </div>
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-charcoal uppercase tracking-wider">Occupancy</p>
            </div>
            <p class="text-4xl font-black leading-none">{{ $departure->occupancy_percent }}%</p>
            <div class="mt-3 h-2 bg-fog rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $departure->occupancy_percent >= 100 ? 'bg-brand' : ($departure->occupancy_percent >= 75 ? 'bg-warning' : 'bg-positive') }}"
                     style="width: {{ min(100, $departure->occupancy_percent) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Registrations table -->
    <div class="bg-white rounded-3xl shadow-sm border border-line overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-line flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg tracking-tight">Registrations</h3>
                    <p class="text-xs text-charcoal mt-0.5 font-medium">{{ $registrations->count() }} entries · total {{ $departure->registered_pax }} pax</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-charcoal border-b border-line bg-fog/50">
                        <th class="px-6 py-4 font-semibold">Name</th>
                        <th class="px-6 py-4 font-semibold">Phone</th>
                        <th class="px-6 py-4 font-semibold text-right">Pax</th>
                        <th class="px-6 py-4 font-semibold">Payment</th>
                        <th class="px-6 py-4 font-semibold">Partner</th>
                        <th class="px-6 py-4 font-semibold">Notes</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($registrations as $registration)
                        <tr class="transition-colors hover:bg-fog/50">
                            <td class="px-6 py-4 font-bold">{{ $registration->name }}</td>
                            <td class="px-6 py-4 text-charcoal font-medium">{{ $registration->phone }}</td>
                            <td class="px-6 py-4 text-right font-bold">{{ $registration->pax }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $registration->payment_color }}">
                                    {{ $registration->payment_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($registration->need_partner)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-warning-soft text-warning">
                                        {{ $registration->partner_label }}
                                    </span>
                                @else
                                    <span class="text-charcoal font-medium">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-charcoal max-w-xs truncate">{{ $registration->notes }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <button type="button"
                                            data-reg-id="{{ $registration->id }}"
                                            data-name="{{ $registration->name }}"
                                            data-phone="{{ $registration->phone }}"
                                            data-pax="{{ $registration->pax }}"
                                            data-payment-status="{{ $registration->payment_status }}"
                                            data-need-partner="{{ $registration->need_partner ? '1' : '0' }}"
                                            data-partner-gender="{{ $registration->partner_gender }}"
                                            data-notes="{{ $registration->notes }}"
                                            onclick="openEditModal(this)"
                                            class="edit-btn inline-flex items-center gap-1.5 text-brand hover:text-brand-hover text-sm font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button type="button"
                                            onclick="copyWhatsApp('{{ addslashes($registration->name) }}', '{{ $registration->phone }}', '{{ addslashes($departure->package->name) }}', '{{ $departure->departure_date->format('d M Y') }}')"
                                            class="inline-flex items-center gap-1.5 text-positive hover:text-positive/80 text-sm font-semibold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        WA
                                    </button>
                                    <form method="POST" action="{{ route('registrations.destroy', $registration) }}"
                                          onsubmit="return confirm('Delete this registration? Seat availability will be recalculated.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 text-sm font-bold rounded-full px-4 py-2 transition-all duration-150 border border-red-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-charcoal font-medium">
                                No registrations yet. Add the first participant below.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add registration form -->
    <div id="add-registration" class="bg-white rounded-3xl shadow-sm border border-line p-8 mb-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-2xl bg-brand-soft flex items-center justify-center">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-lg tracking-tight">Add Registration</h3>
                <p class="text-xs text-charcoal mt-0.5 font-medium">
                    One row can represent multiple pax (e.g. Ahmad = family of 4). {{ $departure->available_seats }} seats currently available.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('registrations.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-5" id="addRegForm">
            @csrf
            <input type="hidden" name="departure_id" value="{{ $departure->id }}">

            <div>
                <label for="reg_name" class="block text-sm font-semibold text-ink mb-2">Participant Name *</label>
                <input
                    type="text"
                    id="reg_name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
            </div>

            <div>
                <label for="reg_phone" class="block text-sm font-semibold text-ink mb-2">Phone Number</label>
                <input
                    type="text"
                    id="reg_phone"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="e.g. 012-3456789"
                    class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
            </div>

            <div>
                <label for="reg_pax" class="block text-sm font-semibold text-ink mb-2">Number of Pax *</label>
                <input
                    type="number"
                    id="reg_pax"
                    name="pax"
                    value="{{ old('pax', 1) }}"
                    min="1"
                    required
                    class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
            </div>

            <div>
                <label for="reg_payment_status" class="block text-sm font-semibold text-ink mb-2">Payment Status</label>
                <select
                    id="reg_payment_status"
                    name="payment_status"
                    class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
                    <option value="pending" @selected(old('payment_status', 'pending') === 'pending')>Pending</option>
                    <option value="deposit" @selected(old('payment_status') === 'deposit')>Deposit</option>
                    <option value="paid" @selected(old('payment_status') === 'paid')>Paid</option>
                </select>
            </div>

            <div>
                <label for="reg_need_partner" class="block text-sm font-semibold text-ink mb-2">Need Partner?</label>
                <select
                    id="reg_need_partner"
                    name="need_partner"
                    onchange="togglePartnerGender(this)"
                    class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
                    <option value="0" @selected(old('need_partner', '0') === '0')>No</option>
                    <option value="1" @selected(old('need_partner') === '1')>Yes</option>
                </select>
            </div>

            <div id="reg_partner_gender_wrap" class="hidden">
                <label for="reg_partner_gender" class="block text-sm font-semibold text-ink mb-2">Partner Gender *</label>
                <select
                    id="reg_partner_gender"
                    name="partner_gender"
                    class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
                    <option value="">Select gender...</option>
                    <option value="male" @selected(old('partner_gender') === 'male')>Male</option>
                    <option value="female" @selected(old('partner_gender') === 'female')>Female</option>
                </select>
            </div>

            <div>
                <label for="reg_notes" class="block text-sm font-semibold text-ink mb-2">Notes</label>
                <input
                    type="text"
                    id="reg_notes"
                    name="notes"
                    value="{{ old('notes') }}"
                    class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
            </div>

            <div class="md:col-span-2 flex items-center gap-4 pt-2">
                <button type="submit" id="saveRegBtn"
                        class="bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
                    Save Registration
                </button>
            </div>
        </form>
    </div>

    <!-- Edit registration modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-lg tracking-tight">Edit Registration</h3>
                <button type="button" onclick="closeEditModal()" class="text-charcoal hover:text-ink text-2xl leading-none font-medium">&times;</button>
            </div>

            <form id="editForm" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="edit_name" class="block text-sm font-semibold text-ink mb-2">Participant Name *</label>
                    <input type="text" id="edit_name" name="name" required
                           class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
                </div>

                <div>
                    <label for="edit_phone" class="block text-sm font-semibold text-ink mb-2">Phone Number</label>
                    <input type="text" id="edit_phone" name="phone"
                           class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
                </div>

                <div>
                    <label for="edit_pax" class="block text-sm font-semibold text-ink mb-2">Number of Pax *</label>
                    <input type="number" id="edit_pax" name="pax" min="1" required
                           class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
                </div>

                <div>
                    <label for="edit_payment_status" class="block text-sm font-semibold text-ink mb-2">Payment Status</label>
                    <select id="edit_payment_status" name="payment_status"
                            class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
                        <option value="pending">Pending</option>
                        <option value="deposit">Deposit</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>

                <div>
                    <label for="edit_need_partner" class="block text-sm font-semibold text-ink mb-2">Need Partner?</label>
                    <select id="edit_need_partner" name="need_partner" onchange="toggleEditPartnerGender(this)"
                            class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>

                <div id="edit_partner_gender_wrap" class="hidden">
                    <label for="edit_partner_gender" class="block text-sm font-semibold text-ink mb-2">Partner Gender *</label>
                    <select id="edit_partner_gender" name="partner_gender"
                            class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
                        <option value="">Select gender...</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div>
                    <label for="edit_notes" class="block text-sm font-semibold text-ink mb-2">Notes</label>
                    <input type="text" id="edit_notes" name="notes"
                           class="w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
                </div>

                <div class="md:col-span-2 flex items-center gap-4 pt-2">
                    <button type="submit" id="updateRegBtn"
                            class="bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
                        Update Registration
                    </button>
                    <button type="button" onclick="closeEditModal()" class="text-sm font-bold text-charcoal hover:text-ink">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function togglePartnerGender(select) {
        const wrap = document.getElementById('reg_partner_gender_wrap');
        if (select.value === '1') {
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
            document.getElementById('reg_partner_gender').value = '';
        }
    }

    function toggleEditPartnerGender(select) {
        const wrap = document.getElementById('edit_partner_gender_wrap');
        if (select.value === '1') {
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
            document.getElementById('edit_partner_gender').value = '';
        }
    }

    function openEditModal(btn) {
        const id = btn.dataset.regId;
        const form = document.getElementById('editForm');
        form.action = '{{ url('/registrations') }}/' + id;
        form.querySelector('#edit_name').value = btn.dataset.name;
        form.querySelector('#edit_phone').value = btn.dataset.phone;
        form.querySelector('#edit_pax').value = btn.dataset.pax;
        form.querySelector('#edit_payment_status').value = btn.dataset.paymentStatus || 'pending';
        form.querySelector('#edit_need_partner').value = btn.dataset.needPartner;
        form.querySelector('#edit_partner_gender').value = btn.dataset.partnerGender;
        form.querySelector('#edit_notes').value = btn.dataset.notes;

        if (btn.dataset.needPartner === '1') {
            document.getElementById('edit_partner_gender_wrap').classList.remove('hidden');
        } else {
            document.getElementById('edit_partner_gender_wrap').classList.add('hidden');
        }

        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function copyWhatsApp(name, phone, packageName, date) {
        const msg = `Hi ${name}, your booking for ${packageName} on ${date} is confirmed. Total pax: ${document.getElementById('reg_pax')?.value || 'N/A'}. Please reply YES to confirm.`;
        navigator.clipboard.writeText(msg).then(() => {
            alert('WhatsApp message copied to clipboard!');
        }).catch(() => {
            // Fallback
            const ta = document.createElement('textarea');
            ta.value = msg;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            alert('WhatsApp message copied to clipboard!');
        });
    }

    // Disable submit buttons on form submit
    document.getElementById('addRegForm')?.addEventListener('submit', function() {
        document.getElementById('saveRegBtn').disabled = true;
        document.getElementById('saveRegBtn').textContent = 'Saving...';
    });
    document.getElementById('editForm')?.addEventListener('submit', function() {
        document.getElementById('updateRegBtn').disabled = true;
        document.getElementById('updateRegBtn').textContent = 'Updating...';
    });
</script>
@endsection
