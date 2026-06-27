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
//    $genre_lbl = $genre_map[$genre_cd] ?? NULL;

@endphp

@extends('layouts.app')

@section('title', ($program['pg_title'] ?? '番組詳細') . ' - 仕分け')

@section('content')
<!-- <div class="max-w-md mx-auto px-4 pt-4 pb-32"> -->

<div class="w-full md:max-w-md mx-auto px-4 pt-2 pb-32">

    <div class="flex flex-col space-y-2 border-b border-gray-200 pb-4">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-400 font-mono">UID: #{{ $program['pgm_uid'] }}</span>
            <a href="{{ route('programs.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← 一覧に戻る
            </a>
        </div>
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $interactionColors['bg'] }}">
                Interaction: {{ $interactionNext ?? $interaction }}{{ $interactionStar }}
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $predLabelColors['bg'] }}">
                Prediction: {{ $pred_label }}
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium">
                Proba: {{ number_format((float)($program['pred_proba'] ?? 0), 4) }}
            </span>
        </div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">
            {{ $program['pg_title'] }}
        </h1>
    </div>

    <div class="mt-4 space-y-4 text-sm text-gray-700 leading-relaxed">
        @if(!empty($program['pg_detail']))
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                <p class="whitespace-pre-wrap">{{ $program['pg_detail'] }}</p>
            </div>
        @endif
        <div class="text-center text-xs text-gray-500 bg-white p-3 rounded-lg border border-gray-200">
            <div>{{ $station ?? '???' }} | {{ $dts_s }} | {{ $dti_m }} min.</div>
        </div>
    </div>
</div>

<div class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 shadow-lg p-4 pb-safe z-50">
    <div class="max-w-md mx-auto">
        <form id="sortForm" action="?" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="pgm_uid" value="{{ $program['pgm_uid'] }}"> <!-- これ要らないよね -->
            <input type="hidden" name="interaction" id="interactionInput" value="">
            <div class="flex items-center justify-center mb-3">
                <label class="inline-flex items-center cursor-pointer bg-gray-50 px-4 py-1.5 rounded-full border border-gray-200 shadow-sm active:bg-gray-100">
                    <input type="checkbox" 
                        id="randomWalk" 
                        name="randomwalk" 
                        value="1" 
                        form="sortForm"
                            {{ ($randomwalk ?? '0') === '1' ? 'checked' : '' }}
                        class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                    <span class="ml-2.5 text-xs font-semibold text-purple-700 tracking-wider flex items-center gap-1">
                        🎲 Random Walk
                    </span>
                </label>
            </div>

            <div class="grid grid-cols-3 gap-3">

                <button type="button" onclick="submitForm('p')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-white bg-green-600 active:bg-green-700 shadow-sm focus:outline-none">
                    <span class="text-lg font-bold">1</span>
                    <span class="text-xs font-medium mt-0.5">興味あり</span>
                </button>

                <button type="button" onclick="submitForm('_')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-gray-700 bg-gray-100 active:bg-gray-200 border border-gray-300 shadow-sm focus:outline-none">
                    <span class="text-lg font-bold">2</span>
                    <span class="text-xs font-medium mt-0.5">保留</span>
                </button>

                <button type="button" onclick="submitForm('n')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-white bg-red-600 active:bg-red-700 shadow-sm focus:outline-none">
                    <span class="text-lg font-bold">3</span>
                    <span class="text-xs font-medium mt-0.5">興味なし</span>
                </button>
            </div>
        </form>
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