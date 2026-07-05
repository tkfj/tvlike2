@extends('layouts.app')

@section('title', 'List - tvlike')

@section('content')
@php
/**
 * キワの確率を100% / 0%に激突させない丸め関数
 * @param float|null $proba 0.0〜1.0 の生確率
 * @return string|null フォーマット済みの文字列（例: "99.9", "54.2", "0.1"）
 */
$formatProba = function ($proba) {
    if (is_null($proba)) return null;
    $p = $proba * 100; // 0.0 〜 100.0% に変換
    if ($p > 0 && $p < 0.1) {
        $val = ceil($p * 10) / 10;
    } elseif ($p > 99.9 && $p < 100) {
        $val = floor($p * 10) / 10;
    } else {
        $val = round($p, 1);
    }
    return number_format($val, 1);
};
@endphp
<h1 class="w-full md:max-w-3xl mx-auto px-4 text-xs font-medium text-gray-400 font-mono tracking-widest mb-4">
    /<span class="font-sans">&thinsp;</span>tvlike<span class="font-sans">&thinsp;</span>/<span class="font-sans">&thinsp;</span>List
</h1>
<form action="{{ route('programs.index') }}" method="GET" class="mb-5 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-md md:max-w-3xl mx-auto">
    @csrf
    <input type="hidden" id="filter-menu-state" name="filter_menu_state" value="{{ request('filter_menu_state', 'closed') }}">
    
    <div class="w-full px-4 pt-3.5 pb-3 flex items-center gap-2">
        <div class="relative flex-1 flex items-center">
            <input type="text" 
                name="keyword" 
                value="{{ $keyword ?? '' }}"
                placeholder="Search..." 
                class="w-full pl-9 pr-9 py-2 bg-white border border-gray-200 rounded-xl text-xs font-mono focus:outline-none focus:border-indigo-500 shadow-sm text-gray-800">
            
            <button type="submit" class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-indigo-600 active:scale-95 transition-all focus:outline-none" aria-label="検索を実行">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z" />
                </svg>
            </button>

            @if(!empty($keyword))
                <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center">
                    <button type="button"
                            onclick="const input = this.form.keyword; input.value = ''; input.focus();"
                            class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                            aria-label="Clear search">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        <button 
            type="button" 
            onclick="toggleFilterMenu()" 
            class="h-[34px] w-[34px] bg-gray-50 border border-gray-200 text-gray-500 rounded-xl shadow-sm hover:bg-gray-100 active:bg-gray-200 transition shrink-0 flex items-center justify-center focus:outline-none"
            aria-label="詳細検索条件を開閉"
        >
            <svg id="filter-icon" class="h-4 w-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </button>
    </div>

    <div id="advanced-filter-menu" class="hidden border-t border-gray-100 bg-slate-50/40 p-4 pt-3.5 space-y-3.5 text-sm">
        
        <div class="flex items-center gap-2 flex-wrap">
            <label class="inline-flex items-center gap-2 cursor-pointer text-[11px] font-bold text-gray-600 select-none bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-sm active:bg-gray-50 font-mono">
                <input type="hidden" name="future_only" value="0"><input 
                    type="checkbox" 
                    name="future_only" 
                    value="1" 
                    onchange="this.form.submit()"
                    {{ ($future_only ?? '1') === '1' ? 'checked' : '' }}
                    class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <span>Today+</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer text-[11px] font-bold text-gray-600 select-none bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-sm active:bg-gray-50 font-mono">
                <input type="hidden" name="pred_only" value="0"><input 
                    type="checkbox" 
                    name="pred_only" 
                    value="1" 
                    onchange="this.form.submit()"
                    {{ ($pred_only ?? '1') === '1' ? 'checked' : '' }}
                    class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <span>Pred</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer text-[11px] font-bold text-gray-600 select-none bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-sm active:bg-gray-50 font-mono">
                <input type="hidden" name="mych_only" value="0"><input 
                    type="checkbox" 
                    name="mych_only" 
                    value="1" 
                    onchange="this.form.submit()"
                    {{ ($mych_only ?? '1') === '1' ? 'checked' : '' }}
                    class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <span>MyCh</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer text-[11px] font-bold text-gray-600 select-none bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-sm active:bg-gray-50 font-mono">
                <input type="hidden" name="has_value" value="0"><input 
                    type="checkbox" 
                    name="has_value" 
                    value="1" 
                    onchange="this.form.submit()"
                    {{ ($has_value ?? '1') === '1' ? 'checked' : '' }}
                    class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <span>HasValue</span>
            </label>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <label class="inline-flex items-center gap-2 cursor-pointer text-[11px] font-bold text-gray-600 select-none bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-sm active:bg-gray-50 font-mono">
                <input type="hidden" name="is_adl" value="0"><input 
                    type="checkbox" 
                    name="is_adl" 
                    value="1" 
                    onchange="this.form.submit()"
                    {{ ($is_adl ?? '1') === '1' ? 'checked' : '' }}
                    class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <span>ADL</span>
            </label>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-2.5 bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-sm overflow-x-auto">
                <span class="text-[11px] text-gray-400 font-bold font-mono tracking-wider shrink-0">Act:</span>
                
                <label class="inline-flex items-center gap-1 cursor-pointer text-xs font-medium text-gray-700 select-none">
                    <input type="checkbox" name="interaction[]" value="P" onchange="this.form.submit()" {{ in_array('P', $interaction) ? 'checked' : '' }} class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="px-1.5 py-0.5 bg-green-50 text-green-700 rounded border border-green-200 text-[10px] font-mono font-bold">Posi</span>
                </label>

                <label class="inline-flex items-center gap-1 cursor-pointer text-xs font-medium text-gray-700 select-none">
                    <input type="checkbox" name="interaction[]" value="N" onchange="this.form.submit()" {{ in_array('N', $interaction) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="px-1.5 py-0.5 bg-red-50 text-red-700 rounded border border-red-200 text-[10px] font-mono font-bold">Nega</span>
                </label>

                <label class="inline-flex items-center gap-1 cursor-pointer text-xs font-medium text-gray-700 select-none">
                    <input type="checkbox" name="interaction[]" value="-" onchange="this.form.submit()" {{ in_array('-', $interaction) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded border border-gray-200 text-[10px] font-mono">None</span>
                </label>
                <input type="hidden" name="interaction[]" value="x">
            </div>

            <div class="flex items-center gap-2.5 bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-sm overflow-x-auto">
                <span class="text-[11px] text-gray-400 font-bold font-mono tracking-wider shrink-0">Pred:</span>
                
                <label class="inline-flex items-center gap-1 cursor-pointer text-xs font-medium text-gray-700 select-none">
                    <input type="checkbox" name="prediction[]" value="P" onchange="this.form.submit()" {{ in_array('P', $prediction) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="px-1.5 py-0.5 bg-green-50 text-green-700 rounded border border-green-200 text-[10px] font-mono font-bold">Posi</span>
                </label>

                <label class="inline-flex items-center gap-1 cursor-pointer text-xs font-medium text-gray-700 select-none">
                    <input type="checkbox" name="prediction[]" value="N" onchange="this.form.submit()" {{ in_array('N', $prediction) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="px-1.5 py-0.5 bg-red-50 text-red-700 rounded border border-red-200 text-[10px] font-mono font-bold">Nega</span>
                </label>

                <label class="inline-flex items-center gap-1 cursor-pointer text-xs font-medium text-gray-700 select-none">
                    <input type="checkbox" name="prediction[]" value="-" onchange="this.form.submit()" {{ in_array('-', $prediction) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded border border-gray-200 text-[10px] font-mono">n/a</span>
                </label>
                <input type="hidden" name="prediction[]" value="x">
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-2 bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-sm overflow-x-auto">
                <label for="sort" class="text-[11px] text-gray-400 font-mono font-bold shrink-0">Sort:</label>
                <select name="sort" id="sort" onchange="this.form.submit()" class="bg-transparent border-none p-0 text-xs text-gray-700 focus:ring-0 cursor-pointer font-medium">
                    <option value="start_desc" {{ $sort === 'start_desc' ? 'selected' : '' }}>Newer</option>
                    <option value="start_asc" {{ $sort === 'start_asc' ? 'selected' : '' }}>Older</option>
                    <option value="prob_desc" {{ $sort === 'prob_desc' ? 'selected' : '' }}>Higher</option>
                    <option value="prob_asc" {{ $sort === 'prob_asc' ? 'selected' : '' }}>Lower</option>
                </select>
            </div>
            <div class="flex items-center gap-2 bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-sm overflow-x-auto">
                <label for="limit" class="text-[11px] text-gray-400 font-mono font-bold shrink-0">Count:</label>
                <select name="limit" id="limit" onchange="this.form.submit()" class="bg-transparent border-none p-0 text-xs text-gray-700 focus:ring-0 cursor-pointer font-medium">
                    <option value="100" {{ $limit === '100' ? 'selected' : '' }}>100</option>
                    <option value="500" {{ $limit === '500' ? 'selected' : '' }}>500</option>
                    <option value="2000" {{ $limit === '2000' ? 'selected' : '' }}>2000</option>
                </select>
            </div>
        </div>

    </div>
</form>
<style>
div:target {
    animation: returnHighlight 1s ease-out;
}

@keyframes returnHighlight {
    0% { 
        background-color: rgb(238 242 255); /* bg-indigo-50 */
        border-color: rgb(165 180 252);     /* border-indigo-300 (枠線も少し強調) */
    } 
    100% { 
        background-color: rgb(255 255 255); /* 元のbg-white（または独自の色）に戻す */
        border-color: rgb(229 231 235);     /* 元のborder-gray-200に戻す */
    }
}
</style>
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
<div class="w-full max-w-md md:max-w-3xl mx-auto bg-white shadow rounded-xl overflow-hidden p-4 md:p-6">
    @if (empty($programs) || count($programs) === 0)
        <p class="text-center text-gray-400 py-8">該当する番組は見つかりませんでした。</p>
    @else
        <div class="space-y-4 divide-y divide-gray-100">
            @foreach ($programs as $prog)
                @php
                    $dts = new DateTime('', new DateTimeZone('Asia/Tokyo'));
                    $dts->setTimestamp(intdiv($prog['start_at'],1000));
                    $d_s = $dts ? $dts->format('Y-m-d') : '';
                    $dw_s = $dts ? $dts->format('D') : '';
                    $ts_s = $dts ? $dts->format('H:i') : '';
                    $dti_m = intdiv($prog['duration'],60*1000);

                    $genre_filtered = $prog['genres'] ? array_filter(json_decode($prog['genres'], true), function($p) {
                        // 番組のジャンルが制御情報または字幕/音声解説のものを除く
                        return $p['lv1']!=14 && !($p['lv1']==11 && ($p['lv2']==5 || $p['lv2']==6));
                    }) : [];
                    $genre_labels = array_map(function($p) {
                        return $p['lv1_label'] . '：' . $p['lv2_label'];
                    }, $genre_filtered);
                    $adl_labels = $prog['defence_labels'] ? array_values(json_decode($prog['defence_labels'], true)) : [];

                    $get_badge_class = function($val) {
                        if ($val === 'P') return 'bg-green-100 text-green-800 border-green-200';
                        if ($val === 'N') return 'bg-red-100 text-red-800 border-red-200';
                        return 'bg-gray-100 text-gray-800 border-gray-200';
                    };
                @endphp
                <div id="pgm-{{ $prog['pgm_uid'] }}-{{ $prog['start_at'] }}" class="scroll-mt-20 py-4 first:pt-0 border-b border-gray-100 last:border-0 flex flex-col justify-between gap-2 transition-colors duration-500">
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4 mb-1">
                            <h2 class="text-base font-bold text-gray-900 leading-snug">
                                <a href="{{ route('programs.show', array_merge(['id' => $prog['pgm_uid'].'.'.$prog['start_at']], request()->query())) }}" class="hover:text-indigo-600 transition-colors">
                                    {{ normalize_epg_text($prog['pgm_title']) }}
                                </a>
                            </h2>
                            <a href="{{ route('programs.show', array_merge(['id' => $prog['pgm_uid'].'.'.$prog['start_at']], request()->query())) }}" 
                                class="inline-block whitespace-nowrap text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2.5 py-1 rounded-lg hover:bg-indigo-100 transition tracking-wide font-mono">
                                View<span class="font-sans">&thinsp;</span>→
                            </a>
                        </div>
                        
                        <div class="text-xs text-gray-500 space-x-1 mb-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span class="inline-block whitespace-nowrap font-bold text-gray-800">{{ str_replace(" ","\u{2009}",normalize_epg_text($prog['service_name'])) }}</span>
                            <span class="inline-block whitespace-nowrap font-mono text-gray-500">{{ $d_s }}<span class="font-sans">&thinsp;</span>{{ $dw_s }}<span class="font-sans">&thinsp;</span>{{ $ts_s }}</span>
                            <span class="inline-block whitespace-nowrap font-mono bg-gray-200/60 text-gray-700 px-1.5 py-0.5 rounded font-medium">{{ $dti_m }}<span class="font-sans">&thinsp;</span>min</span>
                            @if($adl_labels || $genre_labels)
                                <span class="gap-0">
                                    @if($adl_labels)
                                        @foreach ($adl_labels as $adl_label)
                                            <span class="inline-block whitespace-nowrap bg-orange-100 text-orange-800 px-1.5 py-0.5 rounded font-midium">{{ $adl_label }}</span>
                                        @endforeach
                                    @endif
                                    @if($genre_labels)
                                        @foreach ($genre_labels as $genre_label)
                                            <span class="inline-block whitespace-nowrap bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded font-midium">{{ $genre_label }}</span>
                                        @endforeach
                                    @endif
                                </span>
                            @endif
                        </div>
                        
                        @if($prog['pgm_description'])
                        <div class="text-[13px] text-gray-600 leading-relaxed mb-3 line-clamp-2">
                            {{ normalize_epg_text($prog['pgm_description']) }}
                        </div>
                        @endif
                        
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs pt-1">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-block whitespace-nowrap px-2 py-0.5 border text-[11px] rounded-full font-mono {{ $get_badge_class($prog['interaction_next'] ?? $prog['interaction'] ?? '') }}">
                                    Act:<span class="font-sans">&thinsp;</span>{{ $prog['interaction_next'] ?? $prog['interaction'] }}{{ ($prog['interaction_next'] ?? '-') == $prog['interaction'] ? '' : '*'}}
                                </span>
                                
                                <span class="inline-block whitespace-nowrap px-2 py-0.5 border text-[11px] rounded-full font-mono {{ $get_badge_class($prog['pred_label'] ?? '') }}">
                                    Pred:<span class="font-sans">&thinsp;</span>{{ $prog['pred_label'] ?? '-' }}
                                    @if($prog['pred_proba'])
                                    {{ $formatProba($prog['pred_proba']) }}%
                                    @endif
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <span class="inline-block whitespace-nowrap text-gray-400 text-[10px] font-mono">#{{ $prog['pgm_uid'] }}.{{ intdiv($prog['start_at'],10000) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
