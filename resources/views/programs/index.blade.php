@extends('layouts.app')

@section('title', '番組検索一覧')

@section('content')
<h1 class="text-2xl font-bold mb-6 border-b pb-2 text-gray-700">番組検索一覧</h1>
<form action="{{ route('programs.index') }}" method="GET" class="mb-6 bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
    @csrf
    <input type="hidden" id="filter-menu-state" name="filter_menu_state" value="{{ request('filter_menu_state', 'closed') }}">
    <div class="p-4 flex gap-2 items-center">
        <div class="flex-1 flex gap-2">
            <input 
                type="text" 
                name="keyword" 
                value="{{ $keyword ?? '' }}" 
                placeholder="番組名で検索..." 
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white text-sm"
            >
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 font-medium text-sm transition shrink-0 active:bg-indigo-800">
                検索
            </button>
            @if(!empty($keyword))
                <a href="{{ route('programs.index') }}" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg shadow hover:bg-gray-300 flex items-center text-sm shrink-0">
                    クリア
                </a>
            @endif
        </div>

        <button 
            type="button" 
            onclick="toggleFilterMenu()" 
            class="p-2 bg-gray-50 border border-gray-200 text-gray-500 rounded-lg shadow-sm hover:bg-gray-100 active:bg-gray-200 transition shrink-0 flex items-center justify-center"
            aria-label="詳細検索条件を開閉"
        >
            <svg id="filter-icon" class="h-5 w-5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </button>
    </div>

    <div id="advanced-filter-menu" class="hidden border-t border-gray-100 bg-slate-50/50 p-4 space-y-4 text-sm">
        
        <div class="flex items-center gap-3 flex-wrap">
            <label class="inline-flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-gray-700 select-none bg-white px-3 py-2 rounded-md border border-gray-200 shadow-sm active:bg-gray-50">
                <input type="hidden" name="future_only" value="0"><input 
                    type="checkbox" 
                    name="future_only" 
                    value="1" 
                    onchange="this.form.submit()"
                    {{ ($future_only ?? '1') === '1' ? 'checked' : '' }}
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <span class="text-gray-700">未来日</span>
            </label>
            <label class="inline-flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-gray-700 select-none bg-white px-3 py-2 rounded-md border border-gray-200 shadow-sm active:bg-gray-50">
                <input type="hidden" name="pred_only" value="0"><input 
                    type="checkbox" 
                    name="pred_only" 
                    value="1" 
                    onchange="this.form.submit()"
                    {{ ($pred_only ?? '1') === '1' ? 'checked' : '' }}
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <span class="text-gray-700">予測済</span>
            </label>
            <label class="inline-flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-gray-700 select-none bg-white px-3 py-2 rounded-md border border-gray-200 shadow-sm active:bg-gray-50">
                <input type="hidden" name="tgtst_only" value="0"><input 
                    type="checkbox" 
                    name="tgtst_only" 
                    value="1" 
                    onchange="this.form.submit()"
                    {{ ($tgtst_only ?? '1') === '1' ? 'checked' : '' }}
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <span class="text-gray-700">視聴可能局</span>
            </label>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pt-1">
            <div class="flex items-center gap-3 bg-white px-3 py-2 rounded-md border border-gray-200 shadow-sm overflow-x-auto">
                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider shrink-0">Prediction:</span>
                
                <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-medium text-gray-700 select-none">
                    <input type="checkbox" name="labels[]" value="p" onchange="this.form.submit()" {{ in_array('p', $pred_labels) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="px-1.5 py-0.5 bg-green-50 text-green-700 rounded border border-green-200 text-[11px]">Positive</span>
                </label>

                <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-medium text-gray-700 select-none">
                    <input type="checkbox" name="labels[]" value="n" onchange="this.form.submit()" {{ in_array('n', $pred_labels) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="px-1.5 py-0.5 bg-red-50 text-red-700 rounded border border-red-200 text-[11px]">Negative</span>
                </label>

                <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-medium text-gray-700 select-none">
                    <input type="checkbox" name="labels[]" value="_" onchange="this.form.submit()" {{ in_array('_', $pred_labels) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded border border-gray-200 text-[11px]">n/a</span>
                </label>
            </div>

            <div class="flex items-center gap-3 justify-start lg:justify-end overflow-x-auto">
                <div class="flex items-center gap-1.5 bg-white px-2.5 py-1.5 rounded-md border border-gray-200 shadow-sm">
                    <label for="sort" class="text-xs text-gray-400 font-medium shrink-0">Sort:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()" class="bg-transparent border-none p-0 text-xs text-gray-700 focus:ring-0 cursor-pointer">
                        <option value="start_desc" {{ $sort === 'start_desc' ? 'selected' : '' }}>放送開始が新しい順</option>
                        <option value="start_asc" {{ $sort === 'start_asc' ? 'selected' : '' }}>放送開始が古い順</option>
                        <option value="prob_desc" {{ $sort === 'prob_desc' ? 'selected' : '' }}>予測確率（Prob）が高い順</option>
                        <option value="prob_asc" {{ $sort === 'prob_asc' ? 'selected' : '' }}>予測確率（Prob）が低い順</option>
                    </select>
                </div>

                <div class="flex items-center gap-1.5 bg-white px-2.5 py-1.5 rounded-md border border-gray-200 shadow-sm">
                    <label for="limit" class="text-xs text-gray-400 font-medium shrink-0">Count:</label>
                    <select name="limit" id="limit" onchange="this.form.submit()" class="bg-transparent border-none p-0 text-xs text-gray-700 focus:ring-0 cursor-pointer">
                        <option value="100" {{ $limit === '100' ? 'selected' : '' }}>100</option>
                        <option value="500" {{ $limit === '500' ? 'selected' : '' }}>500</option>
                        <option value="2000" {{ $limit === '2000' ? 'selected' : '' }}>2000</option>
                    </select>
                </div>
            </div>
        </div>

    </div>
</form>

<script>
function toggleFilterMenu() {
    const menu = document.getElementById('advanced-filter-menu');
    const icon = document.getElementById('filter-icon');
    const stateInput = document.getElementById('filter-menu-state');
    
    // メニューの開閉
    menu.classList.toggle('hidden');
    
    if (menu.classList.contains('hidden')) {
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>';
        stateInput.value = 'closed';
    } else {
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>';
        stateInput.value = 'open';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    const previousState = "{{ request('filter_menu_state', 'closed') }}";
    if (previousState === 'open') {
        toggleFilterMenu();
    }
});
</script>
<div class="bg-white shadow rounded-lg overflow-hidden p-6">
    @if (empty($programs) || count($programs) === 0)
        <p class="text-center text-gray-400 py-8">該当する番組は見つかりませんでした。</p>
    @else
        <div class="space-y-4 divide-y divide-gray-100">
            @foreach ($programs as $prog)
                @php
                    // 元のDateTimeパース＆差分計算ロジックを完全継承
                    $dts = \DateTime::createFromFormat('YmdHi', $prog['pg_start']);
                    $dts_s = $dts ? $dts->format('Y-m-d H:i') : '不明';
                    $dte = \DateTime::createFromFormat('YmdHi', $prog['pg_end']);
                    
                    $dti_m = 0;
                    if ($dts && $dte) {
                        $dti = $dte->diff($dts);
                        $dti_m = ($dti->days * 24 * 60) + ($dti->h * 60) + $dti->i;
                    }

                    $genre_cd = $prog['genre'] ?? '_';
                    $genre_lbl = $genre_map[$genre_cd] ?? null;

                    // p/n判定用のTailwindクラスマッピング
                    $get_badge_class = function($val) {
                        if ($val === 'p') return 'bg-green-100 text-green-800 border-green-200';
                        if ($val === 'n') return 'bg-red-100 text-red-800 border-red-200';
                        return 'bg-gray-100 text-gray-800 border-gray-200';
                    };
                @endphp

                <div class="pt-4 first:pt-0 flex flex-col justify-between gap-2">
                    <div class="flex-1">
                        <div class="text-base font-bold text-gray-900 mb-1">
                            {{ $prog['pg_title'] }}
                        </div>
                        
                        <div class="text-xs text-gray-500 space-x-1 mb-2">
                            <span>{{ $prog['pgm_station_name'] ?? '' }}</span>
                            <span>|</span>
                            <span>
                                {{ substr($prog['pg_start'], 0, 4) }}-{{ substr($prog['pg_start'], 4, 2) }}-{{ substr($prog['pg_start'], 6, 2) }}
                                {{ substr($prog['pg_start'], 8, 2) }}:{{ substr($prog['pg_start'], 10, 2) }}
                            </span>
                            <span>|</span>
                            <span>{{ $dti_m }}分</span>
                            @if($genre_lbl)
                                <span>|</span>
                                <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-medium">{{ $genre_lbl }}</span>
                            @endif
                        </div>
                        
                        <div class="text-xs text-gray-600 leading-relaxed mb-3 line-clamp-2">
                            {{ $prog['pg_detail'] ?? '' }}
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="px-2 py-0.5 border text-[11px] rounded font-mono {{ $get_badge_class($prog['interaction_next'] ?? $prog['interaction'] ?? '') }}">
                                Interaction: {{ $prog['interaction_next'] ?? $prog['interaction'] }}{{ ($prog['interaction_next'] ?? '_') == $prog['interaction'] ? '' : '*'}}
                            </span>
                            
                            <span class="px-2 py-0.5 border text-[11px] rounded font-mono {{ $get_badge_class($prog['pred_label'] ?? '') }}">
                                Prediction: {{ $prog['pred_label'] ?? '_' }}
                            </span>
                            
                            <span class="font-mono text-gray-500 text-[11px]">
                                Proba: {{ number_format((float)($prog['pred_proba'] ?? 0), 4) }}
                            </span>
                            
                            <a href="{{ route('programs.show', $prog['pgm_uid'] ?? '0') }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-[11px] hover:underline">
                                タグ付け
                            </a>
                            
                            <span class="text-gray-400 text-[11px] font-mono">
                                Uid: {{ $prog['pgm_uid'] ?? '-' }}
                            </span>
                            
                            <span class="text-gray-400 text-[11px] font-mono">
                                AsOf: {{ $prog['asof'] ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
