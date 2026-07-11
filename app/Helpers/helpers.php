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


if (!function_exists('normalize_zen_han_text')) {
    /**
     * 英数と基本的な記号をASCIIに、半角カナを全角に統一。
     * （Python側の normalize_zen_han_text と完全等価）
     *
     * @param string|null $text
     * @return string|null
     */
    function normalize_zen_han_text(?string $text): ?string
    {
        if (is_null($text) || $text === '') {
            return null;
        }
        // \x{3000}         : 全角スペース
        // \x{FF01}-\x{FF5D}: 全角英数・記号（～, ｟, ｠を除く）
        // \x{FF61}-\x{FF9F}: 半角カナ・半角句読点
        $pattern = '/[\x{3000}\x{FF01}-\x{FF5D}\x{FF61}-\x{FF9F}]+/u';
        $text = preg_replace_callback($pattern, function ($matches) {
            return Normalizer::normalize($matches[0], Normalizer::FORM_KC);
        }, $text);

        // \uFF5E (全角チルダ) を \u301C (波ダッシュ) に置換して寄せる
        $text = str_replace("\u{FF5E}", "\u{301C}", $text);

        // 前後の空白削除
        $text = preg_replace('/^\s+/u', '', $text);
        $text = preg_replace('/\s+$/u', '', $text);

        if (mb_strlen($text) === 0) {
            return null;
        }

        return $text;
    }
}