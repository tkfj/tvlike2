<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'tvlike')</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <form id="nav-randomwalk-form" action="{{ route('programs.interact', 'randomwalk') }}" method="POST" class="hidden">
        @csrf
    </form>
    <header class="bg-indigo-600 text-white shadow-md sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ route('programs.index') }}" class="flex items-center gap-1.5 font-bold text-lg tracking-wider hover:opacity-90 transition font-mono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.0" stroke="currentColor" width="24" height="24">
                    <rect x="2" y="6" width="20" height="14" rx="3" stroke-width="2.0"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.0" d="M7 2 l 5 3 5-3" />
                    <path stroke-width="5.5" stroke-linecap="round" stroke-linejoin="miter" d="M 9.4 11 L 12 13.8 L 14.6 11" />
                </svg>
                tvlike
            </a>
            <nav class="hidden md:flex items-center space-x-6 text-xs font-semibold font-mono tracking-wide">
                <a href="{{ route('programs.index') }}" class="flex items-center hover:text-indigo-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 opacity-80">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 8.25V6ZM3.75 15.75a2.25 2.25 0 0 1 2.25-2.25h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25A2.25 2.25 0 0 1 13.5 8.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25?" />
                    </svg><span class="font-sans">&thinsp;</span>List
                </a>

                <a href="#" 
                    onclick="event.preventDefault(); document.getElementById('nav-randomwalk-form').submit();" 
                    class="flex items-center hover:text-indigo-200 transition">
                    🎲<span class="font-sans">&thinsp;</span>Random<span class="font-sans">&thinsp;</span>Walk
                </a>
                <a href="{{ route('settings.chars') }}" class="flex items-center hover:text-indigo-200 transition">
                    ⚙️ Chars
                </a>
                <a href="{{ route('adl.adl') }}" class="flex items-center hover:text-indigo-200 transition">
                    ⚙️ ADL
                </a>
            </nav>

            <button 
                type="button" 
                onclick="toggleMenu()" 
                class="block md:hidden p-2 rounded-xl hover:bg-indigo-700 focus:outline-none transition"
                aria-label="open menu"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </header>

    <div id="mobile-menu" class="hidden fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-xs" onclick="toggleMenu()"></div>
        
        <nav class="fixed top-0 right-0 bottom-0 w-64 bg-white p-4 shadow-xl flex flex-col">
            <div class="flex items-center justify-end pb-2 mb-3 border-b border-gray-100">
                <button type="button" onclick="toggleMenu()" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition focus:outline-none">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-1 flex-1">
                <a href="{{ route('programs.index') }}" 
                   onclick="toggleMenu()" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-slate-50 active:bg-slate-100 transition tracking-wide font-mono">
                    <span class="mr-2.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 8.25V6ZM3.75 15.75a2.25 2.25 0 0 1 2.25-2.25h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25A2.25 2.25 0 0 1 13.5 8.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25?" />
                        </svg>
                    </span>List
                </a>

                <a href="#" 
                   onclick="event.preventDefault(); toggleMenu(); document.getElementById('nav-randomwalk-form').submit();" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-slate-50 active:bg-slate-100 transition tracking-wide font-mono">
                    <span class="mr-2 text-base shrink-0 select-none">🎲</span>Random<span class="font-sans">&thinsp;</span>Walk
                </a>
                <a href="{{ route('settings.chars') }}" 
                   onclick="toggleMenu()" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-slate-50 active:bg-slate-100 transition tracking-wide font-mono">
                    ⚙️ Chars
                </a>
                <a href="{{ route('adl.adl') }}" 
                   onclick="toggleMenu()" 
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-slate-50 active:bg-slate-100 transition tracking-wide font-mono">
                    ⚙️ ADL
                </a>
            </div>
        </nav>
    </div>

    <main class="max-w-4xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    <script>
        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>
