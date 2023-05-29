<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Cancel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendMail;

class ReservationController extends Controller
{
    //
    public function dashboard(){
        return view('dashboard');
    }

    public function detail($id){
        $event=Event::findOrFail($id);

        $reservedPeople=DB::table('reservations')->select('event_id',DB::raw('sum(number_of_people) as number_of_people'))
        ->whereNull('canceled_date')
        ->groupBy('event_id')
        ->having('event_id',$event->id)//groupByの後で条件をつける
        ->first();

        if(!is_null($reservedPeople)){
            $reservablePeople=$event->max_people-$reservedPeople->number_of_people;
        }
        else{
            $reservablePeople=$event->max_people;
        }

        $isReserved=Reservation::where('user_id','=',Auth::id())
        ->where('event_id','=',$id)
        ->where('canceled_date','=',null)
        ->latest()
        ->first();

        $isWaiting=Cancel::where('user_id','=',Auth::id())
        ->where('event_id','=',$id)
        ->where('treated_date','=',null)
        ->latest()
        ->first();
        
        return view('event-detail',compact('event','reservablePeople','isReserved','isWaiting'));
    }

    public function reserve(Request $request){
        $event=Event::findOrFail($request->id);

        $reservedPeople=DB::table('reservations')->select('event_id',DB::raw('sum(number_of_people) as number_of_people'))
        ->whereNull('canceled_date')
        ->groupBy('event_id')
        ->having('event_id',$event->id)//groupByの後で条件をつける
        ->first();

        if(!is_null($reservedPeople)){
            $reservablePeople=$event->max_people-$reservedPeople->number_of_people;
        }
        else{
            $reservablePeople=$event->max_people;
        }
        
        if($request->reservablePeople!=$reservablePeople){
            session()->flash('status','予約可能人数が変更されました。もう一度お試しください。');
            return to_route('events.detail',['id'=>$request->id]);
        }
        //キャンセル待ちがあれば取り消す
        $cancel=Cancel::where('user_id','=',Auth::id())
        ->where('event_id','=',$request->id)
        ->latest()//引数なしだと、created_atの一番新しいもの
        ->first();
        if(!is_null($cancel)){
            $cancel->treated_date=Carbon::now()->format('Y-m-d H:i:s');
            $cancel->save();
        }
        
        if(is_null($reservedPeople) || $event->max_people >= $reservedPeople->number_of_people + $request->reserved_people){
            Reservation::create([
                'user_id'=>Auth::id(),
                'event_id'=>$request->id,
                'number_of_people'=>$request->reserved_people,
            ]);
            SendMail::dispatch($event->name,User::findOrFail(Auth::id())->name,User::findOrFail(Auth::id())->email);

            session()->flash('status','イベント'.$event->name.'を予約しました。');
            return to_route('dashboard');
        }
        else{
        //キャンセル待ち登録
            Cancel::create([
                'user_id'=>Auth::id(),
                'event_id'=>$request->id,
                'number_of_people'=>$request->reserved_people,
            ]);

            session()->flash('status','イベント'.$event->name.'をキャンセル待ちしました。');
            return to_route('dashboard');
        }


        return view('dashboard');
    }

    public function deleteCancel($id){
        $cancel=Cancel::where('user_id','=',Auth::id())
        ->where('event_id','=',$id)
        ->latest()//引数なしだと、created_atの一番新しいもの
        ->first();

        $cancel->treated_date=Carbon::now()->format('Y-m-d H:i:s');
        $cancel->save();

        session()->flash('status','このイベントのキャンセル待ちを取り消しました。');
        return to_route('events.detail',['id'=>$id]);
    }
}
