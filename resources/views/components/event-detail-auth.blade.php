<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            イベント詳細
        </h2>
    </x-slot>

    <div class="pt-4 pb-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="max-w-2xl py-4 mx-auto">
                    <x-validation-errors class="mb-4" />

                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                        @if(!is_null($isWaiting))
                            <div class="bg-yellow-200 py-1 text-center mb-1 flex justify-center items-center">{{$isWaiting->number_of_people}}人でキャンセル待ち中です。
                                <form method="post" action="{{ route('events.deleteCancel',['id'=>$event->id]) }}">
                                @csrf
                                    <x-button class="ml-4">
                                        取り消す
                                    </x-button>
                                </form>
                            </div>
                        @endif
                        <form method="post" action="{{ route('events.reserve',['id'=>$event->id]) }}">
                        @csrf
                            <div>
                                <x-label for="event_name" value="イベント名" />
                                {{$event->name}}
                            </div>
                            <div class="mt-4">
                                <x-label for="information" value="イベント詳細" />
                                {!! nl2br(e($event->information)) !!}
                            </div>

                            <div class="md:flex justify-between">
                                <div class="mt-4">
                                    <x-label for="event_date" value="イベント日付" />
                                    {{$event->eventDate}}
                                </div>

                                <div class="mt-4">
                                    <x-label for="start_time" value="開始時間" />
                                    {{$event->startTime}}
                                </div>

                                <div class="mt-4">
                                    <x-label for="end_time" value="終了時間" />
                                    {{$event->endTime}}
                                </div>
                            </div>
                            <div class="md:flex justify-between items-end">
                                <div class="mt-4">
                                        <x-label for="max_people" value="定員数" />
                                        {{$event->max_people}}
                                </div>
                                @if($isReserved===null)
                                <div class="mt-4">
                                        <x-label for="reserved_people" value="予約人数" />
                                        <select name="reserved_people">
                                        @for($i=1;$i<=$event->max_people;$i++)
                                            @if($reservablePeople>=$i)
                                                <option value="{{$i}}">{{$i}}</option>
                                            @else
                                                <option value="{{$i}}">{{$i}}（キャンセル待ち）</option>
                                            @endif
                                        @endfor
                                        </select>
                                </div>
                                @endif
                                @if($isReserved===null)
                                    <input type="hidden" name="id" value="{{$event->id}}">
                                    <input type="hidden" name="reservablePeople" value="{{$reservablePeople}}">
                                        <x-button class="ml-4">
                                            予約する
                                        </x-button>
                                @else
                                    <span class="text-xs">このイベントは既に予約済みです。</span>
                                @endif
                            </div>
                        </form>
                    </div>
              </div>
        </div>
    </div>
</x-app-layout>
