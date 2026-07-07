@extends('layouts.app') {{-- ←共通レイアウトファイルのパスを指定 --}}

@section('title', '文字置換設定 - tvlike')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xs border border-gray-100 p-6 sm:p-8">
    <div class="mb-6 border-b border-gray-100 pb-4">
        <h2 class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
            ⚙️ 端末別・外字置換設定
        </h2>
        <p class="text-sm text-gray-500 mt-1 leading-relaxed">
            お使いの端末（iPhoneや古いブラウザ等）で表示できず、トーフ（文字化け）になる文字をチェックしてください。安全な互換文字に一括置換します。
        </p>
    </div>

    {{-- 保存完了時のステータスメッセージ表示 --}}
    @if(session('status'))
        <div class="mb-6 flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium">
            <span>✅</span>
            {{ session('status') }}
        </div>
    @endif

    <form action="{{ route('settings.charsUpdate') }}" method="POST">
        @csrf

        {{-- Tailwindのグリッドシステムでレスポンシブに並べる --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($charDefinitions as $key => $target)
                @php $cookieKey = "chr_{$key}"; @endphp
                
                <label class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer hover:bg-indigo-50/40 hover:border-indigo-200 transition group select-none">
                    {{-- チェックボックス（Tailwind標準カスタムスタイル） --}}
                    <input 
                        type="checkbox" 
                        name="chars[{{ $key }}]" 
                        value="1" 
                        class="w-4 h-4 rounded-sm border-gray-300 text-indigo-600 focus:ring-indigo-500/30 accent-indigo-600 cursor-pointer"
                        {{ (isset($clientSettings) && $clientSettings->$cookieKey === '1') ? 'checked' : '' }}
                    >
                    
                    {{-- 置換の対応視覚化（等幅フォントで綺麗に整列） --}}
                    <div class="flex items-center justify-between flex-1 font-mono text-sm tracking-tight text-gray-700 group-hover:text-indigo-900">
                        <span class="text-base font-sans">{{ $target[0] }}</span>
                        <span class="text-xs text-gray-400 mx-1">➔</span>
                        <span class="font-semibold text-gray-600 bg-white px-1.5 py-0.5 rounded border border-gray-100 shadow-3xs">{{ $target[1] }}</span>
                    </div>
                </label>
            @endforeach
        </div>

        {{-- 保存ボタン（テンプレートのヘッダー色に合わせたIndigoスタイル） --}}
        <div class="mt-8 pt-4 border-t border-gray-100 flex justify-end">
            <button 
                type="submit" 
                class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-sm hover:shadow-md transition cursor-pointer flex items-center justify-center gap-2"
            >
                設定を保存する
            </button>
        </div>
    </form>
</div>
@endsection