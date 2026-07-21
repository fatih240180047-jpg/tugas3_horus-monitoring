<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Supply Chain Intelligence Platform</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Material Symbols Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#b91c1c',
                        secondary: '#d97706',
                        error: '#ef4444',
                        'on-primary': '#ffffff',
                        'on-secondary': '#ffffff',
                        'on-error': '#ffffff',
                        'surface-container-lowest': '#05070c',
                        'surface-container-low': '#0b0f19',
                        'surface-container': '#111827',
                        'surface-container-high': '#1f2937',
                        'surface-container-highest': '#374151',
                        'on-surface': '#f9fafb',
                        'on-surface-variant': '#9ca3af',
                        'outline': '#4b5563',
                        'outline-variant': '#374151',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        headline: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface-container-lowest text-on-surface min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Ambient background glow -->
    <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-primary/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-secondary/5 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="bg-surface-container-low border border-outline-variant w-full max-w-[420px] rounded-2xl p-8 md:p-10 shadow-2xl relative z-10">
        
        <!-- Header -->
        <div class="text-center mb-8 flex flex-col items-center gap-3">
            <div class="relative">
                <img src="{{ asset('images/Horus.png') }}" alt="Horus Icon" class="h-12 w-auto rounded-lg shadow-lg border border-outline-variant">
                <div class="absolute -inset-1 rounded-lg bg-primary/20 blur opacity-30 pointer-events-none"></div>
            </div>
            <div>
                <h2 class="font-headline text-xl font-black tracking-wide text-on-surface">Horus Intelligence</h2>
                <p class="text-xs text-on-surface-variant font-medium mt-1">Global Supply Chain Risk Platform</p>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-error/10 border border-error/30 px-4 py-3 rounded-lg mb-6 flex items-start gap-2.5 text-xs text-error font-semibold leading-relaxed">
                <span class="material-symbols-outlined text-[16px] flex-shrink-0 mt-0.5">report</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('masuk.submit') }}" method="POST" class="space-y-5">
            @csrf
            
            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Email Korporat</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-outline-variant material-symbols-outlined text-[18px] select-none">mail</span>
                    <input type="email" name="email" id="email" 
                           class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-4 py-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold" 
                           placeholder="nama@horus.local" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="password" class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kata Sandi</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-outline-variant material-symbols-outlined text-[18px] select-none">lock</span>
                    <input type="password" name="password" id="password" 
                           class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-4 py-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold" 
                           placeholder="••••••••" required>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs py-1">
                <label class="flex items-center gap-2 cursor-pointer text-on-surface-variant font-bold uppercase tracking-wider text-[10px] select-none">
                    <input type="checkbox" name="ingat_saya" value="1" class="rounded bg-surface-container-lowest border-outline-variant text-primary focus:ring-0 focus:ring-offset-0 cursor-pointer">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-primary hover:opacity-90 text-on-primary font-bold text-xs uppercase tracking-wider py-3.5 rounded-lg shadow-lg transition-all">
                <span>Autentikasi Aman</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </button>
        </form>
    </div>

</body>
</html>
