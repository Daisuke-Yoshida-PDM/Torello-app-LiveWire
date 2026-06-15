<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];


    protected $fillable = ['title', 'description', 'user_id', 'status_id', 'position', 'start_date', 'due_date'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class);
    }

    public function formatDate(?Carbon $date): ?string 
    {
        if(!$date) return null;
        $weekday = ['日', '月', '火', '水', '木', '金', '土'];

        $isThisYear = $date->year === Carbon::today()->year;
        $yearFormat = $isThisYear ? 'n/j' : 'Y/n/j';
        return $date->format($yearFormat) . '(' . $weekday[$date->dayOfWeek] . ')';
    }

    public function alertDate(): ?string 
    {
        if(!$this->due_date) return null;

        $setClass = null;

        if($this->due_date->isTomorrow() || $this->due_date->isToday()) {
            $setClass = 'isOneDayAgo';
        }

        if($this->due_date->lt(Carbon::today())) {
            $setClass = 'isOverDue';
        }
        return $setClass;
    }
}
