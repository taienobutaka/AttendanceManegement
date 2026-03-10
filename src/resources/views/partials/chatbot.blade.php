<div id="chatbot-widget" data-csrf="{{ csrf_token() }}">
    <button type="button" id="chatbot-toggle" aria-label="チャットを開く">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
    </button>
    <div id="chatbot-panel">
        <div id="chatbot-header">Atte サポート</div>
        <div id="chatbot-messages"></div>
        <form id="chatbot-form">
            <input type="text" id="chatbot-input" placeholder="質問を入力..." autocomplete="off" maxlength="1000">
            <button type="submit" id="chatbot-send">送信</button>
        </form>
    </div>
</div>
<link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
<script>
document.addEventListener('DOMContentLoaded', function() {
    var widget = document.getElementById('chatbot-widget');
    var toggle = document.getElementById('chatbot-toggle');
    var panel = document.getElementById('chatbot-panel');
    var messages = document.getElementById('chatbot-messages');
    var form = document.getElementById('chatbot-form');
    var input = document.getElementById('chatbot-input');
    var sendBtn = document.getElementById('chatbot-send');
    var csrf = widget.getAttribute('data-csrf');

    toggle.addEventListener('click', function() {
        widget.classList.toggle('is-open');
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var text = (input.value || '').trim();
        if (!text) return;

        var userBubble = document.createElement('div');
        userBubble.className = 'chatbot-msg user';
        userBubble.textContent = text;
        messages.appendChild(userBubble);
        input.value = '';
        sendBtn.disabled = true;

        var loading = document.createElement('div');
        loading.className = 'chatbot-msg assistant';
        loading.textContent = '...';
        loading.setAttribute('aria-busy', 'true');
        messages.appendChild(loading);
        messages.scrollTop = messages.scrollHeight;

        fetch('{{ route("chatbot") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ message: text })
        })
        .then(function(res) { return res.json().then(function(data) { return { ok: res.ok, data: data }; }); })
        .then(function(result) {
            loading.remove();
            var reply = result.data.reply != null ? result.data.reply : '応答を取得できませんでした。';
            var bubble = document.createElement('div');
            bubble.className = result.ok ? 'chatbot-msg assistant' : 'chatbot-msg error';
            bubble.textContent = reply;
            messages.appendChild(bubble);
            messages.scrollTop = messages.scrollHeight;
        })
        .catch(function() {
            loading.remove();
            var err = document.createElement('div');
            err.className = 'chatbot-msg error';
            err.textContent = '通信エラーです。しばらくしてからお試しください。';
            messages.appendChild(err);
            messages.scrollTop = messages.scrollHeight;
        })
        .finally(function() {
            sendBtn.disabled = false;
        });
    });
});
</script>
