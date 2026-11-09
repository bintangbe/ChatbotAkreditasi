<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk · SIAKRED</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: { extend: {
        fontFamily: { sans: ['"Instrument Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
        colors: { brand: { 50: '#FFF3F3', 100: '#FFE1E2', 200: '#FFC4C7', 500: '#E0262F', 600: '#C8161F', 700: '#A8101A', 800: '#870D16' } }
    } }
}
</script>
</head>
<body class="min-h-[100dvh] bg-[#FBF6F5] text-gray-800 font-sans antialiased flex flex-col lg:flex-row">

    {{-- Panel merek: atas (HP) / kiri (desktop) --}}
    <div class="bg-[#A8101A] text-white lg:w-[42%] lg:min-h-[100dvh] px-6 py-8 sm:px-10 lg:py-14 flex flex-col">
        <div class="flex items-center gap-3">
            <div class="bg-white rounded-xl p-1.5 shrink-0">
                <img src="{{ asset('images/logo-telkom.png') }}" alt="Logo Telkom University" class="h-11 sm:h-12 w-auto block">
            </div>
            <div>
                <p class="text-[19px] font-bold leading-tight">SIAKRED</p>
                <p class="text-sm text-white/75">Telkom University Surabaya</p>
            </div>
        </div>
        <div class="hidden lg:block mt-auto">
            <h1 class="text-[34px] font-bold leading-tight tracking-tight">Sistem Informasi Akreditasi Program Studi</h1>
            <p class="mt-3 text-[17px] text-white/80 max-w-[440px]">Kelola data pemenuhan Standar Mutu Universitas Telkom dan capaian indikator beserta bukti pendukungnya.</p>
        </div>
    </div>

    {{-- Form --}}
    <main class="flex-1 flex items-start lg:items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-[420px]">
            <h2 class="text-[26px] sm:text-[28px] font-bold tracking-tight">Masuk</h2>
            <p class="mt-1 text-[15px] text-gray-500">Gunakan akun SIAKRED Anda.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5" novalidate>
                @csrf

                @if ($errors->any())
                    <div role="alert" class="rounded-xl border border-brand-200 bg-brand-50 text-brand-800 px-4 py-3 text-[15px]">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label for="login" class="block text-[15px] font-medium mb-1.5">Username atau email</label>
                    <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username"
                           class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-[16px] focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                </div>

                <div>
                    <label for="password" class="block text-[15px] font-medium mb-1.5">Kata sandi</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-[16px] focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                </div>

                <label class="flex items-center gap-2.5 text-[15px] text-gray-600 select-none">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-gray-300 accent-[#C8161F]" @checked(old('remember'))>
                    Ingat saya
                </label>

                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 active:scale-[.99] text-white text-[16px] font-semibold rounded-xl py-3 transition">
                    Masuk
                </button>
            </form>

            <p class="mt-8 text-sm text-gray-400 text-center">Belum punya akun? Hubungi Admin SPM.</p>
        </div>
    </main>
</body>
</html>
