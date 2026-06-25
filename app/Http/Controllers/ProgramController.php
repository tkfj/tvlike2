<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
{
    // ★ 共通のダミーデータ（プライベートメソッドで使い回す）
    private function getDummyData()
    {
        return [
            ['id' => 1, 'title' => '羽田スカイマーク 運用ログ', 'category' => 'インフラ', 'status' => '完了', 'updated_at' => '2026-06-25'],
            ['id' => 2, 'title' => 'SQLite :memory: 同期検証', 'category' => 'データベース', 'status' => '進行中', 'updated_at' => '2026-06-24'],
            ['id' => 3, 'title' => 'Caddy リバースプロキシ設定', 'category' => 'ネットワーク', 'status' => '未着手', 'updated_at' => '2026-06-23'],
        ];
    }

    // 一覧画面
    public function index(Request $request)
    {
        $pred_labels = $request->input('labels', ['p']);
        $keyword = $request->input('keyword');
        $sort = $request->input('sort', 'start_asc');
        $limit = $request->input('limit', '100');

        // ベースとなる生クエリ（db1 や db2 のテーブルを指定）
        // ※テーブル名やカラム名は実際のファイルに合わせて書き換えてください
        $query = "
            SELECT * 
            FROM tvml.tvml 
            WHERE is_target=1
            AND pred_label IN ('p','n')
        ";
        $params = [];

        if (!empty($pred_labels)) {
            $label_conditions = [];
            
            foreach ($pred_labels as $index => $label) {
                $param_name = "label_" . $index;
                if ($label === '_') {
                    $label_conditions[] = "(pred_label IS NULL OR pred_label = '' OR pred_label = '_')";
                } else {
                    $label_conditions[] = "pred_label = :{$param_name}";
                    $params[$param_name] = $label;
                }
            }
            $query .= " AND (" . implode(' OR ', $label_conditions) . ")";
        } else {
            // もし全チェックが外されたら、何も表示しないように絶対偽の条件を挟む
            $query .= " AND 1=0";
        }
        
        // 検索キーワード
        if (!empty($keyword)) {
            $query .= " AND (pg_title LIKE :keyword_title OR pg_detail LIKE :keyword_detail)";
            $params['keyword_title'] = '%' . $keyword . '%';
            $params['keyword_detail'] = '%' . $keyword . '%';
        }

        switch ($sort) {
            case 'prob_desc':
                $query .= " ORDER BY pred_proba DESC, pg_start ASC";
                break;
            case 'prob_asc':
                $query .= " ORDER BY pred_proba ASC, pg_start ASC";
                break;
            case 'start_desc':
                $query .= " ORDER BY pg_start DESC, pg_end DESC";
                break;
            case 'start_asc':
            default:
                $query .= " ORDER BY pg_start ASC, pg_end ASC";
                break;
        }
        $query .= " LIMIT :limit";
        $params['limit'] = (int)$limit;

        // クエリ実行
        $records = DB::select($query, $params);

        // 生のオブジェクト配列を、Bladeで扱いやすいようにコレクション（または配列）に変換
        $programs = collect($records)->map(function ($pg) {
            return (array) $pg;
        });

        $genre_map = [
            '0'=> 'ニュース/報道',
            '1'=> 'スポーツ',
            '2'=> '情報/ワイドショー',
            '3'=> 'ドラマ',
            '4'=> '音楽',
            '5'=> 'バラエティ',
            '6'=> '映画',
            '7'=> 'アニメ/特撮',
            '8'=> 'ドキュメンタリー/教養',
            '9'=> '劇場/公演',
            'A'=> '趣味/教育',
            'B'=> '福祉',
            'F'=> 'その他',
        ];
        return view('programs.index', compact('programs', 'keyword', 'genre_map', 'sort', 'limit', 'pred_labels'));
    }

    // 詳細画面
    public function show($pgm_uid)
    {
         $query = "
            SELECT * 
            FROM tvml.tvml 
            WHERE pgm_uid = :pgm_uid
        ";
        $params['pgm_uid'] = $pgm_uid;
        // クエリ実行
        $records = DB::select($query, $params);

        // 生のオブジェクト配列を、Bladeで扱いやすいようにコレクション（または配列）に変換
        $programs = collect($records)->map(function ($pg) {
            return (array) $pg;
        });

        // 指定されたIDのデータを配列から探す
        $program = $programs->firstWhere('pgm_uid', $pgm_uid);

        // 万が一IDが見つからなければ404を吐く（それっぽい挙動）
        if (!$program) {
            abort(404);
        }

        return view('programs.show', compact('program'));
    }
}