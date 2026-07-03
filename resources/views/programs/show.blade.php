@php
    $interaction = $program['interaction'] ?? '-';
    $interactionNext = $program['interaction_next'] ?? null;
    $pred_label = $program['pred_label'] ?? '-';
    $labelColors = [
        'P' => ['bg' => 'bg-green-100 text-green-800'],
        'N' => ['bg' => 'bg-red-100 text-red-800'],
        '-' => ['bg' => 'bg-gray-100 text-gray-800'],
    ];
    $interactionColors = $labelColors[$interactionNext ?? $interaction] ?? $labelColors['-'];
    $predLabelColors = $labelColors[$pred_label] ?? $labelColors['-'];
    $interactionStar = (is_null($interactionNext) or $interaction==$interactionNext) ? '' : '*';

    $dts = new DateTime('', new DateTimeZone('Asia/Tokyo'));
    $dts->setTimestamp(intdiv($program['start_at'],1000));
    $d_s = $dts->format('Y-m-d');
    $dw_s = $dts->format('D');
    $t_s = $dts->format('H:i');
    $dti_m = intdiv($program['duration'], 60*1000);

    $genre_filtered = $program['genres'] ? array_filter(json_decode($program['genres'], true), function($p) {
        // 番組のジャンルが制御情報または字幕/音声解説のものを除く
        return $p['lv1']!=14 && !($p['lv1']==11 && ($p['lv2']==5 || $p['lv2']==6));
    }) : [];
    $genre_labels = array_map(function($p) {
        return $p['lv1_label'] . '：' . $p['lv2_label'];
    }, $genre_filtered);

    /**
     * キワの確率を100% / 0%に激突させない丸め関数
     * @param float|null $proba 0.0〜1.0 の生確率
     * @return string|null フォーマット済みの文字列（例: "99.9", "54.2", "0.1"）
     */
    $formatProba = function ($proba) {
        if (is_null($proba)) return null;
        $p = $proba * 100; // 0.0 〜 100.0% に変換
        if ($p > 0 && $p < 0.001) {
            $val = ceil($p * 1000) / 1000;
        } elseif ($p > 99.999 && $p < 100) {
            $val = floor($p * 1000) / 1000;
        } else {
            $val = round($p, 3);
        }
        return number_format($val, 3);
    };
@endphp

@extends('layouts.app')

@section('title', ($program['pgm_title'] ?? '番組詳細') . ' - tvlike')

@section('content')
<div class="w-full md:max-w-3xl mx-auto px-4 text-xs font-medium text-gray-400 font-mono tracking-widest mb-4">
    /<span class="font-sans">&thinsp;</span>tvlike<span class="font-sans">&thinsp;</span>/<span class="font-sans">&thinsp;</span>#{{ $program['pgm_uid'] }}.{{ intdiv($program['start_at'],10000) }}
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
            {{ $program['pgm_title'] }}
        </h1>
        <div class="text-xs text-gray-600 bg-slate-50 px-3 py-2.5 rounded-xl border border-slate-100 flex flex-wrap items-center gap-x-3 gap-y-1.5">
            <span class="font-bold text-gray-800">{{ str_replace(" ","\u{2009}",Normalizer::normalize($program['service_name'], Normalizer::FORM_KC)) }}</span>
            <span class="font-mono text-gray-500">{{ $d_s }}<span class="font-sans">&thinsp;</span>{{ $dw_s }}<span class="font-sans">&thinsp;</span>{{ $t_s }}</span>
            <span class="font-mono bg-gray-200/60 text-gray-700 px-1.5 py-0.5 rounded font-medium">{{ $dti_m }}<span class="font-sans">&thinsp;</span>min</span>
            @if($genre_labels)
                <span class="inline-block whitespace-nowrap gap-0">
                @foreach ($genre_labels as $genre_label)
                    <span class="bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded font-midium">{{ $genre_label }}</span>
                @endforeach
                </span>
            @endif
        </div>
    </div>

    <div class="mt-4 space-y-4 text-[13px] text-gray-700 leading-relaxed">
        @if(!empty($program['pgm_description']))
            <div class="bg-slate-50/50 rounded-xl p-4 border border-slate-100 shadow-sm">
                <p class="whitespace-pre-wrap text-gray-800 leading-normal">{{ $program['pgm_description'] }}</p>
            </div>
        @endif

        @if(!empty($program['extended']))
            <div class="bg-slate-50/30 rounded-xl border border-slate-100 shadow-sm overflow-hidden divide-y divide-slate-100">
                @foreach (json_decode($program['extended'], true) as $ex_key => $ex_val)
                    <div class="p-4 sm:grid sm:grid-cols-4 sm:gap-4 sm:px-5">
                        <dt class="text-[12px] font-bold text-slate-600 font-sans tracking-wider inline-flex items-center border-l-2 border-slate-400 pl-2 self-start sm:w-full h-5 sm:h-auto">
                            {{ Normalizer::normalize($ex_key, Normalizer::FORM_KC) }}
                        </dt>
                        <dd class="mt-1.5 text-gray-800 sm:mt-0 sm:col-span-3">
                            <p class="whitespace-pre-wrap leading-normal text-[13px]">{{ Normalizer::normalize($ex_val, Normalizer::FORM_KC) }}</p>
                        </dd>
                    </div>
                @endforeach
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
        <form id="sortForm" action="{{ route('programs.interact', array_merge(['id' => $program['pgm_uid'].'.'.$program['start_at'], 'randomwalk' => $randomwalk], $backQueryParams ?? [])) }}" method="POST" class="space-y-4">
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
                <a href="{{ route('programs.index', $backQueryParams ?? []) }}#pgm-{{ $program['pgm_uid'] }}-{{ $program['start_at'] }}" 
                    id="backToUrlLink"
                    class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-indigo-700 bg-indigo-50 active:bg-indigo-100 border border-indigo-200 shadow-sm text-center">
                    <span class="text-base font-bold">Back</span>
                    <span class="text-xs font-medium mt-0.5">
                        <kbd class="inline-block min-w-[18px] text-center px-1.5 py-0.5 text-[10px] font-mono font-bold text-gray-800 bg-white border border-gray-300 border-b-2 rounded shadow-sm select-none">Esc</kbd>
                    </span>
                </a>
                @endif

                <button type="button" onclick="submitForm('P')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-white bg-green-600 active:bg-green-700 shadow-sm focus:outline-none">
                    <span class="text-base font-bold">Posi</span>
                    <span class="text-xs font-medium mt-0.5">
                        <kbd class="inline-block min-w-[18px] text-center px-1.5 py-0.5 text-[10px] font-mono font-bold text-gray-800 bg-white border border-gray-300 border-b-2 rounded shadow-sm select-none">1</kbd>
                    </span>
                </button>

                <button type="button" onclick="submitForm('N')" 
                        class="flex flex-col items-center justify-center py-3.5 px-2 rounded-xl text-white bg-red-600 active:bg-red-700 shadow-sm focus:outline-none">
                    <span class="text-base font-bold">Nega</span>
                    <span class="text-xs font-medium mt-0.5">
                        <kbd class="inline-block min-w-[18px] text-center px-1.5 py-0.5 text-[10px] font-mono font-bold text-gray-800 bg-white border border-gray-300 border-b-2 rounded shadow-sm select-none">2</kbd>
                    </span>
                </button>

                <button type="button" onclick="submitForm('-')" 
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
    
    if (e.key === '1') submitForm('P');
    if (e.key === '2') submitForm('N');
    if (e.key === '3') submitForm('-');
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