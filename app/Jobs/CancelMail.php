<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\Cancel;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CancelMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $email;
    public $eventId;
    public $userId;
    public $eventName;
    public $userName;

    public function __construct($eventId,$userId,$eventName,$userName)
    {
        //
    //        $this->event=$event;
        $this->eventId=$eventId;
        $this->userId=$userId;
        $this->eventName=$eventName;
        $this->userName=$userName;
        $this->email=User::findOrFail($userId)->email;
        // $this->userName=User::findOrFail($userId)->name;
        // $this->eventName=Event::findOrFail($eventId)->name;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
//        Log::debug("debug ログ!".$this->eventId);
        Mail::to($this->email)->send(new Cancel($this->userId,$this->eventId,$this->userName,$this->eventName));
    }
}
