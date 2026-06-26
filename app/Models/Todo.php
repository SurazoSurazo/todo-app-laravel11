<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'content',
        'sort_order',
        'deadline_at',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
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
