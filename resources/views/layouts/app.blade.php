<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'tvlike')</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <form id="nav-randomwalk-form" action="{{ route('programs.interact', 'randomwalk') }}" method="POST" class="hidden">
        @csrf
    </form>
    <header class="bg-indigo-600 text-white shadow-md sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ route('programs.index') }}" class="font-bold text-lg tracking-wider hover:opacity-90 transition">
                📺&thinsp;tvlike&thinsp;❤️
            </a>

            <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
                <a href="{{ route('programs.index') }}" class="hover:text-indigo-200 transition">List</a>
                <a href="#" 
                    onclick="event.preventDefault(); document.getElementById('nav-randomwalk-form').submit();" 
                    class="hover:text-indigo-200 transition">
                    🎲&thinsp;Random&thinsp;Walk
                </a>
            </nav>

            <button 
                type="button" 
                onclick="toggleMenu()" 
                class="block md:hidden p-2 rounded-md hover:bg-indigo-700 focus:outline-none transition"
                aria-label="open menu"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </header>

    <div id="mobile-menu" class="hidden fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50" onclick="toggleMenu()"></div>
        
        <nav class="fixed top-0 right-0 bottom-0 w-64 bg-white p-6 shadow-xl flex flex-col space-y-4">
            <div class="flex items-center justify-between border-b pb-2 mb-2">
                <span class="font-bold text-gray-700">menu</span>
                <button type="button" onclick="toggleMenu()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <a href="{{ route('programs.index') }}" onclick="toggleMenu()" class="block font-medium py-2 px-3 rounded hover:bg-gray-100 text-gray-800">List</a>
            <a href="#" 
                onclick="event.preventDefault(); toggleMenu(); document.getElementById('nav-randomwalk-form').submit();" 
                class="block font-medium py-2 px-3 rounded hover:bg-gray-100 text-gray-800">
                🎲 Random Walk
            </a>
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
