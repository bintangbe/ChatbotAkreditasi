{{-- Tombol + dialog hapus dengan alasan wajib. Param: $action, $id (unik), $nama, $kecil (opsional) --}}
<button type="button" onclick="document.getElementById('hapus-{{ $id }}').showModal()"
        class="{{ !empty($kecil) ? 'text-sm font-medium text-brand-700 hover:underline' : 'inline-flex items-center gap-2 border border-brand-200 text-brand-700 hover:bg-brand-50 rounded-xl px-4 py-2.5 text-[15px] font-medium' }}">
    Hapus
</button>
<dialog id="hapus-{{ $id }}" class="w-[min(92vw,460px)] rounded-2xl p-0 backdrop:bg-black/40">
    <form method="POST" action="{{ $action }}" class="p-5 sm:p-6">
        @csrf
        @method('DELETE')
        <h3 class="text-[18px] font-semibold">Hapus data?</h3>
        <p class="mt-1 text-[15px] text-gray-600">{{ $nama }} akan dihapus (soft delete). Administrator masih dapat memulihkannya, dan tindakan ini tercatat di log aktivitas.</p>
        <label for="alasan-{{ $id }}" class="block mt-4 text-[15px] font-medium mb-1.5">Alasan <span class="text-brand-600">*</span></label>
        <textarea id="alasan-{{ $id }}" name="alasan" rows="3" required minlength="5" maxlength="500"
                  class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-[15px] focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100"></textarea>
        <div class="mt-5 flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="border border-gray-300 hover:bg-gray-50 rounded-xl px-4 py-2.5 text-[15px]">Batal</button>
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white rounded-xl px-4 py-2.5 text-[15px] font-medium">Hapus</button>
        </div>
    </form>
</dialog>
