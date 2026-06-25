@extends('layouts.app')

@section('title', '番組検索一覧')

@section('content')
<h1 class="text-2xl font-bold mb-6 border-b pb-2 text-gray-700">番組検索一覧</h1>
<form action="{{ route('programs.index') }}" method="GET" class="mb-6 space-y-3 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
    <div class="flex gap-2">
        <input 
            type="text" 
            name="keyword" 
            value="{{ $keyword ?? '' }}" 
            placeholder="番組名で検索..." 
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white text-sm"
        >
        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 font-medium text-sm transition shrink-0">
            検索
        </button>
        @if(!empty($keyword))
            <a href="{{ route('programs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow hover:bg-gray-300 flex items-center text-sm shrink-0">
                クリア
            </a>
        @endif
    </div>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pt-3 border-t border-gray-100 text-sm">
        <div class="flex items-center gap-4 bg-gray-50 px-3 py-1.5 rounded-md border border-gray-200">
            <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Prediction:</span>
            
            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-medium text-gray-700 select-none">
                <input 
                    type="checkbox" 
                    name="labels[]" 
                    value="p" 
                    onchange="this.form.submit()"
                    {{ in_array('p', $pred_labels) ? 'checked' : '' }}
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <span class="px-1.5 py-0.5 bg-green-50 text-green-700 rounded border border-green-200 text-[11px]">Positive</span>
            </label>

            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-medium text-gray-700 select-none">
                <input 
                    type="checkbox" 
                    name="labels[]" 
                    value="n" 
                    onchange="this.form.submit()"
                    {{ in_array('n', $pred_labels) ? 'checked' : '' }}
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <span class="px-1.5 py-0.5 bg-red-50 text-red-700 rounded border border-red-200 text-[11px]">Negative</span>
            </label>

            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-medium text-gray-700 select-none">
                <input 
                    type="checkbox" 
                    name="labels[]" 
                    value="_" 
                    onchange="this.form.submit()"
                    {{ in_array('_', $pred_labels) ? 'checked' : '' }}
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded border border-gray-200 text-[11px]">n/a</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-4 shrink-0">
            <div class="flex items-center gap-1.5">
                <label for="sort" class="text-xs text-gray-500 font-medium">Sort:</label>
                <select name="sort" id="sort" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-white border border-gray-300 rounded-md text-xs text-gray-700 focus:ring-2 focus:ring-indigo-500">
                    <option value="start_desc" {{ $sort === 'start_desc' ? 'selected' : '' }}>放送開始が新しい順</option>
                    <option value="start_asc" {{ $sort === 'start_asc' ? 'selected' : '' }}>放送開始が古い順</option>
                    <option value="prob_desc" {{ $sort === 'prob_desc' ? 'selected' : '' }}>予測確率（Prob）が高い順</option>
                    <option value="prob_asc" {{ $sort === 'prob_asc' ? 'selected' : '' }}>予測確率（Prob）が低い順</option>
                </select>
            </div>

            <div class="flex items-center gap-1.5">
                <label for="limit" class="text-xs text-gray-500 font-medium">Count:</label>
                <select name="limit" id="limit" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-white border border-gray-300 rounded-md text-xs text-gray-700 focus:ring-2 focus:ring-indigo-500">
                    <option value="100" {{ $limit === '100' ? 'selected' : '' }}>100</option>
                    <option value="500" {{ $limit === '500' ? 'selected' : '' }}>500</option>
                    <option value="2000" {{ $limit === '2000' ? 'selected' : '' }}>2000</option>
                </select>
            </div>
        </div>
    </div>
</form>
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
                            <span class="px-2 py-0.5 border text-[11px] rounded font-mono {{ $get_badge_class($prog['interaction'] ?? '') }}">
                                Interaction: {{ $prog['interaction'] ?? '-' }}
                            </span>
                            
                            <span class="px-2 py-0.5 border text-[11px] rounded font-mono {{ $get_badge_class($prog['pred_label'] ?? '') }}">
                                Prediction: {{ $prog['pred_label'] ?? '-' }}
                            </span>
                            
                            <span class="font-mono text-gray-500 text-[11px]">
                                Proba: {{ number_format((float)($prog['pred_proba'] ?? 0), 3) }}
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
