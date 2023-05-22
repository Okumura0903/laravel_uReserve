<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use App\Models\User;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'information',
        'max_people',
        'start_date',
        'end_date',
        'is_visible'
    ];

    //アクセサ
    protected function eventDate(): Attribute{
        return Attribute::make(
            get:fn()=>Carbon::parse($this->start_date)->format('Y年m月d日')//アロー関数
        );
    }
    protected function startTime(): Attribute{
        return Attribute::make(
            get:fn()=>Carbon::parse($this->start_date)->format('H時i分')//アロー関数
        );
    }
    protected function endTime(): Attribute{
        return Attribute::make(
            get:fn()=>Carbon::parse($this->end_date)->format('H時i分')//アロー関数
        );
    }
    protected function editEventDate(): Attribute{
        return Attribute::make(
            get:fn()=>Carbon::parse($this->start_date)->format('Y-m-d')//アロー関数
        );
    }

    public function users(){
        //多対多のリレーション
        //中間テーブルに属性を追加した場合はwithPivotで書いておく(デフォルトではuser_idとevent_idのみ)
        return $this->belongsToMany(User::class,'reservations')->withPivot('id','number_of_people','canceled_date');
    }
}
