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
        $interaction = $request->input('interaction', ['P','N','-']);
        $prediction = $request->input('prediction', ['P']);
        $keyword = $request->input('keyword');
        $sort = $request->input('sort', 'start_asc');
        $limit = $request->input('limit', '500');
        $future_only = $request->input('future_only', '1');
        $pred_only = $request->input('pred_only', '1');
        $mych_only = $request->input('mych_only', '1');
        $inc_empty = $request->input('inc_empty', '0');

        $query = "
            SELECT
              tvml.*,
              UPPER(tvml.interaction) as interaction,
              tvlike.interaction AS interaction_next 
            FROM tvmldb.tvml AS tvml
            LEFT OUTER JOIN tvlikedb.tvlike AS tvlike
            ON tvlike.pgm_uid = tvml.pgm_uid
            AND tvlike.start_at > tvml.start_at - 8*24*60*60*1000
            AND tvlike.start_at < tvml.start_at + 8*24*60*60*1000
            AND COALESCE(tvlike.pgm_title, '') = COALESCE(tvml.pgm_title, '')
            WHERE 1=1
        ";
        $params = [];
        if($mych_only === '1') {
            $query .= " AND tvml.is_target_channel=1";
        }
        if($pred_only === '1') {
            $query .= " AND tvml.pred_label IN ('P','N')";
        }
        if($future_only === '1') {
            $tz = new DateTimeZone('Asia/Tokyo');
            $currenttime = new DateTimeImmutable('now', $tz);
            $currentdate = $currenttime->setTime(0,0,0,0);
            $currentepoch_ms = $currentdate->getTimestamp() * 1000; 
            $query .= " AND tvml.start_at >= :current_start";
            $params['current_start'] = $currentepoch_ms;
        }
        if($inc_empty === '0') {
            $query .= " AND ((tvml.pgm_title IS NOT NULL AND tvml.pgm_title != '') OR (tvml.pgm_description IS NOT NULL AND tvml.pgm_description != ''))";
        }

        $intr_conditions = [];
        if (in_array('-', $interaction)) {
            $intr_conditions[] = "(interaction_next = '-' OR interaction_next IS NULL AND (tvml.interaction IS NULL OR tvml.interaction = '' OR tvml.interaction = '-'))";
        }
        if (in_array('P', $interaction)) {
            $intr_conditions[] = "(interaction_next = 'P' OR interaction_next IS NULL AND tvml.interaction = 'P')";
        }
        if (in_array('N', $interaction)) {
            $intr_conditions[] = "(interaction_next = 'N' OR interaction_next IS NULL AND tvml.interaction = 'N')";
        }
        if (!empty($intr_conditions)) {
            $query .= " AND (" . implode(' OR ', $intr_conditions) . ")";
        } else {
            // もし全チェックが外されたら、何も表示しないように絶対偽の条件を挟む
            $query .= " AND 1=0";
        }

        $pred_conditions = [];
        if (in_array('-', $prediction)) {
            $pred_conditions[] = "(tvml.pred_label IS NULL OR tvml.pred_label = '' OR tvml.pred_label = '-')";
        }
        if (in_array('P', $prediction)) {
            $pred_conditions[] = "tvml.pred_label = 'P'";
        }
        if (in_array('N', $prediction)) {
            $pred_conditions[] = "tvml.pred_label = 'N'";
        }
        if (!empty($pred_conditions)) {
            $query .= " AND (" . implode(' OR ', $pred_conditions) . ")";
        } else {
            // もし全チェックが外されたら、何も表示しないように絶対偽の条件を挟む
            $query .= " AND 1=0";
        }
        
        // 検索キーワード
        if (!empty($keyword)) {
            $query .= " AND (tvml.pgm_title LIKE :keyword_title OR tvml.pgm_description LIKE :keyword_detail OR tvml.extended LIKE :keyword_extended)";
            $params['keyword_title'] = '%' . $keyword . '%';
            $params['keyword_detail'] = '%' . $keyword . '%';
            $params['keyword_extended'] = '%' . $keyword . '%';
        }

        switch ($sort) {
            case 'prob_desc':
                $query .= " ORDER BY tvml.pred_proba DESC, tvml.start_at ASC";
                break;
            case 'prob_asc':
                $query .= " ORDER BY tvml.pred_proba ASC, tvml.start_at ASC";
                break;
            case 'start_desc':
                $query .= " ORDER BY tvml.start_at DESC, tvml.duration DESC";
                break;
            case 'start_asc':
            default:
                $query .= " ORDER BY tvml.start_at ASC, tvml.duration ASC";
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

        return view('programs.index', compact(
            'programs',
            'keyword',
            'sort',
            'limit',
            'future_only',
            'pred_only',
            'mych_only',
            'inc_empty',
            'prediction',
            'interaction'
        ));
    }

    // 詳細画面
    public function show(Request $request, $id)
    {
        $randomwalk = (int) $request->query('randomwalk', 0);
        $backQueryParams = $request->except(['randomwalk']);
        sscanf($id, "%d.%d", $pgm_uid, $start_at);

        $query = "
            SELECT
              tvml.*,
              tvlike.interaction AS interaction_next 
            FROM tvmldb.tvml AS tvml
            LEFT OUTER JOIN tvlikedb.tvlike AS tvlike
            ON tvlike.pgm_uid = tvml.pgm_uid
            AND tvlike.start_at > tvml.start_at - 8*24*60*60*1000
            AND tvlike.start_at < tvml.start_at + 8*24*60*60*1000
            AND tvlike.pgm_title = tvml.pgm_title
            WHERE tvml.pgm_uid = :pgm_uid
            AND tvml.start_at > :start_at - 8*24*60*60*1000
            AND tvml.start_at < :start_at + 8*24*60*60*1000
            LIMIT 1
        ";
        $params['pgm_uid'] = $pgm_uid;
        $params['start_at'] = $start_at;
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
        return view('programs.show', compact('program', 'randomwalk', 'backQueryParams'));
    }

    /**
     * ★ 詳細画面からの仕分け動作（POST）を処理
     */
    public function interact(Request $request, $id)
    {
        if($id === 'randomwalk') {
            $interaction = '';
            $randomwalk = 1;
            $backQueryParams = [];
            $pgm_uid = null;
            $start_at = null;
        }
        else {
            $request->validate([
                'interaction' => 'nullable|in:P,N,-',
            ]);
            $interaction = $request->input('interaction');
            $randomwalk = (int) $request->query('randomwalk', 0);
            $backQueryParams = $request->except(['interaction', 'randomwalk']);
            sscanf($id, "%d.%d", $pgm_uid, $start_at);
        }

        if (in_array(($interaction ?? ''), ['P','N','-'], true)) {
            DB::statement("
                INSERT INTO tvlikedb.tvlike (
                    asof,
                    pgm_uid,
                    start_at,
                    pgm_title,
                    interaction
                )
                SELECT
                    asof,
                    pgm_uid,
                    start_at,
                    pgm_title,
                    :interaction
                FROM epgdb.epg
                WHERE pgm_uid = :pgm_uid
                AND start_at = :start_at
                LIMIT 1
                ON CONFLICT(pgm_uid, start_at)
                DO UPDATE SET
                    asof = EXCLUDED.asof,
                    pgm_title = EXCLUDED.pgm_title,
                    interaction = EXCLUDED.interaction
                ;
            ", [
                'pgm_uid' => $pgm_uid,
                'start_at' => $start_at,
                'interaction' => $interaction
            ]);
        }

        $tz = new DateTimeZone('Asia/Tokyo');
        $currenttime = new DateTimeImmutable('now', $tz);
        $pasttime = $currenttime->modify('-7 days')->setTime(0,0,0,0);
        $pastepoch_ms = $pasttime->getTimestamp() * 1000; 
        $nextProgramUid = $pgm_uid;
        $nextProgramStart = $start_at;
        if ($randomwalk == 1) {
            $rnd = rand(1,100);
            if ($rnd <= 33) {
                $randomwalk_pred = "N";
            }
            elseif ($rnd <= 50) {
                $randomwalk_pred = "P";
            }
            else {
                $randomwalk_pred = null;
            }
            $params_ = [];
            if(is_null($randomwalk_pred)) {
                $with_ = "tvml1 AS (
                    SELECT * FROM tvmldb.tvml
                    WHERE (
                      (pgm_uid != :current_uid OR :current_uid IS NULL)
                      OR (start_at != :current_start OR :current_start IS NULL)
                    )
                    AND (pgm_title IS NOT NULL AND pgm_title != '' OR pgm_description IS NOT NULL AND pgm_description != '')
                    AND is_target_channel = 1
                    -- AND is_preinstalled = 0
                    AND start_at >= :past_start
                    ORDER BY RANDOM() DESC
                    LIMIT 1
                )";
                $params_['current_uid'] = $pgm_uid;
                $params_['current_start'] = $start_at;
                $params_['past_start'] = $pastepoch_ms;
            }
            else {
                $with_ = "tvml1 AS (
                    SELECT * FROM tvmldb.tvml
                    WHERE (
                      (pgm_uid != :current_uid OR :current_uid IS NULL)
                      OR (start_at != :current_start OR :current_start IS NULL)
                    )
                    AND (pgm_title IS NOT NULL AND pgm_title != '' OR pgm_description IS NOT NULL AND pgm_description != '')
                    AND is_target_channel = 1
                    -- AND is_preinstalled = 0
                    AND start_at >= :past_start
                    AND pred_label = :randomwalk_pred
                    AND (interaction IS NULL OR interaction = '-')
                    ORDER BY RANDOM() DESC
                    LIMIT 1
                )";
                $params_['current_uid'] = $pgm_uid;
                $params_['current_start'] = $start_at;
                $params_['past_start'] = $pastepoch_ms;
                $params_['randomwalk_pred'] = $randomwalk_pred;
            }
            $nextProgram = DB::selectOne("
                WITH
                {$with_}
                SELECT *
                FROM tvml1
            ", $params_);
            if ($nextProgram) {
                $nextProgramUid = $nextProgram->pgm_uid;
                $nextProgramStart = $nextProgram->start_at;
            }
            else {
                $nextProgramUid = null;
                $nextProgramStart = null;
            }
        }

        // ステータスに応じた日本語メッセージをトースト用に作成
        $interactionLabels = ['P' => '「Positive」に仕分けました', 'N' => '「Negative」に仕分けました', '-' => '「Neutral」に仕分けました', '' => 'スキップしました'];
        if($id==='randomwalk') {
            $msg = '';
        }
        else {
            $msg = $interactionLabels[$interaction] ?? '保存しました';
        }

        if ($nextProgramUid) {
            // 次の未処理番組がある場合、その画面へ遷移（やりなおす用に現在のUIDを渡す）
            $redirect = redirect()
                ->route('programs.show', array_merge([
                    'id' => $nextProgramUid . '.' . $nextProgramStart,
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