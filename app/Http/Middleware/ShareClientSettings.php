<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Services\EpgTextCleaner;
use Symfony\Component\HttpFoundation\Response;

class ShareClientSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. クリーナーをコンテナから取得（自動で最新のCookieを反映して作られます）
        $cleaner = app(EpgTextCleaner::class);

        // 2. クリーナーが持っている定義リストをViewにシェア
        $chars = $cleaner->getDefinitions();
        View::share('charDefinitions', $chars);

        // 3. 設定画面のチェックボックスの「現在の状態」を復元してViewにシェア
        $cookieValue = $request->cookie('epg_char_settings');
        $savedSettings = $cookieValue ? json_decode($cookieValue, true) : [];

        $settings = [];
        foreach ($chars as $key => $target) {
            $configKey = "chr_{$key}";
            $settings[$configKey] = $savedSettings[$configKey] ?? '0';
        }
        View::share('clientSettings', (object) $settings);

        return $next($request);
    }
}
