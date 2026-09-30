<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Asisten Akreditasi Prodi</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    /* Animasi loading titik tiga */
    .typing-dot {
        animation: typingBounce 1.2s infinite ease-in-out;
    }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30% { transform: translateY(-6px); opacity: 1; }
    }

    /* Scrollbar rapi */
    #chat-area::-webkit-scrollbar { width: 6px; }
    #chat-area::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 9999px; }

    textarea#message-input {
        field-sizing: content;
    }
</style>
</head>
<body class="h-screen bg-gray-50 flex flex-col overflow-hidden">

    <!-- HEADER -->
    <header class="bg-red-700 text-white shadow-md z-10">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center gap-3">
            <div class="bg-white/15 p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8-1.05 0-2.06-.16-3-.46L3 21l1.5-4.5A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-semibold leading-tight">Asisten Akreditasi Prodi</h1>
                <p class="text-xs text-red-100">Sistem tanya jawab seputar akreditasi program studi</p>
            </div>
        </div>
    </header>

    <!-- CHAT AREA -->
    <main id="chat-area" class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto px-4 py-6 space-y-4" id="messages-container">

            <!-- Pesan sambutan default dari AI -->
            <div class="flex justify-start">
                <div class="max-w-[75%] bg-gray-100 text-gray-800 rounded-2xl rounded-bl-sm px-4 py-3 shadow-sm text-sm leading-relaxed">
                    Selamat datang, Bapak/Ibu Dosen 👋<br>
                    Saya adalah Asisten Akreditasi Program Studi. Silakan ajukan pertanyaan seputar dokumen, indikator, atau standar akreditasi.
                </div>
            </div>

        </div>
    </main>

    <!-- INPUT AREA -->
    <footer class="border-t border-gray-200 bg-white">
        <div class="max-w-4xl mx-auto px-4 py-3">
            <form id="chat-form" class="flex items-end gap-2">
                <textarea
                    id="message-input"
                    rows="1"
                    maxlength="2000"
                    placeholder="Tulis pertanyaan seputar akreditasi di sini..."
                    class="flex-1 resize-none max-h-40 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent"
                ></textarea>
                <button
                    type="submit"
                    id="send-button"
                    class="bg-red-700 hover:bg-red-800 disabled:bg-red-300 disabled:cursor-not-allowed text-white rounded-xl px-4 py-3 flex items-center justify-center transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9-7-9-7-9 7 9 7z" transform="rotate(90 12 12)"/>
                    </svg>
                </button>
            </form>
            <p class="text-[11px] text-gray-400 mt-1 text-center">Chatbot Asisten Akreditasi Sistem Informasi</p>
        </div>
    </footer>

<script>
(function () {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const messagesContainer = document.getElementById('messages-container');
    const chatArea = document.getElementById('chat-area');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // sessionId unik per tab/browser, disimpan supaya konteks percakapan konsisten
    const sessionId = (function () {
        let id = localStorage.getItem('akreditasi_session_id');
        if (!id) {
            id = 'sess-' + crypto.randomUUID();
            localStorage.setItem('akreditasi_session_id', id);
        }
        return id;
    })();

    function scrollToBottom() {
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    function appendUserMessage(text) {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex justify-end';
        wrapper.innerHTML = `
            <div class="max-w-[75%] bg-red-700 text-white rounded-2xl rounded-br-sm px-4 py-3 shadow-sm text-sm leading-relaxed whitespace-pre-wrap"></div>
        `;
        wrapper.querySelector('div').textContent = text;
        messagesContainer.appendChild(wrapper);
        scrollToBottom();
    }

    function appendAiMessage(text) {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex justify-start';
        wrapper.innerHTML = `
            <div class="max-w-[75%] bg-gray-100 text-gray-800 rounded-2xl rounded-bl-sm px-4 py-3 shadow-sm text-sm leading-relaxed whitespace-pre-wrap"></div>
        `;
        wrapper.querySelector('div').textContent = text;
        messagesContainer.appendChild(wrapper);
        scrollToBottom();
    }

    function showTypingIndicator() {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex justify-start';
        wrapper.id = 'typing-indicator';
        wrapper.innerHTML = `
            <div class="bg-gray-100 rounded-2xl rounded-bl-sm px-4 py-3 shadow-sm flex items-center gap-1.5">
                <span class="typing-dot w-2 h-2 bg-gray-400 rounded-full inline-block"></span>
                <span class="typing-dot w-2 h-2 bg-gray-400 rounded-full inline-block"></span>
                <span class="typing-dot w-2 h-2 bg-gray-400 rounded-full inline-block"></span>
            </div>
        `;
        messagesContainer.appendChild(wrapper);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        const el = document.getElementById('typing-indicator');
        if (el) el.remove();
    }

    async function sendMessage(text) {
        showTypingIndicator();
        sendButton.disabled = true;

        try {
            const response = await fetch('/api/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    chatInput: text,
                    sessionId: sessionId,
                }),
            });

            const data = await response.json();
            removeTypingIndicator();
            appendAiMessage(data.reply || 'Maaf, tidak ada respons dari asisten.');

        } catch (error) {
            removeTypingIndicator();
            appendAiMessage('Terjadi gangguan koneksi. Silakan periksa jaringan Anda dan coba lagi.');
            console.error(error);
        } finally {
            sendButton.disabled = false;
            input.focus();
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        appendUserMessage(text);
        input.value = '';
        input.style.height = 'auto';
        sendMessage(text);
    });

    // Enter untuk kirim, Shift+Enter untuk baris baru
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    input.focus();
})();
</script>

</body>
</html>