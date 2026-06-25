@extends('layouts.app')

@section('title', ($item['pg_title'] ?? '番組詳細') . ' - タグ付け')

@section('content')
<div class="mb-4">
    <a href="{{ route('programs.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; 一覧に戻る</a>
</div>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-4 border-b pb-4">
        <span class="text-xs font-mono text-gray-400">TASK-ID: #{{ $program['pgm_uid'] }}</span>
        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">更新日: {{ $program['updated_at'] ?? '' }}</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $program['pg_title'] }}</h1>

    <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-md mb-6">
        <div>
            <span class="block text-xs text-gray-400 uppercase font-semibold">Detail</span>
            <span class="text-sm font-medium text-gray-700">{{ $program['pg_detail'] }}</span>
        </div>
        <div>
            <span class="block text-xs text-gray-400 uppercase font-semibold">Interaction</span>
            <span class="text-sm font-medium text-gray-700">{{ $program['interaction'] }}</span>
        </div>
        <div>
            <span class="block text-xs text-gray-400 uppercase font-semibold">Preict Label</span>
            <span class="text-sm font-medium text-gray-700">{{ $program['pred_label'] }}</span>
        </div>
        <div>
            <span class="block text-xs text-gray-400 uppercase font-semibold">Preict Proba</span>
            <span class="text-sm font-medium text-gray-700">{{ $program['pred_proba'] }}</span>
        </div>
    </div>
</div>
@endsection
