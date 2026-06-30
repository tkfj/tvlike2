@php
    $interaction = $program['interaction'] ?? '_';
    $interactionNext = $program['interaction_next'] ?? null;
    $pred_label = $program['pred_label'] ?? '_';
    $labelColors = [
        'p' => ['bg' => 'bg-green-100 text-green-800'],
        'n' => ['bg' => 'bg-red-100 text-red-800'],
        '_' => ['bg' => 'bg-gray-100 text-gray-800'],
    ];
    $interactionColors = $labelColors[$interactionNext ?? $interaction] ?? $labelColors['_'];
    $predLabelColors = $labelColors[$pred_label] ?? $labelColors['_'];
    $interactionStar = (is_null($interactionNext) or $interaction==$interactionNext) ? '' : '*';

    $dts = DateTime::createFromFormat('YmdHi', $program['pg_start']);
    $d_s = $dts->format('Y-m-d');
    $dw_s = $dts->format('D');
    $t_s = $dts->format('H:i');
    $dte = DateTime::createFromFormat('YmdHi', $program['pg_end']);
    $dti = $dte->diff($dts);
    $dti_m = ($dti->days * 24 * 60) + ($dti->h * 60) + $dti->i;
    $station = $program['pgm_station_name']=='Unknown' ? $program['station_name'] : $program['pgm_station_name'];

    $genre_cd = $program['genre'] ?? '_';
    $genre_lbl = $genre_map[$genre_cd] ?? NULL;

    /**
     * キワの確率を100% / 0%に激突させない丸め関数
     * @param float|null $proba 0.0〜1.0 の生確率
     * @return string|null フォーマット済みの文字列（例: "99.9", "54.2", "0.1"）
     */
    $formatProba = function ($proba) {
        if (is_null($proba)) return null;
        $p = $proba * 100; // 0.0 〜 100.0% に変換
        if ($p > 0 && $p < 0.001) {
            $val = ceil($p * 1000) / 10000;
        } elseif ($p > 99.999 && $p < 100) {
            $val = floor($p * 1000) / 1000;
        } else {
            $val = round($p, 3);
        }
        return number_format($val, 3);
    };
@endphp

@extends('layouts.app')

@section('title', ($program['pg_title'] ?? '番組詳細') . ' - tvlike')

@section('content')
<div class="w-full md:max-w-3xl mx-auto px-4 text-xs font-medium text-gray-400 font-mono tracking-widest mb-4">
    /<span class="font-sans">&thinsp;</span>tvlike<span class="font-sans">&thinsp;</span>/<span class="font-sans">&thinsp;</span>UID:<span class="font-sans">&thinsp;</span>#{{ $program['pgm_uid'] }}
</div>
<div class="w-full md:max-w-3xl mx-auto px-4 pt-2 pb-32">
    <div class="flex flex-col space-y-3 border-b border-gray-200 pb-4">
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium font-mono {{ $interactionColors['bg'] }}">
                Act:<span class="font-sans">&thinsp;</span>{{ str_replace(['p','n','_'], ['P','N','-'], $interactionNext ?? $interaction) }}{{ $interactionStar }}
            </span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium font-mono {{ $predLabelColors['bg'] }}">
                Pred:<span class="font-sans">&thinsp;</span>{{ str_replace(['p','n','_'], ['P','N','-'], $pred_label) }}
                @if($program['pred_proba'])
                {{ $formatProba($program['pred_proba']) }}%
                @endif
            </span>
        </div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">
            {{ $program['pg_title'] }}
        </h1>
        <div class="text-xs text-gray-600 bg-slate-50 px-3 py-2.5 rounded-xl border border-slate-100 flex flex-wrap items-center gap-x-3 gap-y-1.5">
            <span class="font-bold text-gray-800">{{ str_replace("_", "\u{2008}", $station) }}</span>
            <span class="font-mono text-gray-500">{{ $d_s }}<span class="font-sans">&thinsp;</span>{{ $dw_s }}<span class="font-sans">&thinsp;</span>{{ $t_s }}</span>
            <span class="font-mono bg-gray-200/60 text-gray-700 px-1.5 py-0.5 rounded font-medium">{{ $dti_m }}<span class="font-sans">&thinsp;</span>min</span>
            @if($genre_lbl)
                <span class="inline-block whitespace-nowrap px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold tracking-wide">{{ $genre_lbl }}</span>
            @endif
        </div>
    </div>

    <div class="mt-4 space-y-4 text-sm text-gray-700 leading-relaxed">
        @if(!empty($program['pg_detail']))
            <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                <p class="whitespace-pre-wrap text-[13px] text-gray-800">{{ $program['pg_detail'] }}</p>
            </div>
        @endif
    </div>
</div>

@if (session()->has('message') and session('message'))
    <div id="toast" class="fixed bottom-40 inset-x-4 max-w-sm mx-auto bg-gray-900/95 text-white text-sm px-4 py-3 rounded-xl shadow-2xl z-50 text-center font-medium backdrop-blur-sm transition-opacity duration-150 ease-out">
        {{ session('message') }}
    </div>
@endif
<div class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 shadow-lg p-4 pb-safe z-50">
    <div class="max-w-md mx-auto px-4">
        <form id="sortForm" action="{{ route('programs.interact', array_merge(['pgm_uid' => $program['pgm_uid'], 'randomwalk' => $randomwalk], $backQueryParams ?? [])) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="interaction" id="interactionInput" value="">
            <div id="buttonGrid" class="grid grid-cols-4 gap-3">
                @if((int)$randomwalk === 1)
                <button id="skipButton" type="button" onclick="submitForm('')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-gray-700 bg-gray-100 active:bg-gray-200 border border-gray-300 shadow-sm focus:outline-none">
                    <span class="text-base font-bold">Skip</span>
                    <span class="text-xs font-medium mt-0.5">
                        <kbd class="inline-block min-w-[18px] text-center px-1.5 py-0.5 text-[10px] font-mono font-bold text-gray-800 bg-white border border-gray-300 border-b-2 rounded shadow-sm select-none">Esc</kbd>
                    </span>
                </button>
                @else
                <a href="{{ route('programs.index', $backQueryParams ?? []) }}#pgm-{{ $program['pgm_uid'] }}" 
                    id="backToUrlLink"
                    class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-indigo-700 bg-indigo-50 active:bg-indigo-100 border border-indigo-200 shadow-sm text-center">
                    <span class="text-base font-bold">Back</span>
                    <span class="text-xs font-medium mt-0.5">
                        <kbd class="inline-block min-w-[18px] text-center px-1.5 py-0.5 text-[10px] font-mono font-bold text-gray-800 bg-white border border-gray-300 border-b-2 rounded shadow-sm select-none">Esc</kbd>
                    </span>
                </a>
                @endif

                <button type="button" onclick="submitForm('p')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-white bg-green-600 active:bg-green-700 shadow-sm focus:outline-none">
                    <span class="text-base font-bold">Posi</span>
                    <span class="text-xs font-medium mt-0.5">
                        <kbd class="inline-block min-w-[18px] text-center px-1.5 py-0.5 text-[10px] font-mono font-bold text-gray-800 bg-white border border-gray-300 border-b-2 rounded shadow-sm select-none">1</kbd>
                    </span>
                </button>

                <button type="button" onclick="submitForm('n')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-white bg-red-600 active:bg-red-700 shadow-sm focus:outline-none">
                    <span class="text-base font-bold">Nega</span>
                    <span class="text-xs font-medium mt-0.5">
                        <kbd class="inline-block min-w-[18px] text-center px-1.5 py-0.5 text-[10px] font-mono font-bold text-gray-800 bg-white border border-gray-300 border-b-2 rounded shadow-sm select-none">2</kbd>
                    </span>
                </button>

                <button type="button" onclick="submitForm('_')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-gray-700 bg-gray-100 active:bg-gray-200 border border-gray-300 shadow-sm focus:outline-none">
                    <span class="text-base font-bold">Neut</span>
                    <span class="text-xs font-medium mt-0.5">
                        <kbd class="inline-block min-w-[18px] text-center px-1.5 py-0.5 text-[10px] font-mono font-bold text-gray-800 bg-white border border-gray-300 border-b-2 rounded shadow-sm select-none">3</kbd>
                    </span>
                </button>

            </div>
        </form>
    </div>
</div>
<script>
function submitForm(interaction) {
    const existingToast = document.getElementById('toast');
    if (existingToast) {
        existingToast.classList.add('opacity-0');
    }
    document.getElementById('interactionInput').value = interaction;
    document.getElementById('sortForm').submit();
}

window.addEventListener('keydown', (e) => {
    // 入力フォーム等にフォーカスが当たっている場合は発火させない
    if (e.target.tagName === 'INPUT' && e.target.type === 'text') return;
    
    if (e.key === '1') submitForm('p');
    if (e.key === '2') submitForm('n');
    if (e.key === '3') submitForm('_');
    if (e.key === 'Escape') {
        if ({{ (int)$randomwalk === 1 ? '1' : '0' }} === 1) {
            submitForm('');
        }
        else {
            const backLink = document.getElementById('backToUrlLink');
            if (backLink) {
                event.preventDefault(); // ブラウザ標準の戻る挙動と重なる場合のバッティング防止
                backLink.click(); // リンクのクリックを擬似的に発火して、検索条件・ハッシュ維持のまま遷移
            }
        }
    }
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