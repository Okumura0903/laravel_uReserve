<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\Cancel;
use App\Services\MyPageService;
use Carbon\Carbon;
use App\Jobs\CancelMail;

class MyPageController extends Controller
{
    //
    public function index(){
        $user=User::findOrFail(Auth::id());
        $events=$user->events;
        $fromTodayEvents=MyPageService::reservedEvent($events,'fromToday');
        $pastEvents=MyPageService::reservedEvent($events,'past');

//        dd($events,$fromTodayEvents,$pastEvents);
        return view('mypage/index',compact('fromTodayEvents','pastEvents'));
    }
    public function show($id){
        $event=Event::findOrFail($id);
        $reservation=Reservation::where('user_id','=',Auth::id())
        ->where('event_id','=',$id)
        ->latest()//引数なしだと、created_atの一番新しいもの
        ->first();
        
        return view('mypage/show',compact('event','reservation'));

    }
    public function cancel($id){
        $reservation=Reservation::where('user_id','=',Auth::id())
        ->where('event_id','=',$id)
        ->latest()//引数なしだと、created_atの一番新しいもの
        ->first();

                //キャンセル待ちユーザーに通知メール
                $maxPeople=Event::findOrFail($id)->max_people;
                $reservedPeople=DB::table('reservations')->select(DB::raw('sum(number_of_people) as total'))
                ->groupBy('event_id')
                ->where('canceled_date','=',null)
                ->where('event_id','=',$id)
                ->first();
                $reservablePeople=$maxPeople-(int)$reservedPeople->total+$reservation->number_of_people;
                $waitingUsers=Cancel::where('event_id','=',$id)
                ->where('treated_date','=',null)
                ->get();
                foreach($waitingUsers as $waitingUser){
                    if($waitingUser->number_of_people<=$reservablePeople){
                        $name=User::findOrFail($waitingUser->user_id)->name;
                        $email=User::findOrFail($waitingUser->user_id)->email;
                        //sendmail
                        CancelMail::dispatch($id,$waitingUser->user_id,Event::findOrFail($id)->name,$name);
                    }
                }

                
        $reservation=Reservation::where('user_id','=',Auth::id())
        ->where('event_id','=',$id)
        ->latest()//引数なしだと、created_atの一番新しいもの
        ->first();

        $reservation->canceled_date=Carbon::now()->format('Y-m-d H:i:s');
        $reservation->save();

        session()->flash('status','キャンセルできました');
        return to_route('dashboard');
    }
}
