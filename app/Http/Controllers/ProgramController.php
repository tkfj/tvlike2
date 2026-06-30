<?php

namespace App\Http\Controllers;

use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
{
    // 一覧画面
    public function index(Request $request)
    {
        $interaction = $request->input('interaction', ['p','n','_']);
        $prediction = $request->input('prediction', ['p']);
        $keyword = $request->input('keyword');
        $sort = $request->input('sort', 'start_asc');
        $limit = $request->input('limit', '500');
        $future_only = $request->input('future_only', '1');
        $pred_only = $request->input('pred_only', '1');
        $tgtst_only = $request->input('tgtst_only', '1');

        $query = "
            WITH tvlike1 AS (
            SELECT *,
            ROW_NUMBER() OVER(PARTITION BY bsdate, tuner, station_id, pg_start, pg_end, pg_title ORDER BY asof DESC) AS interaction_rank
            FROM tvlike.interactions
            ),
            tvlike0 AS (
            SELECT *
            FROM tvlike1
            WHERE interaction_rank = 1
            )
            SELECT
              m.*,
              NULLIF(m.genre, '') AS genre,
              i.interaction AS interaction_next 
            FROM tvml.tvml AS m
            LEFT OUTER JOIN tvlike0 AS i
            ON i.bsdate = m.bsdate
            AND i.tuner = m.tuner
            AND i.station_id = m.station_id
            AND i.pg_start = m.pg_start
            AND i.pg_end = m.pg_end
            AND i.pg_title = m.pg_title
            WHERE 1=1
        ";
        $params = [];
        if($tgtst_only === '1') {
            $query .= " AND m.is_target=1";
        }
        if($pred_only === '1') {
            $query .= " AND m.pred_label IN ('p','n')";
        }
        if($future_only === '1') {
            $currenttime = new DateTimeImmutable('now', new DateTimeZone('Asia/Tokyo'));
            $currentdates = $currenttime->format('Ymd');
            $query .= " AND m.bsdate >= :bsdate";
            $params['bsdate'] = $currentdates;
        }

        $intr_conditions = [];
        if (in_array('_', $interaction)) {
            $intr_conditions[] = "(interaction_next = '_' OR interaction_next IS NULL AND (m.interaction IS NULL OR m.interaction = '' OR m.interaction = '_'))";
        }
        if (in_array('p', $interaction)) {
            $intr_conditions[] = "(interaction_next = 'p' OR interaction_next IS NULL AND m.interaction = 'p')";
        }
        if (in_array('n', $interaction)) {
            $intr_conditions[] = "(interaction_next = 'n' OR interaction_next IS NULL AND m.interaction = 'n')";
        }
        if (!empty($intr_conditions)) {
            $query .= " AND (" . implode(' OR ', $intr_conditions) . ")";
        } else {
            // もし全チェックが外されたら、何も表示しないように絶対偽の条件を挟む
            $query .= " AND 1=0";
        }

        $pred_conditions = [];
        if (in_array('_', $prediction)) {
            $pred_conditions[] = "(m.pred_label IS NULL OR m.pred_label = '' OR m.pred_label = '_')";
        }
        if (in_array('p', $prediction)) {
            $pred_conditions[] = "m.pred_label = 'p'";
        }
        if (in_array('n', $prediction)) {
            $pred_conditions[] = "m.pred_label = 'n'";
        }
        if (!empty($pred_conditions)) {
            $query .= " AND (" . implode(' OR ', $pred_conditions) . ")";
        } else {
            // もし全チェックが外されたら、何も表示しないように絶対偽の条件を挟む
            $query .= " AND 1=0";
        }
        
        // 検索キーワード
        if (!empty($keyword)) {
            $query .= " AND (m.pg_title LIKE :keyword_title OR m.pg_detail LIKE :keyword_detail)";
            $params['keyword_title'] = '%' . $keyword . '%';
            $params['keyword_detail'] = '%' . $keyword . '%';
        }

        switch ($sort) {
            case 'prob_desc':
                $query .= " ORDER BY m.pred_proba DESC, m.pg_start ASC";
                break;
            case 'prob_asc':
                $query .= " ORDER BY m.pred_proba ASC, m.pg_start ASC";
                break;
            case 'start_desc':
                $query .= " ORDER BY m.bsdate DESC, m.pg_start DESC, m.pg_end DESC";
                break;
            case 'start_asc':
            default:
                $query .= " ORDER BY m.bsdate ASC, m.pg_start ASC, m.pg_end ASC";
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

        $genre_map = $this->get_genre_map();
        return view('programs.index', compact(
            'programs',
            'keyword',
            'genre_map',
            'sort',
            'limit',
            'future_only',
            'pred_only',
            'tgtst_only',
            'prediction',
            'interaction'
        ));
    }

    private function get_genre_map() {
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
        return $genre_map;
    }
    // 詳細画面
    public function show(Request $request, $pgm_uid)
    {
        $randomwalk = (int) $request->query('randomwalk', 0);
        $backQueryParams = $request->except(['randomwalk']);

        $query = "
            WITH tvlike1 AS (
            SELECT *,
            ROW_NUMBER() OVER(PARTITION BY bsdate, tuner, station_id, pg_start, pg_end, pg_title ORDER BY asof DESC) AS interaction_rank
            FROM tvlike.interactions
            ),
            tvlike0 AS (
            SELECT *
            FROM tvlike1
            WHERE interaction_rank = 1
            )
            SELECT
              m.*,
              NULLIF(m.genre, '') AS genre,
              i.interaction AS interaction_next 
            FROM tvml.tvml AS m
            LEFT OUTER JOIN tvlike0 AS i
            ON i.bsdate = m.bsdate
            AND i.tuner = m.tuner
            AND i.station_id = m.station_id
            AND i.pg_start = m.pg_start
            AND i.pg_end = m.pg_end
            AND i.pg_title = m.pg_title
            WHERE m.pgm_uid = :pgm_uid
            LIMIT 1
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
        $genre_map = $this->get_genre_map();
        return view('programs.show', compact('program', 'randomwalk', 'backQueryParams', 'genre_map'));
    }

    /**
     * ★ 詳細画面からの仕分け動作（POST）を処理
     */
    public function interact(Request $request, $pgm_uid)
    {
        if($pgm_uid === 'randomwalk') {
            $interaction = '';
            $randomwalk = 1;
            $backQueryParams = [];
        }
        else {
            $request->validate([
                'interaction' => 'nullable|in:p,n,_',
            ]);
            $interaction = $request->input('interaction');
            $randomwalk = (int) $request->query('randomwalk', 0);
            $backQueryParams = $request->except(['interaction', 'randomwalk']);
        }

        if (in_array(($interaction ?? ''), ['p','n','_'], true)) {
            DB::statement("
                INSERT INTO tvlike.interactions (
                    pgm_uid,
                    asof,
                    tuner,
                    bsdate,
                    station_id,
                    station_name,
                    pgm_station_name,
                    pid,
                    event_id,
                    pg_start,
                    pg_end,
                    pg_title,
                    pg_detail,
                    genre,
                    link,
                    interaction
                )
                SELECT
                    pgm_uid,
                    asof,
                    tuner,
                    bsdate,
                    station_id,
                    station_name,
                    pgm_station_name,
                    pid,
                    event_id,
                    pg_start,
                    pg_end,
                    pg_title,
                    pg_detail,
                    genre,
                    link,
                    :interaction
                FROM tvguide.programs
                WHERE
                pgm_uid = :pgm_uid
                LIMIT 1
                ON CONFLICT(pgm_uid)
                DO UPDATE SET
                    asof = EXCLUDED.asof,
                    tuner = EXCLUDED.tuner,
                    bsdate = EXCLUDED.bsdate,
                    station_id = EXCLUDED.station_id,
                    station_name = EXCLUDED.station_name,
                    pgm_station_name = EXCLUDED.pgm_station_name,
                    pid = EXCLUDED.pid,
                    event_id = EXCLUDED.event_id,
                    pg_start = EXCLUDED.pg_start,
                    pg_end = EXCLUDED.pg_end,
                    pg_title = EXCLUDED.pg_title,
                    pg_detail = EXCLUDED.pg_detail,
                    genre = EXCLUDED.genre,
                    link = EXCLUDED.link,
                    interaction = EXCLUDED.interaction,
                    updated_at = DATETIME('now','localtime')
                ;
            ", [
                'pgm_uid' => $pgm_uid,
                'interaction' => $interaction
            ]);
        }

        $currenttime = new DateTimeImmutable('now', new DateTimeZone('Asia/Tokyo'));
        $pasttime = $currenttime->modify('-7 days');
        $pastdates = $pasttime->format('Ymd');
        $nextProgramUid = $pgm_uid;
        if ($randomwalk == 1) {
            $rnd = rand(1,100);
            if ($rnd <= 33) {
                $randomwalk_pred = "n";
            }
            elseif ($rnd <= 50) {
                $randomwalk_pred = "p";
            }
            else {
                $randomwalk_pred = null;
            }
            $params_ = [];
            if(is_null($randomwalk_pred)) {
                $with_ = "tvml1 AS (
                    SELECT * FROM tvml.tvml
                    WHERE pgm_uid != :current_uid
                    AND is_target = 1
                    AND is_preinstalled = 0
                    AND bsdate >= :pastdates
                    ORDER BY RANDOM() DESC
                    LIMIT 1
                )";
                $params_['current_uid'] = $pgm_uid;
                $params_['pastdates'] = $pastdates;
            }
            else {
                $with_ = "tvml1 AS (
                    SELECT * FROM tvml.tvml
                    WHERE pgm_uid != :current_uid
                    AND is_target = 1
                    AND is_preinstalled = 0
                    AND bsdate >= :pastdates
                    AND pred_label = :randomwalk_pred
                    AND (interaction IS NULL OR interaction = '_')
                    ORDER BY RANDOM() DESC
                    LIMIT 1
                )";
                $params_['current_uid'] = $pgm_uid;
                $params_['pastdates'] = $pastdates;
                $params_['randomwalk_pred'] = $randomwalk_pred;
            }
            $nextProgram = DB::selectOne("
                WITH
                {$with_}
                SELECT pgm_uid
                FROM tvml1
            ", $params_);
            if ($nextProgram) {
                $nextProgramUid = $nextProgram->pgm_uid;
            }
            else {
                $nextProgramUid = null;
            }
        }

        // ステータスに応じた日本語メッセージをトースト用に作成
        $interactionLabels = ['p' => '「Positive」に仕分けました', 'n' => '「Negative」に仕分けました', '_' => '「Neutral」に仕分けました', '' => 'スキップしました'];
        if($pgm_uid==='randomwalk') {
            $msg = '';
        }
        else {
            $msg = $interactionLabels[$interaction] ?? '保存しました';
        }

        if ($nextProgramUid) {
            // 次の未処理番組がある場合、その画面へ遷移（やりなおす用に現在のUIDを渡す）
            $redirect = redirect()
                ->route('programs.show', array_merge([
                    'pgm_uid' => $nextProgramUid,
                    'randomwalk' => $randomwalk
                ], $backQueryParams))
                ->with([
                    'message' => $msg
                ]);
        } else {
            // 全て処理し終えたら一覧画面へ戻る
            $redirect = redirect()
                ->route('programs.index')
                ->with(['message' => $msg . '（すべての未処理番組の仕分けが完了しました！）']);
        }
        return $redirect;
    }
}