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
    $dts_s = $dts->format('Y-m-d H:i');
    $dte = DateTime::createFromFormat('YmdHi', $program['pg_end']);
    $dti = $dte->diff($dts);
    $dti_m = ($dti->days * 24 * 60) + ($dti->h * 60) + $dti->i;
    $station = $program['pgm_station_name']=='Unknown' ? $program['station_name'] : str_replace("_", " ", $program['pgm_station_name']);

    $genre_cd = $program['genre'] ?? '_';
    $genre_lbl = $genre_map[$genre_cd] ?? NULL;

@endphp

@extends('layouts.app')

@section('title', ($program['pg_title'] ?? '番組詳細') . ' - 仕分け')

@section('content')
<div class="w-full md:max-w-md mx-auto px-4 pt-2 pb-32">

    <div class="flex flex-col space-y-3 border-b border-gray-200 pb-4">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-400 font-mono">UID: #{{ $program['pgm_uid'] }}</span>
            @if((int)$randomwalk === 0)
                <a href="{{ route('programs.index', $backQueryParams ?? []) }}#pgm-{{ $program['pgm_uid'] }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    ← 一覧に戻る
                </a>
            @endif
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium font-mono {{ $interactionColors['bg'] }}">
                True:
                {{ str_replace(['p','n','_'], ['P','N','-'], $interactionNext ?? $interaction) }}{{ $interactionStar }}
            </span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium font-mono {{ $predLabelColors['bg'] }}">
                Pred:
                {{ str_replace(['p','n','_'], ['P','N','-'], $pred_label) }}
                @if($program['pred_proba'])
                ({{ number_format($program['pred_proba']*100, 1) }}%)
                @endif
            </span>
        </div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">
            {{ $program['pg_title'] }}
        </h1>
        <div class="text-xs text-gray-600 bg-slate-50 px-3 py-2.5 rounded-xl border border-slate-100 flex flex-wrap items-center gap-x-3 gap-y-1.5">
            <span class="font-bold text-gray-800">{{ $station ?? '???' }}</span>
            <span class="font-mono text-gray-500">{{ $dts_s }}</span>
            <span class="font-mono bg-gray-200/60 text-gray-700 px-1.5 py-0.5 rounded font-medium">{{ $dti_m }} min</span>
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
    <div class="max-w-md mx-auto">
        <form id="sortForm" action="{{ route('programs.interact', array_merge(['pgm_uid' => $program['pgm_uid'], 'randomwalk' => $randomwalk], $backQueryParams ?? [])) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="interaction" id="interactionInput" value="">

            <div id="buttonGrid" class="grid {{ (int)$randomwalk === 1 ? 'grid-cols-4' : 'grid-cols-3' }} gap-3">
                
                <button id="skipButton" type="button" onclick="submitForm('')" 
                        class="{{ (int)$randomwalk === 1 ? '' : 'hidden' }} flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-gray-700 bg-gray-100 active:bg-gray-200 border border-gray-300 shadow-sm focus:outline-none">
                    <span class="text-base font-bold">スキップ</span>
                    <span class="text-xs font-medium mt-0.5">
                        <kbd class="inline-block min-w-[18px] text-center px-1.5 py-0.5 text-[10px] font-mono font-bold text-gray-800 bg-white border border-gray-300 border-b-2 rounded shadow-sm select-none">Esc</kbd>
                    </span>
                </button>

                <button type="button" onclick="submitForm('p')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-white bg-green-600 active:bg-green-700 shadow-sm focus:outline-none">
                    <span class="text-base font-bold">興味あり</span>
                    <span class="text-xs font-medium mt-0.5">
                        <kbd class="inline-block min-w-[18px] text-center px-1.5 py-0.5 text-[10px] font-mono font-bold text-gray-800 bg-white border border-gray-300 border-b-2 rounded shadow-sm select-none">1</kbd>
                    </span>
                </button>

                <button type="button" onclick="submitForm('n')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-white bg-red-600 active:bg-red-700 shadow-sm focus:outline-none">
                    <span class="text-base font-bold">興味なし</span>
                    <span class="text-xs font-medium mt-0.5">
                        <kbd class="inline-block min-w-[18px] text-center px-1.5 py-0.5 text-[10px] font-mono font-bold text-gray-800 bg-white border border-gray-300 border-b-2 rounded shadow-sm select-none">2</kbd>
                    </span>
                </button>

                <button type="button" onclick="submitForm('_')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-gray-700 bg-gray-100 active:bg-gray-200 border border-gray-300 shadow-sm focus:outline-none">
                    <span class="text-base font-bold">中立</span>
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