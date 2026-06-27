@extends('layouts.app')

@section('title', ($program['pg_title'] ?? '番組詳細') . ' - 仕分け')

@section('content')
<div class="max-w-2xl mx-auto">
    
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('programs.index') }}" class="text-sm text-indigo-600 hover:underline flex items-center gap-1">
            &larr; 一覧に戻る
        </a>

        @if(session('message') || isset($message))
            <div id="toast" class="bg-gray-900 text-white text-xs px-4 py-2 rounded-full shadow-lg flex items-center gap-2 animate-bounce">
                <span>{{ session('message') ?? $message }}</span>
                <a href="?pgm_uid={{ $program['pgm_uid'] }}" class="text-sky-400 border border-sky-400 rounded-full px-2 py-0.5 hover:bg-sky-400 hover:text-gray-900 transition">
                    やりなおす
                </a>
            </div>
        @endif
    </div>

    <div class="bg-white shadow-xl rounded-xl border border-gray-100 overflow-hidden transition-transform duration-150 active:scale-[0.99]">
        
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs font-mono">
            <div class="text-gray-400">UID: #{{ $program['pgm_uid'] }}</div>
            <div class="flex gap-3 text-gray-600">
                <span>Interaction: <strong class="text-gray-900">{{ $program['interaction'] ?? '_' }}</strong></span>
                <span>Prediction: <strong class="text-gray-900">{{ $program['pred_label'] ?? '_' }}</strong></span>
                <span>Proba: <strong class="text-gray-900">{{ number_format((float)($program['pred_proba'] ?? 0), 4) }}</strong></span>
            </div>
        </div>

        <div class="p-6">
            <div class="text-xs text-gray-400 mb-2 flex flex-wrap items-center gap-1.5 font-medium">
                <span>{{ $program['pgm_station_name'] ?? $station ?? '' }}</span>
                <span>|</span>
                <span>{{ $dts_s ?? '' }}</span>
                <span>|</span>
                <span>{{ $dti_m ?? 0 }}分</span>
                @if(!empty($genre_lbl))
                    <span>|</span>
                    <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 rounded text-[10px]">{{ $genre_lbl }}</span>
                @endif
            </div>

            <h1 class="text-xl font-bold text-gray-900 mb-4 leading-snug">
                {{ $program['pg_title'] }}
            </h1>

            <div class="border-l-4 border-indigo-500 pl-4 py-1 text-sm text-gray-600 leading-relaxed mb-6 bg-indigo-50/30 rounded-r-lg p-3">
                <span class="block text-[10px] text-indigo-400 font-bold uppercase tracking-wider mb-1">Detail</span>
                {!! nl2br(htmlspecialchars($program['pg_detail'] ?? '詳細説明はありません。')) !!}
            </div>

            <form id="sortForm" action="?" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="pgm_uid" value="{{ $program['pgm_uid'] }}"> <!-- これ要らないよね -->
                <input type="hidden" name="interaction" id="interactionInput" value="">

                <div class="grid grid-cols-3 gap-3 pt-2">
                    <button 
                        type="button" 
                        onclick="submitForm('p')" 
                        class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-4 rounded-xl shadow-md text-sm transition-all text-center cursor-pointer"
                    >
                        興味あり <span class="block text-[11px] font-normal opacity-80 mt-0.5">キー [1]</span>
                    </button>
                    
                    <button 
                        type="button" 
                        onclick="submitForm('n')" 
                        class="bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold py-3 px-4 rounded-xl shadow-md text-sm transition-all text-center cursor-pointer"
                    >
                        興味なし <span class="block text-[11px] font-normal opacity-80 mt-0.5">キー [2]</span>
                    </button>
                    
                    <button 
                        type="button" 
                        onclick="submitForm('_')" 
                        class="bg-gray-500 hover:bg-gray-600 active:bg-gray-700 text-white font-bold py-3 px-4 rounded-xl shadow-md text-sm transition-all text-center cursor-pointer"
                    >
                        保留 <span class="block text-[11px] font-normal opacity-80 mt-0.5">キー [3]</span>
                    </button>
                </div>

                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200/60 flex items-center">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer text-xs font-medium text-gray-700 select-none w-full">
                        <input 
                            type="checkbox" 
                            name="randomwalk" 
                            value="1" 
                            {{ ($randomwalk ?? '0') === '1' ? 'checked' : '' }}
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        >
                        <div>
                            <span class="block font-bold text-gray-800">Random Walk</span>
                        </div>
                    </label>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-6 text-center text-xs text-gray-400 flex items-center justify-center gap-4 bg-white/60 py-2 rounded-lg border border-dashed border-gray-200">
        <span>⌨️ テンキー片手に高速仕分け可能:</span>
        <span><kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-300 rounded font-mono text-gray-600 shadow-sm text-[11px]">1</kbd> 興味あり</span>
        <span><kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-300 rounded font-mono text-gray-600 shadow-sm text-[11px]">2</kbd> 興味なし</span>
        <span><kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-300 rounded font-mono text-gray-600 shadow-sm text-[11px]">3</kbd> 保留</span>
    </div>
</div>

<script>
    function submitForm(interaction) {
        document.getElementById('interactionInput').value = interaction;
        document.getElementById('sortForm').submit();
    }

    // キーボードショートカットの完全移植
    window.addEventListener('keydown', (e) => {
        // 入力フォーム等にフォーカスが当たっている場合は発火させない
        if (e.target.tagName === 'INPUT' && e.target.type === 'text') return;
        
        if (e.key === '1') submitForm('p');
        if (e.key === '2') submitForm('n');
        if (e.key === '3') submitForm('_');
    });

    // トースト通知を10秒後に自動的にフェードアウトさせる演出
    const toast = document.getElementById('toast');
    if (toast) {
        setTimeout(() => {
            toast.style.transition = 'opacity 1s ease';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 1000);
        }, 10000);
    }
</script>
@endsection