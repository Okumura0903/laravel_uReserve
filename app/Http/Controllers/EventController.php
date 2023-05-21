<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\EventService;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $today=Carbon::today();

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

        $events=DB::table('events')
        ->leftJoinSub($reservedPeople,'reservedPeople',function($join){//元のテーブルにnumber_of_peopleを追加する
            $join->on('events.id','=','reservedPeople.event_id');//イベントテーブルのidと↑の予約人数テーブルのidで結合
        })
         ->where('start_date','>=',$today)
         ->orderBy('start_date','asc')
         ->paginate(10);

        return view('manager.events.index',compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('manager.events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        $check=EventService::checkEventDuplication($request['event_date'],$request['start_time'],$request['end_time']);
        if($check){
            session()->flash('status','この時間帯は既に他の予約が存在します。');
            return view('manager.events.create');
        }

        $startDate=EventService::joinDateAndTime($request['event_date'],$request['start_time']);
        $endDate=EventService::joinDateAndTime($request['event_date'],$request['end_time']);

        Event::create([
            'name'=>$request['event_name'],
            'information'=>$request['information'],
            'start_date'=>$startDate,
            'end_date'=>$endDate,
            'max_people'=>$request['max_people'],
            'is_visible'=>$request['is_visible'],
        ]);

        session()->flash('status','登録OKです');
        return to_route('events.index');

    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        //
        $event=Event::findOrFail($event->id);

        $users=$event->users;
//        dd($users,$event);
        $reservations=[];
        foreach($users as $user){
            $reservedInfo=[
                'name'=>$user->name,
                'number_of_people'=>$user->pivot->number_of_people,
                'canceled_date'=>$user->pivot->canceled_date
            ];
            array_push($reservations,$reservedInfo);
        }
//        dd($reservations);
        $eventDate=$event->eventDate;
        $startTime=$event->startTime;
        $endTime=$event->endTime;
        return view('manager.events.show',compact('event','users','reservations','eventDate','startTime','endTime'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        //
        $event=Event::findOrFail($event->id);

        $today=Carbon::today()->format('Y年m月d日');
        if($event->eventDate < $today){
            return abort(404);
        }

        $eventDate=$event->editEventDate;
        $startTime=$event->startTime;
        $endTime=$event->endTime;
        return view('manager.events.edit',compact('event','eventDate','startTime','endTime'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        //
        $check=EventService::countEventDuplication($request['event_date'],$request['start_time'],$request['end_time']);
        if($check > 1){
            $event=Event::findOrFail($event->id);
            $eventDate=$event->editEventDate;
            $startTime=$event->startTime;
            $endTime=$event->endTime;   
            session()->flash('status','この時間帯は既に他の予約が存在します。');
            return view('manager.events.edit',compact('event','eventDate','startTime','endTime'));
        }

        $startDate=EventService::joinDateAndTime($request['event_date'],$request['start_time']);
        $endDate=EventService::joinDateAndTime($request['event_date'],$request['end_time']);

        $event=Event::findOrFail($event->id);
        $event->name=$request['event_name'];
        $event->information=$request['information'];
        $event->start_date=$startDate;
        $event->end_date=$endDate;
        $event->max_people=$request['max_people'];
        $event->is_visible=$request['is_visible'];
        $event->save();

        session()->flash('status','登録OKです');
        return to_route('events.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        //
    }
    public function past()
    {
        //
        $today=Carbon::today();

        //予約テーブルからイベントごとの合計人数でテーブルを作る
        $reservedPeople=DB::table('reservations')->select('event_id',DB::raw('sum(number_of_people) as number_of_people'))
        ->whereNull('canceled_date')
        ->groupBy('event_id');//DB::rawで生のSQLを書ける

        $events=DB::table('events')
        ->leftJoinSub($reservedPeople,'reservedPeople',function($join){//元のテーブルにnumber_of_peopleを追加する
            $join->on('events.id','=','reservedPeople.event_id');//イベントテーブルのidと↑の予約人数テーブルのidで結合
        })
        ->whereDate('start_date','<',$today)
        ->orderBy('start_date','desc')
        ->paginate(10);
        return view('manager.events.past',compact('events'));
    }
}
