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
            <button type="submit" id="hermes-send" class="bg-brand hover:bg-brand-hover text-white text-sm font-bold rounded-full px-6 py-3 transition-all duration-150 disabled:opacity-60 disabled:cursor-not-allowed">
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
    const button = document.getElementById('hermes-send');
    const token = document.querySelector('input[name="_token"]').value;
    let busy = false;

    function scrollDown() {
        log.scrollTop = log.scrollHeight;
    }

    function bubble(text, mine) {
        const div = document.createElement('div');
        div.className = mine
            ? 'ml-12 text-sm bg-brand text-white rounded-2xl px-4 py-3 whitespace-pre-wrap'
            : 'mr-12 text-sm bg-fog rounded-2xl px-4 py-3 whitespace-pre-wrap text-ink';
        div.textContent = text;
        log.appendChild(div);
        scrollDown();
        return div;
    }

    function typingBubble() {
        const div = document.createElement('div');
        div.className = 'mr-12 text-sm bg-fog rounded-2xl px-4 py-3 text-charcoal flex items-center gap-1.5';
        div.setAttribute('aria-label', 'Hermes is typing');
        for (let i = 0; i < 3; i++) {
            const dot = document.createElement('span');
            dot.className = 'w-2 h-2 rounded-full bg-charcoal/60 animate-bounce';
            dot.style.animationDelay = (i * 0.15) + 's';
            div.appendChild(dot);
        }
        log.appendChild(div);
        scrollDown();
        return div;
    }

    function setBusy(state) {
        busy = state;
        button.disabled = state;
        input.disabled = state;
        button.textContent = state ? 'Sending...' : 'Hantar';
        button.classList.toggle('opacity-60', state);
        button.classList.toggle('cursor-not-allowed', state);
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (busy) return;
        const message = input.value.trim();
        if (!message) return;
        bubble(message, true);
        input.value = '';
        setBusy(true);
        const typing = typingBubble();
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
            typing.remove();
            if (res.status === 429) {
                bubble('Terlalu banyak mesej. Tunggu sebentar dan cuba lagi.', false);
            } else if (!res.ok) {
                bubble('Server error (' + res.status + '). Cuba lagi.', false);
            } else {
                const data = await res.json();
                bubble(data.reply || data.message || 'Error', false);
            }
        } catch (err) {
            typing.remove();
            bubble('Gagal connect. Semak internet dan cuba lagi.', false);
        } finally {
            setBusy(false);
            input.focus();
        }
    });
})();
</script>
@endsection
