@extends('layouts.app') {{-- 共通レイアウトのファイル名に合わせて適宜変更してください --}}

@section('title', '絶対防衛ライン設定')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
    
    {{-- タイトル部 --}}
    <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100">
        <span class="text-xl">🛡️</span>
        <h1 class="text-lg font-bold tracking-wide text-gray-800">絶対防衛ライン設定 (YAML)</h1>
    </div>

    {{-- 保存成功時のメッセージ --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center gap-2 animate-fade-in">
            <span>✅</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- YAMLパースエラー等の表示 --}}
    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-mono white-space-pre-wrap leading-relaxed">
            <div class="font-sans font-bold text-sm mb-1 flex items-center gap-2">
                <span>⚠️</span> 構文エラーを検知しました
            </div>
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif

    {{-- 入力フォーム --}}
    <form action="{{ route('adl.adlUpdate') }}" method="POST" class="space-y-4">
        @csrf
        
        <div class="relative">
            <textarea 
                name="adl_yaml" 
                spellcheck="false" 
                class="w-full h-[600px] p-4 bg-gray-50 border border-gray-200 rounded-xl font-mono text-sm leading-relaxed text-gray-700 focus:outline-none focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-100 transition duration-150 resize-y"
                placeholder="features:&#10;  actress1:&#10;    monotone_constraints: 1"
            >{{ old('adl_yaml', $adl_yaml) }}</textarea>
        </div>

        {{-- ボタン配置エリア --}}
        <div class="flex items-center justify-end pt-2">
            <button 
                type="submit" 
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-sm rounded-xl shadow-sm hover:shadow transition duration-150 cursor-pointer"
            >
                設定を保存
            </button>
        </div>
    </form>
</div>
@endsection