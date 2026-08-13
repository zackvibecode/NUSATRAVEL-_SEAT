@extends('layouts.app')

@section('title', 'Packages')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight leading-none">Packages</h2>
            <p class="text-sm text-charcoal mt-2">Reusable travel products. One package can have many departure dates.</p>
        </div>
        <a href="{{ route('packages.create') }}"
           class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Package
        </a>
    </div>

    @include('partials.trip-filters', ['filter' => $filter, 'showPackage' => false, 'showStatus' => true])

    <div class="bg-white rounded-3xl shadow-sm border border-line overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-charcoal border-b border-line bg-fog/50">
                        <th class="px-6 py-4 font-semibold">
                            @include('partials.sort-link', ['filter' => $filter, 'column' => 'name', 'label' => 'Package'])
                        </th>
                        <th class="px-6 py-4 font-semibold">
                            @include('partials.sort-link', ['filter' => $filter, 'column' => 'destination', 'label' => 'Destination'])
                        </th>
                        <th class="px-6 py-4 font-semibold">Description</th>
                        <th class="px-6 py-4 font-semibold text-center">
                            @include('partials.sort-link', ['filter' => $filter, 'column' => 'departures_count', 'label' => 'Departures'])
                        </th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($packages as $package)
                        <tr class="transition-colors hover:bg-fog/50">
                            <td class="px-6 py-4 font-bold">{{ $package->name }}</td>
                            <td class="px-6 py-4 text-charcoal font-medium">{{ $package->destination }}</td>
                            <td class="px-6 py-4 text-charcoal max-w-xs truncate">{{ $package->description }}</td>
                            <td class="px-6 py-4 text-center text-charcoal font-semibold">{{ $package->departures_count }}</td>
                            <td class="px-6 py-4">
                                @include('partials.status-badge', ['status' => $package->status])
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('packages.edit', $package) }}"
                                       class="inline-flex items-center gap-1.5 text-brand hover:text-brand-hover text-sm font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>

                                    <button type="button"
                                            class="inline-flex items-center gap-1.5 text-red-600 hover:text-red-700 text-sm font-semibold"
                                            data-delete-package
                                            data-delete-url="{{ route('packages.destroy', $package) }}"
                                            data-package-name="{{ $package->name }}"
                                            data-departures-count="{{ $package->departures_count }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-charcoal font-medium">
                                No packages yet. Create your first package.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($packages->hasPages())
            <div class="px-6 py-4 border-t border-line">
                {{ $packages->links() }}
            </div>
        @endif
    </div>

    {{-- Delete confirm modal --}}
    <div id="deletePackageModal"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-ink/40 backdrop-blur-[2px]"
         role="dialog"
         aria-modal="true"
         aria-labelledby="deletePackageTitle">
        <div class="bg-white rounded-3xl shadow-xl border border-line w-full max-w-md p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 id="deletePackageTitle" class="text-lg font-black tracking-tight text-ink">Padam pakej?</h3>
                    <p class="text-sm text-charcoal mt-2 leading-relaxed">
                        Pakej <span id="deletePackageName" class="font-bold text-ink"></span> akan
                        <strong>hilang dari database</strong>. Semua trip dates dan peserta yang linked akan ikut dipadam.
                        Tindakan ini tidak boleh undo.
                    </p>
                    <p id="deletePackageMeta" class="text-xs text-charcoal/80 mt-2"></p>
                </div>
            </div>

            <form id="deletePackageForm" method="POST" class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button"
                        id="deletePackageCancel"
                        class="inline-flex justify-center items-center rounded-full px-5 py-2.5 text-sm font-semibold text-charcoal border border-line hover:bg-fog transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="inline-flex justify-center items-center rounded-full px-5 py-2.5 text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-colors">
                    Ya, padam
                </button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    (function () {
        const modal = document.getElementById('deletePackageModal');
        const form = document.getElementById('deletePackageForm');
        const nameEl = document.getElementById('deletePackageName');
        const metaEl = document.getElementById('deletePackageMeta');
        const cancelBtn = document.getElementById('deletePackageCancel');

        if (!modal || !form) return;

        function openModal(url, name, departuresCount) {
            form.action = url;
            nameEl.textContent = name;
            const count = Number(departuresCount) || 0;
            metaEl.textContent = count > 0
                ? `Pakej ini ada ${count} departure yang akan ikut hilang.`
                : 'Pakej ini tiada departure.';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            form.action = '';
        }

        document.querySelectorAll('[data-delete-package]').forEach((btn) => {
            btn.addEventListener('click', () => {
                openModal(
                    btn.getAttribute('data-delete-url'),
                    btn.getAttribute('data-package-name') || 'pakej ini',
                    btn.getAttribute('data-departures-count')
                );
            });
        });

        cancelBtn?.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
    })();
</script>
@endsection
