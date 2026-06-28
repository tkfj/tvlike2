<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '番組管理システム')</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    <header class="bg-indigo-600 text-white shadow-md sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ route('programs.index') }}" class="font-bold text-lg tracking-wider hover:opacity-90 transition">
                📺 PGM-Manager
            </a>

            <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
                <a href="{{ route('programs.index') }}" class="hover:text-indigo-200 transition">番組一覧</a>
                <a href="#" class="hover:text-indigo-200 transition">ダミーメニュー1</a>
                <a href="#" class="hover:text-indigo-200 transition">設定</a>
                <form><button 
                    type="submit" 
                    formaction="{{ route('programs.interact', 'randomwalk') }}" 
                    formmethod="POST"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg shadow hover:bg-purple-700 font-semibold text-sm transition shrink-0 flex items-center gap-1 active:bg-purple-800"
                >
                    🎲 Random Walk
                </button></form>
            </nav>

            <button 
                type="button" 
                onclick="toggleMenu()" 
                class="block md:hidden p-2 rounded-md hover:bg-indigo-700 focus:outline-none transition"
                aria-label="メニューを開く"
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
                <span class="font-bold text-gray-700">メニュー</span>
                <button type="button" onclick="toggleMenu()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <a href="{{ route('programs.index') }}" onclick="toggleMenu()" class="block font-medium py-2 px-3 rounded hover:bg-gray-100 text-gray-800">番組一覧</a>
            <a href="#" onclick="toggleMenu()" class="block font-medium py-2 px-3 rounded hover:bg-gray-100 text-gray-800">ダミーメニュー1</a>
            <a href="#" onclick="toggleMenu()" class="block font-medium py-2 px-3 rounded hover:bg-gray-100 text-gray-800">設定</a>
        </nav>
    </div>

    <main class="max-w-4xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    <script>
        // フルバージョン標準の classList 操作に戻す
        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>
