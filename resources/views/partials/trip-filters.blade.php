@php
    /** @var \App\Support\TripListFilter $filter */
    $showPackage = $showPackage ?? true;
    $showSort = $showSort ?? true;
    $showStatus = $showStatus ?? false;
    $extra = $extra ?? [];

    $selectClass = 'w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all';
    $labelClass = 'block text-xs font-semibold text-charcoal mb-2';
@endphp

<form method="GET" action="{{ route($filter->actionRoute) }}"
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-5 gap-y-5">
    @foreach ($extra as $key => $value)
        @if ($value !== null && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="min-w-0">
        <label for="month" class="{{ $labelClass }}">Month</label>
        <select name="month" id="month" class="{{ $selectClass }}">
            <option value="" @selected($filter->month === null)>All Months</option>
            @foreach (\App\Support\TripListFilter::months() as $num => $label)
                <option value="{{ $num }}" @selected($filter->month === $num)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="min-w-0">
        <label for="year" class="{{ $labelClass }}">Year</label>
        <select name="year" id="year" class="{{ $selectClass }}">
            <option value="" @selected($filter->year === null)>All Years</option>
            @foreach (\App\Support\TripListFilter::years() as $y)
                <option value="{{ $y }}" @selected($filter->year === $y)>{{ $y }}</option>
            @endforeach
        </select>
    </div>

    <div class="min-w-0">
        <label for="destination" class="{{ $labelClass }}">Country</label>
        <select name="destination" id="destination" class="{{ $selectClass }}">
            <option value="" @selected($filter->destination === null)>All Countries</option>
            @foreach (\App\Support\TripListFilter::destinations() as $dest)
                <option value="{{ $dest }}" @selected($filter->destination === $dest)>{{ $dest }}</option>
            @endforeach
        </select>
    </div>

    @if ($showPackage)
        <div class="min-w-0">
            <label for="package_id" class="{{ $labelClass }}">Package</label>
            <select name="package_id" id="package_id" class="{{ $selectClass }}">
                <option value="" @selected($filter->packageId === null)>All Packages</option>
                @foreach ($filter->packagesForDropdown() as $pkg)
                    <option value="{{ $pkg->id }}" @selected($filter->packageId === $pkg->id)>{{ $pkg->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if ($showStatus)
        <div class="min-w-0">
            <label for="status" class="{{ $labelClass }}">Status</label>
            <select name="status" id="status" class="{{ $selectClass }}">
                <option value="" @selected($filter->status === null)>All</option>
                <option value="active" @selected($filter->status === 'active')>Active</option>
                <option value="archived" @selected($filter->status === 'archived')>Archived</option>
            </select>
        </div>
    @endif

    @if ($showSort)
        <div class="min-w-0">
            <label for="sort" class="{{ $labelClass }}">Sort by</label>
            <select name="sort" id="sort" class="{{ $selectClass }}">
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
        <div class="min-w-0">
            <label for="dir" class="{{ $labelClass }}">Order</label>
            <select name="dir" id="dir" class="{{ $selectClass }}">
                <option value="asc" @selected($filter->dir === 'asc')>Oldest / A–Z first</option>
                <option value="desc" @selected($filter->dir === 'desc')>Newest / Z–A first</option>
            </select>
        </div>
    @endif

    <div class="flex flex-wrap items-end gap-3 sm:col-span-2 lg:col-span-1 xl:col-span-2 pb-0.5">
        <button type="submit"
                class="bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
            Filter
        </button>
        <a href="{{ route($filter->actionRoute) }}"
           class="inline-flex items-center text-sm font-semibold text-charcoal hover:text-ink px-3 py-3">
            Reset
        </a>
    </div>
</form>
