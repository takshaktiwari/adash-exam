<?php

namespace Takshak\Exam\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuestion extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Get the question that owns the UserQuestion
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function scopeAnswered(Builder $query)
    {
        return $query->where('status', 'answered');
    }

    public function scopeMarked(Builder $query)
    {
        return $query->where('status', 'marked');
    }
}
