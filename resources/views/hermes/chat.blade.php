@extends('layouts.app')

@section('title', 'Hermes Chat')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-none">Hermes Chat</h2>
            <p class="text-sm text-charcoal mt-2">Tanya / arahan: baca, edit, padam pakej, padam pax. Contoh: <span class="font-semibold text-ink">list package</span>, <span class="font-semibold text-ink">cari TRANSJAVA</span>, <span class="font-semibold text-ink">padam pax 12</span></p>
        </div>
        <a href="{{ route('hermes.guide') }}" class="text-sm font-semibold text-brand hover:underline">API guide →</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-line overflow-hidden flex flex-col" style="min-height: 28rem;">
        <div id="hermes-log" class="flex-1 p-5 space-y-4 overflow-y-auto max-h-[60vh]">
            <div class="text-sm bg-fog rounded-2xl px-4 py-3 text-charcoal">
                Hi. Taip <strong>help</strong> untuk command. Saya boleh list data, cari, padam package, cancel trip, padam pax.
            </div>
        </div>
        <form id="hermes-form" class="border-t border-line p-4 flex gap-3">
            @csrf
            <input id="hermes-input" type="text" autocomplete="off" placeholder="Contoh: list package"
                   class="flex-1 rounded-full border border-line px-5 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none">
            <button type="submit" class="bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3">
                Hantar
            </button>
        </form>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    const log = document.getElementById('hermes-log');
    const form = document.getElementById('hermes-form');
    const input = document.getElementById('hermes-input');
    const token = document.querySelector('input[name="_token"]').value;

    function bubble(text, mine) {
        const div = document.createElement('div');
        div.className = mine
            ? 'ml-12 text-sm bg-brand text-white rounded-2xl px-4 py-3 whitespace-pre-wrap'
            : 'mr-12 text-sm bg-fog rounded-2xl px-4 py-3 whitespace-pre-wrap text-ink';
        div.textContent = text;
        log.appendChild(div);
        log.scrollTop = log.scrollHeight;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;
        bubble(message, true);
        input.value = '';
        try {
            const res = await fetch(@json(route('hermes.chat.message')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message }),
            });
            const data = await res.json();
            bubble(data.reply || data.message || 'Error', false);
        } catch (err) {
            bubble('Gagal connect. Cuba lagi.', false);
        }
    });
})();
</script>
@endsection
