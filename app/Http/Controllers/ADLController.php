<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class ADLController extends Controller
{
    public function adl()
    {
        $query = "SELECT adl_yaml FROM adldb.adl WHERE adl_id = 1 LIMIT 1";
        $row = DB::selectOne($query);
        if ($row) {
            $adl_yaml = $row->adl_yaml;
        } 
        else {
            $adl_yaml = "# absolute defence line
feature: []
";
        }
        return view('adl.adl', compact('adl_yaml'));
    }

    public function adlUpdate(Request $request)
    {
        $adl_yaml = $request->input('adl_yaml');

        // 万が一空で送信された場合の最低限のケア
        if (empty(trim($adl_yaml))) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['adl_yaml' => '設定が空です。空白の送信はできません。']);
        }

        try {
            // SymfonyのYamlコンポーネントでパース。構文エラーがあるとParseExceptionが飛ぶ
            Yaml::parse($adl_yaml);
            
            // 構文チェックが通ったら、SQLite3のテーブルへ保存（なければINSERT、あればUPDATE）
            DB::statement("
                INSERT INTO adldb.adl (
                    adl_id,
                    adl_yaml
                )
                VALUES ( 1, :adl_yaml )
                ON CONFLICT(adl_id)
                DO UPDATE SET
                    adl_yaml = EXCLUDED.adl_yaml
                ;
            ", [
                'adl_yaml' => $adl_yaml
            ]);

            return redirect()->back()->with('success', '絶対防衛ライン設定を更新しました。');

        } catch (ParseException $exception) {
            // YAMLのパースエラーが発生した場合、エラーメッセージを画面に送り返す
            return redirect()->back()
                ->withInput() // 入力内容を保持してリダイレクト（old('yaml_text') で復元）
                ->withErrors(['yaml_text' => 'YAML構文エラー: ' . $exception->getMessage()]);
        }
    }
}