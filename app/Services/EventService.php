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

}