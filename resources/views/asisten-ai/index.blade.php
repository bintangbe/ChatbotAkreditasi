@extends('layouts.app')

@section('title', 'Tanya Asisten AI')
@section('header_width', 'max-w-[960px]')

@push('head')
<!-- Render Markdown + sanitasi -->
<script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>
<!-- PDF.js: menggambar halaman PDF untuk pratinjau saat hover -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    if (window.pdfjsLib) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }
</script>
<style>
@verbatim
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

    /* Layar kecil: kotak input & sapaan lebih ringkas */
    @media (max-width: 639px) {
        #chat-form.hero-form #message-input { min-height: 64px; }
        #mic-button, #send-button { width: 44px !important; height: 44px !important; }
        #message-input { font-size: 16px !important; }
        #chat-area.is-empty #messages-container { padding-bottom: 2rem; }
    }
@endverbatim
</style>
@endpush

{{-- HEADER: judul + panel batasan (dev.md §8.4) --}}
@section('header')
    <div class="hidden sm:grid w-[50px] h-[50px] rounded-xl bg-white text-brand-700 place-items-center shadow-sm shrink-0">
        @include('layouts.partials.icon', ['name' => 'chat', 'class' => 'h-6 w-6'])
    </div>
    <div class="min-w-0 flex-1">
        <h1 class="text-[19px] sm:text-[22px] font-bold leading-tight tracking-tight truncate">Asisten Akreditasi Prodi</h1>
        <p class="text-[13px] sm:text-[15px] text-brand-100 truncate">Tanya jawab seputar isi dokumen standar akreditasi</p>
    </div>
    <button type="button" id="info-toggle" aria-expanded="false" aria-controls="info-panel"
            class="shrink-0 flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-sm font-medium hover:bg-white/10 border border-white/20">
        @include('layouts.partials.icon', ['name' => 'info', 'class' => 'h-5 w-5'])
        <span class="hidden md:inline">Cakupan</span>
    </button>
@endsection

{{-- Riwayat chat: tampil di sidebar, tepat di bawah menu "Tanya Asisten AI" --}}
@section('sidebar_extra')
    <div class="mt-1 mb-1 ml-[22px] pl-3 border-l border-white/15">
        <button type="button" id="new-chat" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-white/10 text-[14.5px] text-left transition-colors">
            @include('layouts.partials.icon', ['name' => 'plus', 'class' => 'h-4 w-4 shrink-0'])
            Buat baru
        </button>
        <button type="button" id="search-toggle" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-white/10 text-[14.5px] text-left transition-colors">
            @include('layouts.partials.icon', ['name' => 'search', 'class' => 'h-4 w-4 shrink-0'])
            Cari riwayat
        </button>
        <div id="search-box" class="hidden px-1 pt-1">
            <input id="search-input" type="search" placeholder="Cari riwayat chat..." autocomplete="off" class="w-full bg-white/10 placeholder:text-gray-300 text-[15px] text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/30">
        </div>
        <p class="mt-3 px-3 text-xs font-semibold uppercase tracking-wide text-white/60">Riwayat chat</p>
        <div id="history-list" class="mt-1 max-h-[40vh] overflow-y-auto overscroll-contain space-y-0.5"></div>
    </div>
@endsection

@section('content')
    @php $asisten = config('siakred.asisten'); @endphp
    <div id="info-panel" hidden class="shrink-0 bg-brand-50 border-b border-brand-100 text-[14px] sm:text-[15px] text-gray-700">
        <div class="max-w-[960px] mx-auto px-4 sm:px-6 py-3">
            <ul class="list-disc pl-5 space-y-1">
                <li>Mengacu pada <strong>{{ $asisten['jumlah_dokumen'] }} dokumen standar</strong> yang terindeks ({{ $asisten['versi_standar'] }}).</li>
                <li>Dokumen mengacu Permendikbud 3/2020 dan Renstra 2019&ndash;2023, jadi jawaban perlu diverifikasi terhadap regulasi yang berlaku saat ini.</li>
                <li>Asisten menjawab pertanyaan seputar <strong>isi dokumen standar</strong>, bukan data capaian prodi.</li>
            </ul>
        </div>
    </div>

@verbatim
    <!-- CHAT AREA -->
    <main id="chat-area" class="flex-1 overflow-y-auto">
        <div class="max-w-[960px] mx-auto px-4 sm:px-5 py-6 sm:py-8 space-y-6" id="messages-container">

            <!-- Sapaan awal (chat baru): kotak input dipindah ke sini lewat JS -->
            <div id="welcome" class="w-full flex flex-col items-center text-center">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
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
    <footer id="input-footer" class="shrink-0 px-3 sm:px-4 pb-3 sm:pb-4 pt-3">
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

@endverbatim
@endsection

@push('scripts')
<script>
    // Tombol "Cakupan" di header: buka/tutup panel batasan
    (function () {
        const btn = document.getElementById('info-toggle');
        const panel = document.getElementById('info-panel');
        btn.addEventListener('click', function () {
            panel.hidden = !panel.hidden;
            btn.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
        });
    })();
</script>
@verbatim
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

    // Tutup menu geser (layar kecil) lewat logika di layout
    function closeSidebar() {
        if (!backdrop.classList.contains('hidden')) document.getElementById('sidebar-close').click();
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
            del.className = 'absolute right-1.5 p-1.5 rounded-md text-gray-300 hover:text-white hover:bg-white/15 opacity-0 group-hover:opacity-100 focus:opacity-100 [@media(hover:none)]:opacity-100 transition-opacity';
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

    // Deep link dari halaman indikator: /asisten-ai?q=... (dev.md §8.3).
    // Pertanyaan hanya diisikan ke kotak input, TIDAK langsung dikirim.
    const prefill = new URLSearchParams(window.location.search).get('q');
    if (prefill) {
        input.value = prefill.slice(0, input.maxLength > 0 ? input.maxLength : 2000);
        input.dispatchEvent(new Event('input'));
    }

    updateEmptyState();
    // Di HP jangan langsung fokus (keyboard akan menutupi layar), kecuali ada pertanyaan terisi
    if (prefill || window.matchMedia('(hover: hover)').matches) {
        input.focus();
        if (prefill) input.setSelectionRange(input.value.length, input.value.length);
    }
})();
</script>

@endverbatim
@endpush
