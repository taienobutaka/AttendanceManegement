<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    private const SYSTEM_CONTEXT = <<<'TEXT'
あなたは勤怠管理システム「Atte」のサポートアシスタントです。以下のデータベースと画面仕様に基づき、正確に答えてください。

【データベース構造（事実に基づく説明に使用すること）】
- users: id, name, email, password, created_at, updated_at。会員登録で1ユーザー1レコード。
- attendances: id, user_id, date, start_time, end_time, created_at, updated_at。1日1ユーザーあたり1レコード。勤務開始で当日のレコードが作成され start_time が入る。勤務終了で end_time が更新される。
- rests: id, attendance_id, start_time, end_time, created_at, updated_at。1つの勤怠(attendance)に複数レコード可。休憩開始でその日の attendance に紐づく rest が作成され start_time が入る。休憩終了で end_time が更新される。
- 勤務時間: その日の attendance の end_time − start_time で計算。
- 休憩時間: その attendance に紐づく rests の各 (end_time − start_time) の合計。複数回休憩がある場合は合算して表示。

【画面と表示内容】
- 勤怠入力画面（/）: 勤務開始・勤務終了・休憩開始・休憩終了の4ボタン。勤務開始を押すと attendances に当日レコードが作成され、休憩・勤務終了が押せるようになる。休憩開始で rests にレコード追加、休憩終了でその rest の end_time を記録。勤務終了でその日の attendance の end_time を記録し、その日の打刻は終了。
- 会員一覧（/members）: 表示列は「名前」「最新勤務日」「勤務一覧」。最新勤務日はそのユーザーの attendances の date のうち最も新しい日付。勤務一覧列の「詳細」リンクでその会員の勤務一覧（/users/{id}）へ遷移。5件ごとページネーション。
- 勤務一覧（自分の）（/user）: 表示列は名前・年月日・勤務開始・勤務終了・休憩時間・勤務時間。自分の attendances を日付の新しい順に5件ずつ表示。
- 勤務一覧（特定会員）（/users/{id}）: 表示列は勤務日・勤務開始・勤務終了・休憩時間・勤務時間。その会員の attendances を日付の新しい順に5件ずつ表示。
- 日付一覧（/attendance）: 表示列は名前・勤務開始・勤務終了・休憩時間・勤務時間。指定した日付の attendances を一覧（user 名付き）。前日・翌日ボタンで日付を切り替え。5件ごとページネーション。
- ログイン・会員登録: メール認証付き。登録後メールのリンクからログイン可能。

【回答のルール】
- 「【現在のシステムデータ】」に登録会員・勤務登録者名・勤務詳細（勤務開始・勤務終了・休憩時間・勤務時間）が記載されている場合は、その内容をそのまま用いて質問に答える。例: 「会員登録者を教えて」には登録会員の名前を列挙する。「昨日の勤務者を教えて」には昨日の勤務登録者を答える。「昨日の勤務者の勤務時間を教えて」「本日の勤務時間を教えて」など勤務時間の詳細を聞かれた場合も、【現在のシステムデータ】の「勤務詳細」に記載された名前・勤務開始・勤務終了・休憩時間・勤務時間をそのまま用いて明確に回答する。勤務時間について「お答えできません」とは言わない。
- 上記のテーブル名・カラム・画面仕様に基づき明確に回答する。推測はせず、ここに書かれた範囲で答える。
- 2〜4文程度で簡潔に。操作手順は箇条書きでも可。
- システムの範囲外の質問には「このシステムでは〜についてお答えできます」と伝える。
TEXT;

    public function __invoke(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) {
            return response()->json(['reply' => 'チャットボットの設定が完了していません。OPENAI_API_KEYを.envに設定してください。'], 503);
        }

        $systemContent = self::SYSTEM_CONTEXT . "\n\n" . $this->getSystemDataContext();

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemContent],
                    ['role' => 'user', 'content' => $request->input('message')],
                ],
                'max_tokens' => 500,
            ]);

            if (!$response->successful()) {
                Log::warning('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['reply' => '申し訳ありません。一時的に応答できません。しばらくしてからお試しください。'], 502);
            }

            $data = $response->json();
            $reply = $data['choices'][0]['message']['content'] ?? '';

            return response()->json(['reply' => trim($reply)]);
        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage());
            return response()->json(['reply' => '申し訳ありません。エラーが発生しました。しばらくしてからお試しください。'], 500);
        }
    }

    /**
     * データベースから取得した現在の会員・勤務情報をプロンプト用に整形する。
     */
    private function getSystemDataContext(): string
    {
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $weekStart = Carbon::now()->startOfWeek()->toDateString();
        $weekEnd = Carbon::now()->endOfWeek()->toDateString();
        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $monthEnd = Carbon::now()->endOfMonth()->toDateString();

        $memberNames = User::orderBy('id')->pluck('name')->toArray();
        $membersText = count($memberNames) > 0
            ? implode('、', $memberNames) . '（全' . count($memberNames) . '名）'
            : '（0名）';

        $todayAttendances = Attendance::whereDate('date', $today)
            ->with(['user:id,name', 'rests'])
            ->get();
        $todayNames = $todayAttendances->map(fn ($a) => $a->user->name ?? '')->unique()->filter()->values()->toArray();
        $todayText = count($todayNames) > 0 ? implode('、', $todayNames) : 'なし';
        $todayDetailText = $this->formatAttendanceDetails($todayAttendances);

        $yesterdayAttendances = Attendance::whereDate('date', $yesterday)
            ->with(['user:id,name', 'rests'])
            ->get();
        $yesterdayNames = $yesterdayAttendances->map(fn ($a) => $a->user->name ?? '')->unique()->filter()->values()->toArray();
        $yesterdayText = count($yesterdayNames) > 0 ? implode('、', $yesterdayNames) : 'なし';
        $yesterdayDetailText = $this->formatAttendanceDetails($yesterdayAttendances);

        $weekAttendances = Attendance::whereBetween('date', [$weekStart, $weekEnd])
            ->with('user:id,name')
            ->get();
        $weekNames = $weekAttendances->map(fn ($a) => $a->user->name ?? '')->unique()->filter()->values()->toArray();
        $weekText = count($weekNames) > 0 ? implode('、', $weekNames) : 'なし';

        $monthAttendances = Attendance::whereBetween('date', [$monthStart, $monthEnd])
            ->with('user:id,name')
            ->get();
        $monthNames = $monthAttendances->map(fn ($a) => $a->user->name ?? '')->unique()->filter()->values()->toArray();
        $monthText = count($monthNames) > 0 ? implode('、', $monthNames) : 'なし';

        return "【現在のシステムデータ】\n"
            . "登録会員: {$membersText}\n"
            . "本日（{$today}）の勤務登録者: {$todayText}\n"
            . "本日（{$today}）の勤務詳細:\n{$todayDetailText}\n"
            . "昨日（{$yesterday}）の勤務登録者: {$yesterdayText}\n"
            . "昨日（{$yesterday}）の勤務詳細:\n{$yesterdayDetailText}\n"
            . "今週（{$weekStart}〜{$weekEnd}）の勤務登録者: {$weekText}\n"
            . "今月（{$monthStart}〜{$monthEnd}）の勤務登録者: {$monthText}";
    }

    /**
     * 勤怠一覧を「名前・勤務開始・勤務終了・休憩時間・勤務時間」の行形式に整形する。
     *
     * @param  \Illuminate\Support\Collection<int, Attendance>  $attendances
     */
    private function formatAttendanceDetails($attendances): string
    {
        if ($attendances->isEmpty()) {
            return '（なし）';
        }
        $lines = [];
        foreach ($attendances as $a) {
            $name = $a->user->name ?? '（不明）';
            $start = $a->start_time ? substr($a->start_time, 0, 8) : '-';
            $end = $a->end_time ? substr($a->end_time, 0, 8) : '-';
            $rest = $a->total_rest_duration ?? '00:00:00';
            $work = $a->duration ?? '-';
            $lines[] = "  {$name}: 勤務開始 {$start}, 勤務終了 {$end}, 休憩時間 {$rest}, 勤務時間 {$work}";
        }
        return implode("\n", $lines);
    }
}
