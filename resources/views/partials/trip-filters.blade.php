@php
    /** @var \App\Support\TripListFilter $filter */
    $showPackage = $showPackage ?? true;
    $showSort = $showSort ?? true;
    $showStatus = $showStatus ?? false;
    $extra = $extra ?? [];
@endphp

<form method="GET" action="{{ route($filter->actionRoute) }}" class="flex flex-wrap items-end gap-3">
    @foreach ($extra as $key => $value)
        @if ($value !== null && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div>
        <label for="month" class="block text-xs font-semibold text-charcoal mb-2">Month</label>
        <select name="month" id="month"
                class="rounded-full border border-line bg-white px-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
            <option value="" @selected($filter->month === null)>All Months</option>
            @foreach (\App\Support\TripListFilter::months() as $num => $label)
                <option value="{{ $num }}" @selected($filter->month === $num)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="year" class="block text-xs font-semibold text-charcoal mb-2">Year</label>
        <select name="year" id="year"
                class="rounded-full border border-line bg-white px-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
            <option value="" @selected($filter->year === null)>All Years</option>
            @foreach (\App\Support\TripListFilter::years() as $y)
                <option value="{{ $y }}" @selected($filter->year === $y)>{{ $y }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="destination" class="block text-xs font-semibold text-charcoal mb-2">Country</label>
        <select name="destination" id="destination"
                class="rounded-full border border-line bg-white px-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all min-w-[140px]">
            <option value="" @selected($filter->destination === null)>All Countries</option>
            @foreach (\App\Support\TripListFilter::destinations() as $dest)
                <option value="{{ $dest }}" @selected($filter->destination === $dest)>{{ $dest }}</option>
            @endforeach
        </select>
    </div>

    @if ($showPackage)
        <div>
            <label for="package_id" class="block text-xs font-semibold text-charcoal mb-2">Package</label>
            <select name="package_id" id="package_id"
                    class="rounded-full border border-line bg-white px-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all min-w-[160px]">
                <option value="" @selected($filter->packageId === null)>All Packages</option>
                @foreach ($filter->packagesForDropdown() as $pkg)
                    <option value="{{ $pkg->id }}" @selected($filter->packageId === $pkg->id)>{{ $pkg->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if ($showStatus)
        <div>
            <label for="status" class="block text-xs font-semibold text-charcoal mb-2">Status</label>
            <select name="status" id="status"
                    class="rounded-full border border-line bg-white px-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
                <option value="" @selected($filter->status === null)>All</option>
                <option value="active" @selected($filter->status === 'active')>Active</option>
                <option value="archived" @selected($filter->status === 'archived')>Archived</option>
            </select>
        </div>
    @endif

    @if ($showSort)
        <div>
            <label for="sort" class="block text-xs font-semibold text-charcoal mb-2">Sort by</label>
            <select name="sort" id="sort"
                    class="rounded-full border border-line bg-white px-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
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
            <label for="dir" class="block text-xs font-semibold text-charcoal mb-2">Order</label>
            <select name="dir" id="dir"
                    class="rounded-full border border-line bg-white px-4 py-2.5 text-sm font-medium focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none transition-all">
                <option value="asc" @selected($filter->dir === 'asc')>Oldest / A–Z first</option>
                <option value="desc" @selected($filter->dir === 'desc')>Newest / Z–A first</option>
            </select>
        </div>
    @endif

    <button type="submit"
            class="bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-2.5 transition-all duration-150 hover:scale-[1.03] shadow-sm hover:shadow-md">
        Filter
    </button>
    <a href="{{ route($filter->actionRoute) }}" class="text-sm font-semibold text-charcoal hover:text-ink px-2 py-2.5">Reset</a>
</form>
