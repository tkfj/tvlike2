<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\View;

class SettingController extends Controller
{
    public function chars()
    {
        return view('settings.chars');
    }
    public function charsUpdate(Request $request)
    {
        $postedChars = $request->input('chars', []); 
        $charDefinitions = View::shared('charDefinitions');
        $cookieData = [];
        foreach ($charDefinitions as $key => $target) {
            $configKey = "chr_{$key}";
            $cookieData[$configKey] = isset($postedChars[$key]) ? '1' : '0';
        }
        $response = back()->with('status', '設定を保存しました');
        $jsonString = json_encode($cookieData);
        $response->withCookie(cookie()->forever('epg_char_settings', $jsonString));

        return $response;
    }
}