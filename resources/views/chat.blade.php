<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Asisten Akreditasi Prodi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<!-- Render Markdown + sanitasi (BARU) -->
<script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>
<!-- PDF.js: menggambar halaman PDF untuk pratinjau saat hover (BARU) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    if (window.pdfjsLib) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }
</script>
<script>
tailwind.config = {
    theme: {
        extend: {
            fontFamily: { sans: ['"Instrument Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
            colors: {
                brand: {
                    50: '#FFF3F3', 100: '#FFE1E2', 200: '#FFC4C7', 300: '#FF9A9F',
                    500: '#E0262F', 600: '#C8161F', 700: '#A8101A', 800: '#870D16', 900: '#5E0A10'
                }
            }
        }
    }
}
</script>
<style>
    .typing-dot { animation: typingBounce 1.2s infinite ease-in-out; }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30% { transform: translateY(-5px); opacity: 1; }
    }
    #chat-area::-webkit-scrollbar { width: 6px; }
    #chat-area::-webkit-scrollbar-thumb { background-color: #f0c4c6; border-radius: 9999px; }
    textarea#message-input { field-sizing: content; }
    @media (prefers-reduced-motion: reduce) { .typing-dot { animation: none; } }

    /* ===== KOTAK PRATINJAU DOKUMEN (hanya tampilan) ===== */
    #doc-preview {
        box-shadow: 0 24px 48px -12px rgba(135, 13, 22, .30), 0 8px 18px -8px rgba(0, 0, 0, .18);
        transform-origin: top left;
        animation: pvIn .18s ease-out;
    }
    @keyframes pvIn {
        from { opacity: 0; transform: translateY(6px) scale(.97); }
        to   { opacity: 1; transform: none; }
    }
    .pv-skeleton {
        background: linear-gradient(100deg, #f5f5f6 30%, #ffeaea 50%, #f5f5f6 70%);
        background-size: 200% 100%;
        animation: pvShimmer 1.3s infinite linear;
    }
    @keyframes pvShimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }
    .pv-page { box-shadow: 0 1px 2px rgba(0,0,0,.08), 0 4px 10px -4px rgba(0,0,0,.12); }
    .pv-img { opacity: 0; transition: opacity .3s ease; }
    .pv-img.loaded { opacity: 1; }
    @media (prefers-reduced-motion: reduce) {
        #doc-preview, .pv-skeleton { animation: none; }
        .pv-img { transition: none; }
    }

    /* ===== TAMPILAN JAWABAN AI (Markdown) - BARU ===== */
    .chat-md { line-height: 1.6; word-break: break-word; overflow-wrap: anywhere; }
    .chat-md > *:first-child { margin-top: 0; }
    .chat-md > *:last-child { margin-bottom: 0; }
    .chat-md p { margin: 0 0 1rem; }
    .chat-md strong { font-weight: 700; color: #111827; }
    .chat-md em { font-style: italic; }

    .chat-md h1, .chat-md h2, .chat-md h3, .chat-md h4 {
        font-weight: 700; color: #A8101A; line-height: 1.4;
        margin: 1.25rem 0 0.5rem;
    }
    .chat-md h1 { font-size: 1.1rem; }
    .chat-md h2 { font-size: 1.05rem; }
    .chat-md h3 { font-size: 0.98rem; }
    .chat-md h4 { font-size: 0.92rem; }

    .chat-md ul, .chat-md ol { margin: 0 0 1rem; padding-left: 0; list-style-position: inside; }
    .chat-md ul { list-style-type: disc; }
    .chat-md ol { list-style-type: decimal; }
    .chat-md li { margin: 0.1rem 0; }
    .chat-md li::marker { color: inherit; font-weight: 400; }
    .chat-md li > ul, .chat-md li > ol { padding-left: 1.5rem; }
    .chat-md li > p { margin: 0 0 0.35rem; }
    .chat-md li > ul, .chat-md li > ol { margin: 0.2rem 0 0.2rem; }
    .chat-md ul ul { list-style: circle; }

    .chat-md a {
        color: #A8101A; font-weight: 500;
        text-decoration: underline; text-underline-offset: 3px;
        text-decoration-color: #FFC4C7;
    }
    .chat-md a:hover { text-decoration-color: #A8101A; }

    .chat-md hr { border: 0; border-top: 1px solid #F3E4E5; margin: 1rem 0; }
    .chat-md blockquote {
        margin: 0.75rem 0; padding: 0.4rem 0.9rem;
        border-left: 3px solid #FFC4C7; background: #FFF3F3;
        color: #4B5563; border-radius: 0 0.5rem 0.5rem 0;
    }
    .chat-md code {
        background: #FFF3F3; color: #870D16; padding: 0.1rem 0.35rem;
        border-radius: 0.3rem; font-size: 0.85em;
    }
    .chat-md pre {
        background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 0.5rem;
        padding: 0.75rem; overflow-x: auto; margin: 0.75rem 0;
    }
    .chat-md pre code { background: transparent; padding: 0; color: inherit; }

    .chat-md table {
        display: block; overflow-x: auto; width: 100%;
        border-collapse: collapse; margin: 0.75rem 0; font-size: 0.85em;
    }
    .chat-md th, .chat-md td { border: 1px solid #E5E7EB; padding: 0.45rem 0.7rem; text-align: left; vertical-align: top; }
    .chat-md th { background: #FFF3F3; color: #870D16; font-weight: 600; }

    /* ===== TAMPILAN AWAL CHAT BARU (sapaan + kotak input di tengah) ===== */
    #welcome[hidden] { display: none; }
    #chat-area.is-empty { display: flex; flex-direction: column; }
    #chat-area.is-empty #messages-container { margin-top: auto; margin-bottom: auto; width: 100%; padding-bottom: 5rem; }
    #chat-form.hero-form {
        flex-direction: column; align-items: stretch;
        border-radius: 24px; padding: 14px 14px 12px;
        box-shadow: 0 10px 34px rgba(168, 16, 26, .12);
    }
    #chat-form.hero-form #message-input { flex: 0 0 auto; min-height: 76px; max-height: 220px; padding: 6px 8px; }
    #chat-form.hero-form #form-actions { align-self: flex-end; }
    #chat-form.hero-form #send-button, #chat-form.hero-form #mic-button { width: 44px; height: 44px; }

    /* ===== INPUT SUARA ===== */
    #mic-button {
        width: 50px; height: 50px; flex-shrink: 0; border-radius: 12px;
        display: grid; place-items: center; color: #C8161F;
        transition: background-color .15s ease, color .15s ease, transform .1s ease;
    }
    #mic-button:hover { background: #FFE1E2; }
    #mic-button:active { transform: scale(.95); }
    #mic-button.listening { background: #C8161F; color: #fff; animation: micPulse 1.4s infinite; }
    @keyframes micPulse {
        0%   { box-shadow: 0 0 0 0 rgba(200, 22, 31, .45); }
        70%  { box-shadow: 0 0 0 12px rgba(200, 22, 31, 0); }
        100% { box-shadow: 0 0 0 0 rgba(200, 22, 31, 0); }
    }
    @media (prefers-reduced-motion: reduce) { #mic-button.listening { animation: none; } }
    #voice-toast {
        position: fixed; left: 50%; bottom: 110px; transform: translateX(-50%); z-index: 60;
        max-width: min(92vw, 460px); background: #5E0A10; color: #fff; font-size: 15px;
        padding: 10px 16px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.2);
    }

    /* ===== TOMBOL AKSI PESAN: salin / edit / coba lagi ===== */
    .msg-actions { display: flex; align-items: center; gap: 2px; height: 32px; opacity: 0; transition: opacity .15s ease; }
    .msg-row:hover .msg-actions, .msg-actions:focus-within { opacity: 1; }
    @media (hover: none) { .msg-actions { opacity: 1; } }
    .act-btn {
        display: grid; place-items: center; width: 30px; height: 30px; border-radius: 8px;
        color: #9CA3AF; transition: color .15s ease, background-color .15s ease;
    }
    .act-btn:hover { color: #A8101A; background: #FFE1E2; }
    .act-btn.done { color: #15803D; }
    .act-btn:disabled { opacity: .4; cursor: not-allowed; }
    .edit-box textarea { field-sizing: content; min-height: 3rem; max-height: 16rem; }
</style>
</head>
<body class="h-[100dvh] bg-[#FBF6F5] text-gray-800 font-sans antialiased flex overflow-hidden">

    <!-- SIDEBAR RIWAYAT CHAT -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-[280px] -translate-x-full md:translate-x-0 md:static shrink-0 transition-transform duration-200 bg-[#A8101A] text-gray-100 flex flex-col">
        <!-- Logo + nama aplikasi -->
        <div class="px-4 pt-5 pb-4 flex items-center gap-3 border-b border-white/10">
            <div class="bg-white rounded-xl p-1.5 shrink-0 shadow-sm">
                <img src="data:image/png;base64,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" alt="Logo Telkom University" class="h-12 w-auto block">
            </div>
            <p class="text-[17px] font-bold leading-snug tracking-tight">Chatbot Asisten Akreditasi</p>
        </div>

        <div class="px-3 pt-3 space-y-1">
            <button type="button" id="new-chat" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 text-[16px] font-medium text-left transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                Buat baru
            </button>
            <button type="button" id="search-toggle" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 text-[16px] font-medium text-left transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                Cari
            </button>
            <div id="search-box" class="hidden px-1 pt-1">
                <input id="search-input" type="search" placeholder="Cari riwayat chat..." autocomplete="off" class="w-full bg-white/10 placeholder:text-gray-400 text-[15px] text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/30">
            </div>
        </div>

        <p class="mt-7 px-6 text-sm font-semibold text-gray-300">Riwayat chat</p>
        <nav id="history-list" class="flex-1 overflow-y-auto px-3 mt-2 pb-4 space-y-0.5"></nav>
    </aside>
    <div id="sidebar-backdrop" class="hidden fixed inset-0 z-30 bg-black/40 md:hidden"></div>

    <div class="flex-1 min-w-0 h-full flex flex-col">

    <!-- HEADER -->
    <header class="bg-brand-700 text-white shrink-0">
        <div class="max-w-[960px] mx-auto px-5 py-5 flex items-center gap-4">
            <button type="button" id="menu-toggle" aria-label="Buka riwayat chat" class="md:hidden -ml-2 p-2 rounded-lg hover:bg-white/10 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="w-[50px] h-[50px] rounded-xl bg-white text-brand-700 grid place-items-center shadow-sm shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8-1.05 0-2.06-.16-3-.46L3 21l1.5-4.5A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <div class="min-w-0">
                <h1 class="text-[22px] font-bold leading-tight tracking-tight">Asisten Akreditasi Prodi</h1>
                <p class="text-[15px] text-brand-100 truncate">Sistem Tanya Jawab Seputar Akreditasi Program Studi Sistem Informasi</p>
            </div>
        </div>
    </header>

    <!-- CHAT AREA -->
    <main id="chat-area" class="flex-1 overflow-y-auto">
        <div class="max-w-[960px] mx-auto px-5 py-8 space-y-6" id="messages-container">

            <!-- Sapaan awal (chat baru): kotak input dipindah ke sini lewat JS -->
            <div id="welcome" class="w-full flex flex-col items-center text-center">
                <div class="flex items-center justify-center gap-3">
                    <div class="w-11 h-11 shrink-0 rounded-full bg-brand-600 text-white grid place-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8-1.05 0-2.06-.16-3-.46L3 21l1.5-4.5A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <h2 class="text-[30px] md:text-[38px] font-semibold tracking-tight text-gray-800 leading-tight">Selamat datang, Bapak/Ibu Dosen </h2>
                </div>
                <p class="mt-3 text-[16px] md:text-[17px] text-gray-500 max-w-[560px]">Silakan ajukan pertanyaan seputar dokumen, indikator, atau standar akreditasi.</p>
                <div id="hero-slot" class="w-full max-w-[720px] mt-8 text-left"></div>
            </div>

        </div>
    </main>

    <!-- INPUT AREA -->
    <footer id="input-footer" class="shrink-0 px-4 pb-4 pt-3">
        <div id="footer-inner" class="max-w-[960px] mx-auto">
            <form id="chat-form" class="flex items-end gap-2 bg-white border border-brand-200 rounded-[22px] p-[10px] shadow-[0_6px_24px_rgba(168,16,26,0.10)] transition focus-within:border-brand-600 focus-within:ring-4 focus-within:ring-brand-100">
                <textarea
                    id="message-input"
                    rows="1"
                    maxlength="2000"
                    placeholder="Tulis pertanyaan seputar akreditasi di sini..."
                    class="flex-1 resize-none max-h-40 bg-transparent px-4 py-3 text-[18px] placeholder:text-gray-400 focus:outline-none"
                ></textarea>
                <div id="form-actions" class="flex items-center gap-1.5 shrink-0">
                <button
                    type="button"
                    id="mic-button"
                    aria-label="Mulai bicara"
                    title="Bicara (ketik dengan suara)"
                    aria-pressed="false"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-14 0m7 7v4m-4 0h8M12 3a3 3 0 00-3 3v5a3 3 0 006 0V6a3 3 0 00-3-3z"/>
                    </svg>
                </button>
                <button
                    type="submit"
                    id="send-button"
                    aria-label="Kirim pesan"
                    class="w-[50px] h-[50px] shrink-0 bg-brand-600 hover:bg-brand-700 active:scale-95 disabled:bg-brand-200 disabled:cursor-not-allowed text-white rounded-xl grid place-items-center transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                </button>
                </div>
            </form>
            <p id="footer-caption" class="text-sm text-gray-400 mt-3 text-center">Chatbot Asisten Akreditasi Sistem Informasi</p>
        </div>
    </footer>
    </div><!-- /konten -->

    <div id="voice-toast" class="hidden" role="status" aria-live="polite"></div>

    <!-- KOTAK PRATINJAU DOKUMEN (muncul saat kursor diarahkan ke link referensi) -->
    <div id="doc-preview" class="hidden fixed z-50 pointer-events-none bg-white border border-brand-100 rounded-2xl overflow-hidden" style="width:320px">
        <!-- Kepala: nama dokumen + jumlah halaman -->
        <div class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-brand-700 to-brand-500 text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span id="doc-preview-title" class="flex-1 min-w-0 truncate text-xs font-semibold tracking-wide">Dokumen</span>
            <span id="doc-preview-count" class="shrink-0 text-[10px] font-semibold bg-white/20 rounded-full px-2 py-0.5"></span>
        </div>
        <!-- Isi: gambar halaman -->
        <div class="p-2 bg-gradient-to-b from-brand-50/60 to-white">
            <div id="doc-preview-grid" class="hidden grid gap-2"></div>
            <img id="doc-preview-img" alt="Pratinjau halaman dokumen" class="w-full min-h-[120px] object-contain rounded-lg border border-gray-200 bg-gray-50 pv-page">
            <p id="doc-preview-msg" class="hidden text-xs text-gray-500 py-6 px-2 text-center">Pratinjau tidak tersedia. Klik link untuk membuka dokumen.</p>
        </div>
        <!-- Kaki: daftar halaman -->
        <p id="doc-preview-cap" class="text-[11px] text-gray-500 text-center py-1.5 px-2 border-t border-gray-100 bg-gray-50/80"></p>
    </div>

<script>
(function () {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const messagesContainer = document.getElementById('messages-container');
    const chatArea = document.getElementById('chat-area');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // ===== RIWAYAT CHAT (disimpan di localStorage browser) =====
    const STORE_KEY = 'akreditasi_chats';
    const MAX_CHATS = 100;
    function newId() { return 'sess-' + crypto.randomUUID(); }
    function loadChats() {
        try { const v = JSON.parse(localStorage.getItem(STORE_KEY)); return Array.isArray(v) ? v : []; }
        catch (e) { return []; }
    }
    function saveChats() {
        try { localStorage.setItem(STORE_KEY, JSON.stringify(chats)); } catch (e) { console.warn(e); }
    }
    let chats = loadChats();
    let currentId = newId();   // sekaligus dipakai sebagai sessionId ke n8n
    let searchQuery = '';
    let busy = false;   // true selama menunggu jawaban (mencegah edit/coba lagi ganda)

    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const historyList = document.getElementById('history-list');
    const searchBox = document.getElementById('search-box');
    const searchInput = document.getElementById('search-input');

    const AVATAR = '<div class="w-10 h-10 shrink-0 rounded-full bg-brand-600 text-white grid place-items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8-1.05 0-2.06-.16-3-.46L3 21l1.5-4.5A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>';

    function scrollToBottom() {
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    // ===== RENDER MARKDOWN (BARU) =====
    // Semua link hasil Markdown dibuka di tab baru
    if (window.DOMPurify) {
        DOMPurify.addHook('afterSanitizeAttributes', function (node) {
            if (node.tagName === 'A') {
                node.setAttribute('target', '_blank');
                node.setAttribute('rel', 'noopener noreferrer');
            }
        });
    }

    // Jika backend sudah mengirim daftar "sources" (chip Referensi),
    // bagian "Sumber"/"Referensi" di dalam teks Markdown dibuang supaya tidak dobel.
    function stripSourceSection(text) {
        return text.replace(/\n+(?:#{1,6}\s*)?\**\s*(?:Sumber|Referensi)\s*:?\s*\**\s*\n[\s\S]*$/i, '').trim();
    }

    // Kalau n8n menulis "Referensi" + link Google Drive langsung di dalam teks jawaban
    // (backend tidak mengenali sumbernya), ubah bagian itu jadi chip Referensi.
    function extractDriveRefs(text) {
        const m = text.match(/\n+(?:#{1,6}\s*)?\**\s*(?:Sumber|Referensi)\s*:?\s*\**\s*\n([\s\S]*)$/i);
        if (!m) return null;
        const sources = [];
        let cur = { label: '', pages: [] };
        m[1].split('\n').forEach(function (raw) {
            const url = raw.match(/https?:\/\/drive\.google\.com\/file\/d\/([\w-]+)[^\s)\]>]*/i);
            let line = raw.replace(/https?:\/\/\S+/g, ' ')
                          .replace(/\[([^\]]*)\]\(\s*\)/g, '$1')
                          .replace(/[*_`>#\[\]()-]+(?=\s|$)/g, ' ')
                          .trim().replace(/^[-*+\s\[]+/, '');
            if (line.length > 2) {
                const pm = line.match(/(?:hlm|halaman|hal)\.?\s*:?\s*(\d+(?:\s*[,&]\s*\d+)*)/i);
                cur = {
                    label: line.replace(/\s*[\(,].*$/, '').replace(/\.pdf$/i, '')
                               .replace(/^\d+(?:[_\s]\d+)*[_\s]+/, '').replace(/_/g, ' ').trim(),
                    pages: pm ? pm[1].split(/\D+/).filter(Boolean).map(Number) : []
                };
            }
            if (url) {
                const pages = cur.pages.length ? cur.pages : [1];
                sources.push({
                    label: cur.label || 'Dokumen',
                    page: pages.join(', '),
                    pages: pages,
                    fileId: url[1],
                    url: 'https://drive.google.com/file/d/' + url[1] + '/view#page=' + pages[0],
                    fallback: 'https://drive.google.com/thumbnail?id=' + url[1] + '&sz=w600'
                });
                cur = { label: '', pages: [] };
            }
        });
        if (!sources.length) return null;
        return { text: text.slice(0, m.index).trim(), sources: sources };
    }

    // Chip dari backend hanya membawa 1 halaman + tanpa ID file (preview jadi cover).
    // Di sini: ambil ID file dari URL, baca SEMUA halaman dari teks jawaban
    // ("Halaman: 7, 11, 12"), dan gabungkan jadi satu chip per dokumen.
    function enrichSources(sources, text) {
        const byId = {};
        const out = [];
        sources.forEach(function (s) {
            const idm = s.fileId ? [null, s.fileId] : String(s.url || '').match(/\/file\/d\/([\w-]+)/);
            if (!idm) { out.push(s); return; }
            const id = idm[1];
            let pages = s.pages ? s.pages.slice() : String(s.page).split(/\D+/).filter(Boolean).map(Number);
            if (!s.pages && s.doc) {
                const name = s.doc.replace(/[^A-Za-z0-9]+/g, '[_\\s]+');
                const re = new RegExp(name + '(?:\\.pdf)?[^()]*\\([^)]*?(?:Halaman|hlm\\.?)\\s*:?\\s*(\\d+(?:\\s*(?:,|&|dan|-)\\s*\\d+)*)', 'i');
                const m = text.match(re);
                if (m) pages = pages.concat(m[1].split(/\D+/).filter(Boolean).map(Number));
            }
            if (!byId[id]) {
                byId[id] = Object.assign({}, s, { fileId: id, pages: [] });
                out.push(byId[id]);
            }
            byId[id].pages = byId[id].pages.concat(pages);
        });
        out.forEach(function (s) {
            if (!s.pages) return;
            s.pages = Array.from(new Set(s.pages.filter(function (n) { return n > 0; }))).sort(function (a, b) { return a - b; });
            if (!s.pages.length) s.pages = [1];
            s.page = s.pages.join(', ');
            s.url = 'https://drive.google.com/file/d/' + s.fileId + '/view#page=' + s.pages[0];
            s.fallback = 'https://drive.google.com/thumbnail?id=' + s.fileId + '&sz=w600';
        });
        return out;
    }

    function renderMarkdown(text) {
        if (!window.marked || !window.DOMPurify) return null; // CDN gagal dimuat -> pakai teks biasa
        const html = marked.parse(text, { gfm: true, breaks: true });
        return DOMPurify.sanitize(html);
    }

    // ===== LINK DRIVE DI DALAM JAWABAN -> PRATINJAU HALAMAN (BARU) =====
    // Cari link Google Drive, ambil nomor halaman dari teks "hlm. 4, 7, 11"
    function enhanceDriveLinks(root) {
        root.querySelectorAll('a[href*="drive.google.com/file/d/"]').forEach(function (a) {
            const m = a.href.match(/\/file\/d\/([\w-]+)/);
            if (!m) return;
            const holder = a.closest('li, p');
            const ctx = a.textContent + ' ' + (holder ? holder.textContent : '');
            const pm = ctx.match(/(?:hlm|halaman|hal)\.?\s*(\d+(?:\s*[,&-]\s*\d+)*)/i);
            const pages = pm ? pm[1].split(/\D+/).filter(Boolean).map(Number) : [1];
            a.classList.add('ref-link');
            a.dataset.fileId = m[1];
            a.dataset.page = String(pages[0]);
            a.dataset.pages = pages.join(', ');
            a.dataset.fallback = 'https://drive.google.com/thumbnail?id=' + m[1] + '&sz=w600';
        });
    }

    // Render 1 halaman PDF menjadi gambar (di-cache supaya hover berikutnya instan)
    const pdfDocs = {};
    const pageImages = {};
    async function renderPdfPage(fileId, page) {
        const key = fileId + ':' + page;
        if (pageImages[key]) return pageImages[key];
        if (!window.pdfjsLib) throw new Error('pdf.js tidak tersedia');
        if (!pdfDocs[fileId]) {
            pdfDocs[fileId] = pdfjsLib.getDocument('/api/chat/doc/' + fileId).promise;
        }
        const pdf = await pdfDocs[fileId];
        const num = Math.min(Math.max(page, 1), pdf.numPages);
        const pg = await pdf.getPage(num);
        const base = pg.getViewport({ scale: 1 });
        const viewport = pg.getViewport({ scale: 640 / base.width });
        const canvas = document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        await pg.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise;
        pageImages[key] = canvas.toDataURL('image/jpeg', 0.85);
        return pageImages[key];
    }

    // ===== TOMBOL AKSI: SALIN / EDIT / COBA LAGI =====
    const ICON = {
        copy:  'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z',
        check: 'M5 13l4 4L19 7',
        edit:  'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z',
        retry: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'
    };
    function iconSvg(path) {
        return '<svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="' + path + '"/></svg>';
    }
    function actionBtn(label, iconPath, handler) {
        const b = document.createElement('button');
        b.type = 'button';
        b.title = label;
        b.setAttribute('aria-label', label);
        b.className = 'act-btn';
        b.innerHTML = iconSvg(iconPath);
        b.addEventListener('click', function (e) { handler(e, b); });
        return b;
    }

    async function copyText(text) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (e) {
            // cadangan untuk http / browser lama
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0;';
            document.body.appendChild(ta);
            ta.select();
            let ok = false;
            try { ok = document.execCommand('copy'); } catch (_) {}
            ta.remove();
            return ok;
        }
    }
    function copyButton(getText) {
        return actionBtn('Salin', ICON.copy, async function (e, btn) {
            const ok = await copyText(getText());
            if (!ok) return;
            btn.classList.add('done');
            btn.title = 'Tersalin';
            btn.innerHTML = iconSvg(ICON.check);
            setTimeout(function () {
                btn.classList.remove('done');
                btn.title = 'Salin';
                btn.innerHTML = iconSvg(ICON.copy);
            }, 1500);
        });
    }

    // Kirim ulang pertanyaan ke-userIdx: jawaban/pesan setelahnya dibuang,
    // lalu dijawab ulang. Jika newText diisi, pertanyaan diganti (dipakai oleh Edit).
    function resendFrom(userIdx, newText) {
        if (busy) return;
        const c = getChat(currentId);
        if (!c) return;
        const m = c.messages[userIdx];
        if (!m || m.role !== 'user') return;
        if (typeof newText === 'string') {
            m.text = newText;
            if (userIdx === 0) c.title = newText.replace(/\s+/g, ' ').slice(0, 60);
        }
        c.messages.length = userIdx + 1;
        c.updated = Date.now();
        chats.sort(function (x, y) { return y.updated - x.updated; });
        saveChats();
        renderHistory();
        renderChat(c);
        sendMessage(m.text);
    }

    function lastUserIndex(c) {
        for (let i = c.messages.length - 1; i >= 0; i--) {
            if (c.messages[i].role === 'user') return i;
        }
        return -1;
    }

    function retryAi(idx) {
        const c = getChat(currentId);
        if (!c) return;
        // idx null = pesan gangguan koneksi (tidak tersimpan) -> ulangi pertanyaan terakhir
        const u = (idx === null || idx === undefined) ? lastUserIndex(c) : idx - 1;
        if (u >= 0) resendFrom(u);
    }

    function startEdit(wrapper, idx) {
        if (busy) return;
        const c = getChat(currentId);
        if (!c || !c.messages[idx]) return;
        const original = c.messages[idx].text;

        wrapper.innerHTML = '';
        const box = document.createElement('div');
        box.className = 'edit-box w-full max-w-[80%] bg-white border border-brand-200 rounded-2xl p-3 shadow-sm focus-within:border-brand-600 focus-within:ring-4 focus-within:ring-brand-100 transition';

        const ta = document.createElement('textarea');
        ta.className = 'w-full resize-none bg-transparent px-2 py-1 text-[18px] leading-[1.6] focus:outline-none';
        ta.maxLength = 2000;
        ta.value = original;

        const row = document.createElement('div');
        row.className = 'flex justify-end gap-2 mt-2';

        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.textContent = 'Batal';
        cancel.className = 'px-4 py-2 rounded-lg text-[15px] font-medium text-gray-600 hover:bg-gray-100 transition-colors';

        const save = document.createElement('button');
        save.type = 'button';
        save.textContent = 'Kirim';
        save.className = 'px-4 py-2 rounded-lg text-[15px] font-semibold text-white bg-brand-600 hover:bg-brand-700 disabled:bg-brand-200 disabled:cursor-not-allowed transition-colors';

        function submitEdit() {
            const v = ta.value.trim();
            if (!v) return;
            if (v === original.trim()) { renderChat(getChat(currentId)); return; }
            resendFrom(idx, v);
        }
        cancel.addEventListener('click', function () { renderChat(getChat(currentId)); });
        save.addEventListener('click', submitEdit);
        ta.addEventListener('input', function () { save.disabled = !ta.value.trim(); });
        ta.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); submitEdit(); }
            else if (e.key === 'Escape') { e.preventDefault(); renderChat(getChat(currentId)); }
        });

        row.appendChild(cancel);
        row.appendChild(save);
        box.appendChild(ta);
        box.appendChild(row);
        wrapper.appendChild(box);
        ta.focus();
        ta.setSelectionRange(ta.value.length, ta.value.length);
    }

    function appendUserMessage(text, idx) {
        const wrapper = document.createElement('div');
        wrapper.className = 'msg-row flex flex-col items-end';
        wrapper.innerHTML = `
            <div class="max-w-[80%] bg-brand-600 text-white rounded-2xl px-5 py-3 shadow-sm text-[18px] font-medium leading-[1.6] whitespace-pre-wrap"></div>
        `;
        wrapper.firstElementChild.textContent = text;

        // tombol aksi hanya untuk pesan yang sudah tersimpan (punya indeks)
        if (typeof idx === 'number') {
            const actions = document.createElement('div');
            actions.className = 'msg-actions mt-1';
            actions.appendChild(copyButton(function () { return text; }));
            actions.appendChild(actionBtn('Edit', ICON.edit, function () { startEdit(wrapper, idx); }));
            actions.appendChild(actionBtn('Coba lagi', ICON.retry, function () { resendFrom(idx); }));
            wrapper.appendChild(actions);
        }

        messagesContainer.appendChild(wrapper);
        updateEmptyState();
        scrollToBottom();
    }

    function appendAiMessage(text, sources, idx) {
        let hasSources = Array.isArray(sources) && sources.length > 0;
        if (!hasSources) {
            const parsed = extractDriveRefs(text);
            if (parsed) { text = parsed.text; sources = parsed.sources; hasSources = true; }
        }
        if (hasSources) sources = enrichSources(sources, text);
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-start gap-4';
        wrapper.innerHTML = AVATAR + `
            <div class="msg-row min-w-0 max-w-[85%]">
                <div class="bubble min-w-0 bg-white border border-gray-200/70 text-gray-800 rounded-[20px] px-5 py-4 shadow-sm text-[18px]">
                    <div class="msg-text chat-md"></div>
                </div>
            </div>
        `;
        const column = wrapper.lastElementChild;
        const bubble = column.firstElementChild;
        const target = bubble.querySelector('.msg-text');

        const cleanText = hasSources ? stripSourceSection(text) : text;
        const html = renderMarkdown(cleanText);
        if (html !== null) {
            target.innerHTML = html;
            enhanceDriveLinks(target);
        } else {
            target.classList.add('whitespace-pre-wrap');
            target.textContent = cleanText;
        }

        // Kalau ada referensi dokumen, tampilkan di bawah jawaban
        if (hasSources) {
            bubble.appendChild(buildReferences(sources));
        }

        // Tombol aksi: salin + coba lagi
        let copyPayload = cleanText;
        if (hasSources) {
            copyPayload += '\n\nReferensi:\n' + sources.map(function (s) {
                return '- ' + s.label + ', hlm. ' + s.page + ' (' + s.url + ')';
            }).join('\n');
        }
        const actions = document.createElement('div');
        actions.className = 'msg-actions mt-1';
        actions.appendChild(copyButton(function () { return copyPayload; }));
        actions.appendChild(actionBtn('Coba lagi', ICON.retry, function () { retryAi(idx); }));
        column.appendChild(actions);

        messagesContainer.appendChild(wrapper);
        updateEmptyState();
        scrollToBottom();
    }

    // ===== REFERENSI DOKUMEN + PRATINJAU SAAT HOVER =====
    const DOC_ICON = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';

    function buildReferences(sources) {
        const box = document.createElement('div');
        box.className = 'mt-4 pt-4 border-t border-gray-100';

        const title = document.createElement('p');
        title.className = 'text-[15px] font-semibold text-brand-700 mb-2.5';
        title.textContent = 'Referensi';
        box.appendChild(title);

        const list = document.createElement('div');
        list.className = 'flex flex-wrap gap-2';

        sources.forEach(function (s) {
            const a = document.createElement('a');
            a.href = s.url;
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
            a.className = 'ref-link inline-flex items-center gap-2 bg-brand-50 border border-brand-100 hover:bg-brand-100 hover:border-brand-200 text-brand-800 rounded-full pl-3 pr-4 py-2 text-[15px] font-medium transition-colors no-underline';
            a.dataset.preview = s.preview || '';
            a.dataset.fallback = s.fallback || '';
            a.dataset.page = s.page;
            // pratinjau digambar langsung dari PDF untuk SEMUA halaman yang dirujuk
            const fid = s.fileId || ((String(s.url || '').match(/\/file\/d\/([\w-]+)/) || [])[1]);
            if (fid) {
                a.dataset.fileId = fid;
                const pgs = (s.pages && s.pages.length ? s.pages : String(s.page).split(/\D+/).filter(Boolean).map(Number));
                a.dataset.pages = pgs.join(', ');
            }
            a.insertAdjacentHTML('afterbegin', DOC_ICON);
            a.appendChild(document.createTextNode(s.label + ', hlm. ' + s.page));
            list.appendChild(a);
        });

        box.appendChild(list);
        return box;
    }

    const preview = document.getElementById('doc-preview');
    const previewImg = document.getElementById('doc-preview-img');
    const previewMsg = document.getElementById('doc-preview-msg');
    const previewCap = document.getElementById('doc-preview-cap');
    const previewGrid = document.getElementById('doc-preview-grid');
    const previewTitle = document.getElementById('doc-preview-title');
    const previewCount = document.getElementById('doc-preview-count');

    // "1_5_Standar_Dosen_Tendik (2020), hlm. 7" -> "Standar Dosen Tendik"
    function docTitle(link) {
        let t = (link.textContent || '').replace(/\s*[\(,].*$/, '').trim();
        t = t.replace(/\.pdf$/i, '').replace(/^\d+(?:_\d+)*_/, '').replace(/_/g, ' ').trim();
        return t || 'Dokumen';
    }
    let currentLink = null;
    let lastMouse = { x: 0, y: 0 };

    // Atur ukuran kotak pratinjau. Satu halaman = 320 px. Beberapa halaman disusun dalam
    // grid (kolom x baris) dan ukuran gambar diperkecil otomatis supaya SEMUA halaman
    // muat di layar (kotak pratinjau tidak bisa di-scroll karena mengikuti kursor).
    function layoutPreview(n) {
        if (n <= 1) {
            preview.style.width = '320px';
            previewGrid.style.gridTemplateColumns = '';
            return;
        }
        const GAP = 8, PAD = 16, RATIO = 1.414, LABEL = 0, CAPTION = 72, MAXW = 230, MINW = 40; // CAPTION = tinggi kepala + kaki kotak
        const availW = window.innerWidth - 32;
        const availH = window.innerHeight - 32;

        let best = null;
        for (let cols = 1; cols <= n; cols++) {
            const rows = Math.ceil(n / cols);
            let w = Math.min(MAXW, Math.floor((availW - PAD - (cols - 1) * GAP) / cols));
            const maxWByHeight = Math.floor(((availH - PAD - CAPTION) / rows - GAP - LABEL) / RATIO);
            w = Math.min(w, maxWByHeight);
            // ukuran sama -> pilih yang berdampingan (sampai 3 kolom) daripada menumpuk ke bawah
            if (w >= MINW && (!best || w > best.w || (w === best.w && cols <= 3))) best = { cols: cols, w: w };
        }
        if (!best) {
            // terlalu banyak halaman untuk layar ini: pakai ukuran minimum
            const cols = Math.max(1, Math.floor((availW - PAD + GAP) / (MINW + GAP)));
            best = { cols: Math.min(cols, n), w: MINW };
        }

        previewGrid.style.gridTemplateColumns = 'repeat(' + best.cols + ', ' + best.w + 'px)';
        preview.style.width = (best.cols * best.w + (best.cols - 1) * GAP + PAD) + 'px';
    }

    function showPreview(link) {
        currentLink = link;

        // reset tampilan
        previewTitle.textContent = docTitle(link);
        previewCount.textContent = '';
        previewGrid.innerHTML = '';
        previewGrid.classList.add('hidden');
        layoutPreview(1);

        function showError() {
            if (currentLink !== link) return;
            previewGrid.classList.add('hidden');
            previewImg.classList.add('hidden');
            previewMsg.textContent = 'Pratinjau tidak tersedia. Klik link untuk membuka dokumen.';
            previewMsg.classList.remove('hidden');
            previewCap.textContent = '';
            positionPreview();
        }

        function useFallback() {
            if (currentLink !== link) return;
            if (!link.dataset.fallback) { showError(); return; }
            previewGrid.classList.add('hidden');
            layoutPreview(1);
            previewImg.dataset.stage = 'fallback';
            previewImg.onerror = showError;
            previewMsg.classList.add('hidden');
            previewImg.classList.remove('hidden');
            previewCap.textContent = 'Pratinjau halaman 1 dokumen';
            previewCount.textContent = 'Hlm. 1';
            previewImg.src = link.dataset.fallback;
        }

        // Link dari jawaban AI: render SEMUA halaman yang dirujuk langsung dari PDF
        if (link.dataset.fileId && window.pdfjsLib) {
            const fileId = link.dataset.fileId;
            // semua halaman yang dirujuk (tanpa duplikat, berurutan)
            const pages = Array.from(new Set(
                (link.dataset.pages || link.dataset.page || '1')
                    .split(', ').map(Number).filter(function (n) { return n > 0; })
            )).sort(function (x, y) { return x - y; });

            layoutPreview(pages.length);
            previewImg.classList.add('hidden');
            previewMsg.classList.add('hidden');
            previewGrid.classList.remove('hidden');
            previewCap.textContent = 'Halaman ' + pages.join(', ');
            previewCount.textContent = pages.length > 1 ? pages.length + ' halaman' : 'Hlm. ' + pages[0];
            preview.classList.remove('hidden');

            let failed = 0;
            pages.forEach(function (pageNo) {
                const cell = document.createElement('figure');
                cell.className = 'min-w-0 m-0';

                const holder = document.createElement('div');
                holder.className = 'pv-page pv-skeleton relative w-full rounded-lg border border-gray-200 overflow-hidden grid place-items-center text-[11px] text-gray-400';
                holder.style.aspectRatio = '1 / 1.414';
                holder.textContent = 'Memuat...';

                // lencana nomor halaman di pojok kiri atas
                const badge = document.createElement('span');
                badge.className = 'absolute top-1.5 left-1.5 z-10 bg-brand-600 text-white text-[10px] font-bold leading-none px-1.5 py-1 rounded-full shadow';
                badge.textContent = 'Hlm. ' + pageNo;

                cell.appendChild(holder);
                previewGrid.appendChild(cell);

                renderPdfPage(fileId, pageNo).then(function (url) {
                    if (currentLink !== link) return;
                    const img = document.createElement('img');
                    img.className = 'pv-img w-full block';
                    img.onload = function () {
                        img.classList.add('loaded');
                        positionPreview();
                    };
                    img.src = url;
                    holder.classList.remove('pv-skeleton', 'grid', 'place-items-center');
                    holder.classList.add('bg-white');
                    holder.style.aspectRatio = '';
                    holder.textContent = '';
                    holder.appendChild(img);
                    holder.appendChild(badge);
                    positionPreview();
                }).catch(function (err) {
                    console.warn('Pratinjau PDF hlm. ' + pageNo + ' gagal:', err);
                    failed++;
                    if (currentLink !== link) return;
                    holder.classList.remove('pv-skeleton');
                    holder.classList.add('bg-gray-50');
                    holder.textContent = 'Tidak tersedia';
                    holder.appendChild(badge);
                    // semua halaman gagal -> pakai thumbnail Drive (halaman 1)
                    if (failed === pages.length) useFallback();
                });
            });
            return;
        }

        // Chip "Referensi" dari backend (perilaku lama, satu gambar)
        previewCap.textContent = 'Halaman ' + link.dataset.page;
        previewCount.textContent = 'Hlm. ' + link.dataset.page;
        previewImg.classList.remove('hidden');
        previewMsg.classList.add('hidden');
        previewImg.onerror = function () {
            if (previewImg.dataset.stage === 'page' && link.dataset.fallback) {
                previewImg.dataset.stage = 'fallback';
                previewCap.textContent = 'Pratinjau halaman 1 dokumen';
                previewImg.src = link.dataset.fallback;
            } else {
                showError();
            }
        };
        previewImg.dataset.stage = 'page';
        previewImg.src = link.dataset.preview || link.dataset.fallback;
        preview.classList.remove('hidden');
    }

    function hidePreview() {
        currentLink = null;
        preview.classList.add('hidden');
    }

    // Posisi mengikuti kursor; juga dipanggil ulang setelah gambar selesai dirender (tinggi berubah)
    function positionPreview() {
        const gap = 16;
        const w = preview.offsetWidth;
        const h = preview.offsetHeight;
        let x = lastMouse.x + gap;
        let y = lastMouse.y + gap;
        if (x + w > window.innerWidth - 8) x = lastMouse.x - w - gap;
        if (y + h > window.innerHeight - 8) y = window.innerHeight - h - 8;
        preview.style.left = Math.max(8, x) + 'px';
        preview.style.top = Math.max(8, y) + 'px';
    }

    function movePreview(e) {
        lastMouse = { x: e.clientX, y: e.clientY };
        positionPreview();
    }

    function getChat(id) { return chats.find(function (c) { return c.id === id; }); }

    function addToChat(id, msg, titleHint) {
        let c = getChat(id);
        if (!c) {
            c = { id: id, title: (titleHint || 'Percakapan baru').replace(/\s+/g, ' ').slice(0, 60), updated: Date.now(), messages: [] };
            chats.unshift(c);
        }
        c.messages.push(msg);
        c.updated = Date.now();
        chats.sort(function (a, b) { return b.updated - a.updated; });
        if (chats.length > MAX_CHATS) chats.length = MAX_CHATS;
        saveChats();
        renderHistory();
        return c.messages.length - 1;
    }

    function clearMessages() {
        Array.from(messagesContainer.children).forEach(function (el) {
            if (el.id !== 'welcome') el.remove();
        });
        updateEmptyState();
    }

    // Gambar ulang seluruh percakapan dari data tersimpan
    function renderChat(c) {
        clearMessages();
        c.messages.forEach(function (m, i) {
            if (m.role === 'user') appendUserMessage(m.text, i);
            else appendAiMessage(m.text, m.sources, i);
        });
        updateEmptyState();
        scrollToBottom();
    }

    // Chat kosong -> sapaan + kotak input di tengah (seperti Claude).
    // Sudah ada pesan -> kotak input kembali ke bawah.
    const welcomeEl = document.getElementById('welcome');
    const heroSlot = document.getElementById('hero-slot');
    const inputFooter = document.getElementById('input-footer');
    const footerInner = document.getElementById('footer-inner');
    const footerCaption = document.getElementById('footer-caption');
    function updateEmptyState() {
        const empty = !Array.from(messagesContainer.children).some(function (el) { return el.id !== 'welcome'; });
        chatArea.classList.toggle('is-empty', empty);
        welcomeEl.hidden = !empty;
        inputFooter.classList.toggle('hidden', empty);
        form.classList.toggle('hero-form', empty);
        const target = empty ? heroSlot : footerInner;
        if (form.parentNode !== target) {
            if (empty) heroSlot.appendChild(form);
            else footerInner.insertBefore(form, footerCaption);
        }
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    }

    function newChat() {
        stopListening(true);
        currentId = newId();
        clearMessages();
        renderHistory();
        closeSidebar();
        chatArea.scrollTop = 0;
        input.focus();
    }

    function openChat(id) {
        stopListening(true);
        const c = getChat(id);
        if (!c) return;
        currentId = id;
        renderChat(c);
        renderHistory();
        closeSidebar();
    }

    function deleteChat(id) {
        chats = chats.filter(function (c) { return c.id !== id; });
        saveChats();
        if (id === currentId) newChat(); else renderHistory();
    }

    function renderHistory() {
        const q = searchQuery.trim().toLowerCase();
        const list = chats.filter(function (c) {
            if (!q) return true;
            return c.title.toLowerCase().includes(q) ||
                c.messages.some(function (m) { return String(m.text).toLowerCase().includes(q); });
        });

        historyList.innerHTML = '';
        if (!list.length) {
            const p = document.createElement('p');
            p.className = 'px-3 py-2 text-sm text-gray-400';
            p.textContent = q ? 'Tidak ada hasil.' : 'Belum ada riwayat.';
            historyList.appendChild(p);
            return;
        }

        list.forEach(function (c) {
            const row = document.createElement('div');
            row.className = 'group relative flex items-center rounded-lg transition-colors ' +
                (c.id === currentId ? 'bg-white/15' : 'hover:bg-white/10');

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.title = c.title;
            btn.className = 'flex-1 min-w-0 text-left truncate px-3 py-2.5 text-[15px]';
            btn.textContent = c.title;
            btn.addEventListener('click', function () { openChat(c.id); });

            const del = document.createElement('button');
            del.type = 'button';
            del.setAttribute('aria-label', 'Hapus riwayat');
            del.title = 'Hapus';
            del.className = 'absolute right-1.5 p-1.5 rounded-md text-gray-300 hover:text-white hover:bg-white/15 opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity';
            del.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>';
            del.addEventListener('click', function (e) {
                e.stopPropagation();
                if (confirm('Hapus riwayat chat ini?')) deleteChat(c.id);
            });

            row.appendChild(btn);
            row.appendChild(del);
            historyList.appendChild(row);
        });
    }

    document.getElementById('new-chat').addEventListener('click', newChat);
    document.getElementById('search-toggle').addEventListener('click', function () {
        const hidden = searchBox.classList.toggle('hidden');
        if (hidden) { searchQuery = ''; searchInput.value = ''; renderHistory(); }
        else searchInput.focus();
    });
    searchInput.addEventListener('input', function () { searchQuery = searchInput.value; renderHistory(); });
    document.getElementById('menu-toggle').addEventListener('click', function () {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
    });
    backdrop.addEventListener('click', closeSidebar);
    renderHistory();

    // ===== INPUT SUARA (Web Speech API, bahasa Indonesia) =====
    const micButton = document.getElementById('mic-button');
    const voiceToast = document.getElementById('voice-toast');
    const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
    const defaultPlaceholder = input.placeholder;
    let recognition = null;
    let listening = false;
    let toastTimer = null;

    function showToast(msg) {
        voiceToast.textContent = msg;
        voiceToast.classList.remove('hidden');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { voiceToast.classList.add('hidden'); }, 4500);
    }

    function setListeningUI(on) {
        listening = on;
        micButton.classList.toggle('listening', on);
        micButton.setAttribute('aria-pressed', on ? 'true' : 'false');
        micButton.setAttribute('aria-label', on ? 'Hentikan rekaman' : 'Mulai bicara');
        micButton.title = on ? 'Hentikan rekaman' : 'Bicara (ketik dengan suara)';
        input.placeholder = on ? 'Mendengarkan... silakan bicara' : defaultPlaceholder;
    }

    // abort = true -> buang sisa hasil (dipakai saat pesan dikirim / pindah chat)
    function stopListening(abort) {
        if (!recognition) return;
        const r = recognition;
        if (abort) { r.onresult = null; r.onerror = null; }
        try { abort ? r.abort() : r.stop(); } catch (e) {}
        if (abort) { recognition = null; setListeningUI(false); }
    }

    function startListening() {
        const rec = new SpeechRec();
        rec.lang = 'id-ID';
        rec.continuous = true;
        rec.interimResults = true;
        rec.maxAlternatives = 1;

        // teks yang sudah ada di kotak dipertahankan, hasil suara ditambahkan di belakangnya
        let base = input.value;
        if (base && !/\s$/.test(base)) base += ' ';

        rec.onresult = function (ev) {
            let finalText = '', interim = '';
            for (let i = 0; i < ev.results.length; i++) {
                const t = ev.results[i][0].transcript;
                if (ev.results[i].isFinal) finalText += t; else interim += t;
            }
            input.value = (base + finalText + interim).slice(0, 2000);
            input.scrollTop = input.scrollHeight;
        };
        rec.onerror = function (ev) {
            if (ev.error === 'not-allowed' || ev.error === 'service-not-allowed') {
                showToast('Izin mikrofon ditolak. Izinkan mikrofon di pengaturan browser, lalu coba lagi.');
            } else if (ev.error === 'audio-capture') {
                showToast('Mikrofon tidak ditemukan. Periksa perangkat Anda.');
            } else if (ev.error === 'network') {
                showToast('Pengenalan suara membutuhkan koneksi internet.');
            } else if (ev.error === 'no-speech') {
                showToast('Suara tidak terdengar. Coba bicara lebih dekat ke mikrofon.');
            }
        };
        rec.onend = function () {
            if (recognition === rec) recognition = null;
            setListeningUI(false);
            input.focus();
        };

        recognition = rec;
        try {
            rec.start();
            setListeningUI(true);
        } catch (e) {
            recognition = null;
            setListeningUI(false);
        }
    }

    if (!SpeechRec) {
        // browser tidak mendukung (mis. Firefox) -> sembunyikan tombol
        micButton.style.display = 'none';
    } else {
        micButton.addEventListener('click', function () {
            if (listening) stopListening(false); else startListening();
        });
        // kalau pengguna mengetik manual saat merekam, hentikan rekaman agar tidak saling menimpa
        input.addEventListener('input', function () { if (listening) stopListening(false); });
    }

    function showTypingIndicator() {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-start gap-4';
        wrapper.id = 'typing-indicator';
        wrapper.innerHTML = AVATAR + `
            <div class="bg-white border border-gray-200/70 rounded-[20px] px-5 py-[18px] shadow-sm flex items-center gap-1.5">
                <span class="typing-dot w-2 h-2 bg-brand-500 rounded-full inline-block"></span>
                <span class="typing-dot w-2 h-2 bg-brand-500 rounded-full inline-block"></span>
                <span class="typing-dot w-2 h-2 bg-brand-500 rounded-full inline-block"></span>
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
        const convId = currentId;
        busy = true;
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
                    sessionId: convId,
                }),
            });

            const data = await response.json();
            const reply = data.reply || 'Maaf, tidak ada respons dari asisten.';
            const aiIdx = addToChat(convId, { role: 'ai', text: reply, sources: data.sources || [] });
            removeTypingIndicator();
            if (convId === currentId) appendAiMessage(reply, data.sources, aiIdx);

        } catch (error) {
            removeTypingIndicator();
            if (convId === currentId) appendAiMessage('Terjadi gangguan koneksi. Silakan periksa jaringan Anda dan coba lagi.', [], null);
            console.error(error);
        } finally {
            busy = false;
            sendButton.disabled = false;
            input.focus();
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        stopListening(true);
        const userIdx = addToChat(currentId, { role: 'user', text: text }, text);
        appendUserMessage(text, userIdx);
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

    // Pratinjau hanya untuk perangkat dengan mouse (di HP tidak ada hover)
    if (window.matchMedia('(hover: hover)').matches) {
        messagesContainer.addEventListener('mouseover', function (e) {
            const link = e.target.closest('.ref-link');
            if (!link || link === currentLink) return;
            showPreview(link);
            movePreview(e);
        });
        messagesContainer.addEventListener('mousemove', function (e) {
            if (currentLink) movePreview(e);
        });
        messagesContainer.addEventListener('mouseout', function (e) {
            const link = e.target.closest('.ref-link');
            if (link && !link.contains(e.relatedTarget)) hidePreview();
        });
        messagesContainer.addEventListener('click', hidePreview);
        chatArea.addEventListener('scroll', hidePreview);
    }

    updateEmptyState();
    input.focus();
})();
</script>

</body>
</html>