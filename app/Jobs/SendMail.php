<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\Reserved;
 use Illuminate\Support\Facades\Auth;
 use App\Models\User;
 use App\Models\Event;
 use Illuminate\Support\Facades\Log;


class SendMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

     public $event;
     public $user;
    public $email;

    public function __construct($event,$user,$email)
    {
        //
        $this->event=$event;
        $this->user=$user;
        $this->email=$email;
    }
    
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
//        Mail::to('a@b.c')->send(new Reserved('$this->event','testname'));
        //Mail::to(User::findOrFail(Auth::id())->email)->send(new Reserved($this->event,'testname'));
        Mail::to($this->email)->send(new Reserved($this->event,$this->user));
}
}
