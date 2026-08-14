@php
    /** @var \App\Support\TripListFilter $filter */
    $showPackage = $showPackage ?? true;
    $showSort = $showSort ?? true;
    $showStatus = $showStatus ?? false;
    $showSearch = $showSearch ?? ($filter->context === 'departures');
    $extra = $extra ?? [];

    $selectClass = 'rounded-xl border border-line bg-white px-3 py-2 text-xs font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all';
    $labelClass = 'block text-[11px] font-semibold text-charcoal mb-1.5';
@endphp

<form method="GET" action="{{ route($filter->actionRoute) }}"
      id="tripFilterForm"
      class="flex flex-wrap items-end gap-x-6 gap-y-4">
    @foreach ($extra as $key => $value)
        @if ($value !== null && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    @if ($showSearch)
        <div class="w-full flex-[1_1_100%]">
            <label for="search" class="{{ $labelClass }}">Search</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-charcoal" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $filter->search }}"
                    placeholder="Search package, destination, airline, or customer..."
                    autocomplete="off"
                    class="w-full rounded-xl border border-line bg-white pl-12 pr-4 py-3 text-base font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all"
                >
            </div>
            <p class="text-[11px] text-charcoal mt-1.5 font-medium">Case-insensitive · results update as you type</p>
        </div>
    @endif

    <div>
        <label for="month" class="{{ $labelClass }}">Month</label>
        <select name="month" id="month" class="{{ $selectClass }} min-w-[8.5rem]">
            <option value="" @selected($filter->month === null)>All Months</option>
            @foreach (\App\Support\TripListFilter::months() as $num => $label)
                <option value="{{ $num }}" @selected($filter->month === $num)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="year" class="{{ $labelClass }}">Year</label>
        <select name="year" id="year" class="{{ $selectClass }} min-w-[7rem]">
            <option value="" @selected($filter->year === null)>All Years</option>
            @foreach (\App\Support\TripListFilter::years() as $y)
                <option value="{{ $y }}" @selected($filter->year === $y)>{{ $y }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="destination" class="{{ $labelClass }}">Country</label>
        <select name="destination" id="destination" class="{{ $selectClass }} min-w-[9.5rem]">
            <option value="" @selected($filter->destination === null)>All Countries</option>
            @foreach (\App\Support\TripListFilter::destinations() as $dest)
                <option value="{{ $dest }}" @selected($filter->destination === $dest)>{{ $dest }}</option>
            @endforeach
        </select>
    </div>

    @if ($showPackage)
        <div>
            <label for="package_id" class="{{ $labelClass }}">Package</label>
            <select name="package_id" id="package_id" class="{{ $selectClass }} min-w-[10.5rem] max-w-[14rem]">
                <option value="" @selected($filter->packageId === null)>All Packages</option>
                @foreach ($filter->packagesForDropdown() as $pkg)
                    <option value="{{ $pkg->id }}" @selected($filter->packageId === $pkg->id)>{{ $pkg->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if ($showStatus)
        <div>
            <label for="status" class="{{ $labelClass }}">Status</label>
            <select name="status" id="status" class="{{ $selectClass }} min-w-[7rem]">
                <option value="" @selected($filter->status === null)>All</option>
                <option value="active" @selected($filter->status === 'active')>Active</option>
                <option value="archived" @selected($filter->status === 'archived')>Archived</option>
            </select>
        </div>
    @endif

    @if ($showSort)
        <div>
            <label for="sort" class="{{ $labelClass }}">Sort by</label>
            <select name="sort" id="sort" class="{{ $selectClass }} min-w-[9.5rem]">
                @if ($filter->context === 'packages')
                    <option value="name" @selected($filter->sort === 'name')>Package name</option>
                    <option value="destination" @selected($filter->sort === 'destination')>Country</option>
                    <option value="departures_count" @selected($filter->sort === 'departures_count')># Departures</option>
                @elseif (in_array($filter->context, ['participants', 'need_partner']))
                    <option value="departure_date" @selected($filter->sort === 'departure_date')>Departure date</option>
                    <option value="name" @selected($filter->sort === 'name')>Participant name</option>
                    <option value="package_name" @selected($filter->sort === 'package_name')>Package</option>
                @else
                    <option value="departure_date" @selected($filter->sort === 'departure_date')>Departure date</option>
                    <option value="package_name" @selected($filter->sort === 'package_name')>Package name</option>
                    <option value="destination" @selected($filter->sort === 'destination')>Country</option>
                @endif
            </select>
        </div>
        <div>
            <label for="dir" class="{{ $labelClass }}">Order</label>
            <select name="dir" id="dir" class="{{ $selectClass }} min-w-[10rem]">
                <option value="asc" @selected($filter->dir === 'asc')>Oldest / A–Z first</option>
                <option value="desc" @selected($filter->dir === 'desc')>Newest / Z–A first</option>
            </select>
        </div>
    @endif

    <div class="flex items-center gap-3 pb-0.5">
        <button type="submit"
                class="bg-brand hover:bg-brand-hover text-white text-xs font-bold rounded-full px-5 py-2 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
            Filter
        </button>
        <a href="{{ route($filter->actionRoute) }}"
           class="text-xs font-semibold text-charcoal hover:text-ink py-2">
            Reset
        </a>
    </div>
</form>

@if ($showSearch)
<script>
    (function () {
        const form = document.getElementById('tripFilterForm');
        if (!form) return;

        const searchInput = form.querySelector('#search');
        const FOCUS_KEY = 'trip_search_focused';
        const CURSOR_KEY = 'trip_search_cursor';

        // Restore focus + cursor position after a page reload so the user
        // can keep typing without interruption.
        if (searchInput && sessionStorage.getItem(FOCUS_KEY) === '1') {
            sessionStorage.removeItem(FOCUS_KEY);
            searchInput.focus();
            const pos = parseInt(sessionStorage.getItem(CURSOR_KEY), 10);
            const len = searchInput.value.length;
            const restore = isNaN(pos) ? len : Math.min(pos, len);
            try {
                searchInput.setSelectionRange(restore, restore);
            } catch (e) {
                // Some input types don't support setSelectionRange — ignore.
            }
            sessionStorage.removeItem(CURSOR_KEY);
        }

        function submitForm() {
            if (searchInput) {
                sessionStorage.setItem(FOCUS_KEY, '1');
                sessionStorage.setItem(CURSOR_KEY, String(searchInput.selectionStart || searchInput.value.length));
            }
            form.submit();
        }

        // Live search: auto-submit after debounce while typing, but only if
        // the value actually changed (prevents double-submit on Enter etc).
        if (searchInput) {
            let timer = null;
            let lastValue = searchInput.value;

            searchInput.addEventListener('input', function () {
                const value = searchInput.value;
                if (value === lastValue) return;
                lastValue = value;
                clearTimeout(timer);
                timer = setTimeout(submitForm, 700);
            });

            // If the user hits Enter, cancel the pending debounce and submit
            // immediately (keeps focus restored right away).
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(timer);
                    submitForm();
                }
            });
        }

        // Auto-submit when any dropdown changes too (month/year/etc).
        form.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', submitForm);
        });
    })();
</script>
@endif
