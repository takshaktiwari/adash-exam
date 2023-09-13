<?php

namespace Takshak\Exam\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPaper extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'start_at' => 'datetime',
        'submit_at' => 'datetime',
        'end_at' => 'datetime'
    ];

    /**
     * Get the user that owns the UserPaper
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the paper that owns the UserPaper
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    /**
     * Get all of the questions for the UserPaper
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions(): HasMany
    {
        return $this->hasMany(UserQuestion::class);
    }
}
