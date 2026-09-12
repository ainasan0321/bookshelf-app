<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ReadingPlanStatus;


class ReadingPlan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'book_id',
        'target_date',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'date',
        'status' => ReadingPlanStatus::class,
    ];

    public function user(): belongsTo
    {
        return $this->BelongsTo(User::class);
    }

    public function book():  belongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
