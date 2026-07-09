<?php

if (!function_exists('normalize_epg_text')) {
    /**
     * @deprecated DBの正規化済みカラムを使用すること
     * EPGテキストをaKVモードで正規化する
     * Unicodeの正規化は[映]などの四角囲みを外してしまうので使用しない。
     * 'a': 全角英数記を半角に変換
     * 's': 全角スペースを半角に変換
     * 'K': 半角カタカナを全角に変換
     * 'V': 濁点付きの半角カナを1文字の全角カナに結合（ヴ などの対応）
     */
    function normalize_epg_text(?string $text): ?string
    {
        if (is_null($text)) {
            return null;
        }
        return mb_convert_kana($text, "asKV", "UTF-8");
    }
}
if (!function_exists('clean_epg_text')) {
    /**
     * Settingsに基づいて[映]などのARIB外字の正規化を行う
     */
    function clean_epg_text(?string $text): string {
        return app(App\Services\EpgTextCleaner::class)->clean($text);
    }
}