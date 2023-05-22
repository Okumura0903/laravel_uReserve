<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventService
{
    public static function checkEventDuplication($eventDate,$startTime,$endTime){
        //重複チェック
        return DB::table('events')
            ->whereDate('start_date',$eventDate)
            ->whereTime('end_date','>',$startTime)
            ->whereTime('start_date','<',$endTime)
            ->exists();
    }
    public static function joinDateAndTime($date,$time){
        $join=$date." ".$time;
        return Carbon::createFromFormat('Y-m-d H:i',$join);
    }
    public static function countEventDuplication($eventDate,$startTime,$endTime){
        //カウント
        return DB::table('events')
            ->whereDate('start_date',$eventDate)
            ->whereTime('end_date','>',$startTime)
            ->whereTime('start_date','<',$endTime)
            ->count();
    }

    public static function getWeekEvents($startDate,$endDate){
        //予約テーブルからイベントごとの合計人数でテーブルを作る
        $reservedPeople=DB::table('reservations')->select('event_id',DB::raw('sum(number_of_people) as number_of_people'))
        ->whereNull('canceled_date')
        ->groupBy('event_id');//DB::rawで生のSQLを書ける

        //内部結合・・合計人数がない場合データが表示されない：joinSub
        //外部結合・・合計人数がない場合、nullとして表示される：leftJoinSub
        // +"id": 1
        // +"name": "青山 美加子"
        // +"information": "を解とけいやの店にはなんと立ちどま向むかしだのそとを思いだいもいいのがつまりもう咽喉のどい近眼鏡きんがたずねました。（この頁ページ一つずつ集あつまみ、掌てのぞきこうね」と叫さけびましたら、それでも着ついているかとおりたいてしました。「ありません。りんごを見ました。それにさっきかんしゅうに見えないかけて、この辺へんじまい ▶"
        // +"max_people": 17
        // +"start_date": "2023-05-01 09:01:37"
        // +"end_date": "2023-05-01 10:01:37"
        // +"is_visible": 1
        // +"created_at": "2023-05-21 20:02:15"
        // +"updated_at": "2023-05-21 20:02:15"
        // +"event_id": 1
        // +"number_of_people": "8"

        return DB::table('events')
            ->leftJoinSub($reservedPeople,'reservedPeople',function($join){//元のテーブルにnumber_of_peopleを追加する
                $join->on('events.id','=','reservedPeople.event_id');//イベントテーブルのidと↑の予約人数テーブルのidで結合
            })
            ->whereBetween('start_date',[$startDate,$endDate])
            ->orderBy('start_date','asc')
            ->get();
    }
}