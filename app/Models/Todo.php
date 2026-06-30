<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    public const STATUS_NOT_STARTED = '未対応';
    public const STATUS_IN_PROGRESS = '処理中';
    public const STATUS_DONE = '処理済み';
    public const STATUSES = [
        self::STATUS_NOT_STARTED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_DONE,
    ];

    public const PRIORITY_HIGH = '高';
    public const PRIORITY_MEDIUM = '中';
    public const PRIORITY_LOW = '低';
    public const PRIORITIES = [
        self::PRIORITY_HIGH,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_LOW,
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'content',
        'status',
        'priority',
        'sort_order',
        'deadline_at',
        'slack_notified_at',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setDeadlineAtAttribute($value)
    {
        $this->attributes['deadline_at'] = filled($value)
            ? Carbon::parse($value)->format('Y-m-d H:i:s')
            : null;
    }

    public function scopeCategorySearch($query, $categoryId)
    {
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

    public function scopeKeywordSearch($query, $keyword)
    {
        if (!empty($keyword)) {
            $query->where('content', 'like', '%' . $keyword . '%');
        }

        return $query;
    }
}
