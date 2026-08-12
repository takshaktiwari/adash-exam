<?php

namespace Takshak\Exam\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPaperReport extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'user_paper_at' => 'datetime',
    ];

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subMonths(6));
    }

    /**
     * Get the user that owns the UserPaperReport
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the paper that owns the UserPaperReport
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    /**
     * Get the userPaper that owns the UserPaperReport
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userPaper(): BelongsTo
    {
        return $this->belongsTo(UserPaper::class);
    }
}
