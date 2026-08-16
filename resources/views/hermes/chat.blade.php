@extends('layouts.app')

@section('title', 'Hermes Chat')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">Hermes Chat</h2>
            <p class="text-sm text-charcoal mt-2">Ask or command: read, edit, delete packages, delete pax. Try: <span class="font-semibold text-ink">list package</span>, <span class="font-semibold text-ink">cari TRANSJAVA</span></p>
        </div>
        <a href="{{ route('hermes.guide') }}" class="text-sm font-semibold text-brand hover:underline">API guide →</a>
    </div>

    <div class="bg-white rounded-lg border border-line overflow-hidden flex flex-col" style="min-height: 28rem;">
        <div id="hermes-log" class="flex-1 p-4 sm:p-5 space-y-4 overflow-y-auto max-h-[60vh]">
            <div class="mr-12 text-sm bg-fog rounded-2xl px-4 py-3 whitespace-pre-wrap text-ink">
                Hi. Type <strong>help</strong> for commands. I can list data, search, delete package, cancel trip, delete pax.
            </div>
        </div>

        <!-- Quick command chips -->
        <div class="px-4 sm:px-5 pb-3 flex flex-wrap gap-2 border-t border-line pt-3">
            <button type="button" data-hermes-cmd="help" class="text-xs font-semibold bg-fog hover:bg-brand-soft text-charcoal hover:text-brand rounded-full px-3.5 py-1.5 transition-colors">help</button>
            <button type="button" data-hermes-cmd="overview" class="text-xs font-semibold bg-fog hover:bg-brand-soft text-charcoal hover:text-brand rounded-full px-3.5 py-1.5 transition-colors">overview</button>
            <button type="button" data-hermes-cmd="list package" class="text-xs font-semibold bg-fog hover:bg-brand-soft text-charcoal hover:text-brand rounded-full px-3.5 py-1.5 transition-colors">list package</button>
            <button type="button" data-hermes-cmd="list trip" class="text-xs font-semibold bg-fog hover:bg-brand-soft text-charcoal hover:text-brand rounded-full px-3.5 py-1.5 transition-colors">list trip</button>
            <button type="button" data-hermes-cmd="list pax" class="text-xs font-semibold bg-fog hover:bg-brand-soft text-charcoal hover:text-brand rounded-full px-3.5 py-1.5 transition-colors">list pax</button>
        </div>

        <form id="hermes-form" class="border-t border-line p-4 flex gap-3">
            @csrf
            <input id="hermes-input" type="text" autocomplete="off" placeholder="Type a command... e.g. list package"
                   class="flex-1 rounded-full border border-line px-5 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand/20 focus:outline-none">
            <button type="submit" id="hermes-send" class="bg-ink hover:bg-black text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-150 disabled:opacity-60 disabled:cursor-not-allowed">
                Send
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
        button.textContent = state ? 'Sending...' : 'Send';
        button.classList.toggle('opacity-60', state);
        button.classList.toggle('cursor-not-allowed', state);
    }

    async function send(message) {
        if (busy || !message) return;
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
                bubble('Too many messages. Wait a moment and try again.', false);
            } else if (!res.ok) {
                bubble('Server error (' + res.status + '). Try again.', false);
            } else {
                const data = await res.json();
                bubble(data.reply || data.message || 'Error', false);
            }
        } catch (err) {
            typing.remove();
            bubble('Connection failed. Check your internet and try again.', false);
        } finally {
            setBusy(false);
            input.focus();
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        send(input.value.trim());
    });

    document.querySelectorAll('[data-hermes-cmd]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            send(chip.dataset.hermesCmd);
        });
    });
})();
</script>
@endsection
