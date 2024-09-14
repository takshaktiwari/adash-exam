<?php

namespace Takshak\Exam\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paper extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'activate_at' => 'datetime',
        'expire_at' => 'datetime',
    ];

    /**
     * Get all of the sections for the Paper
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sections(): HasMany
    {
        return $this->hasMany(PaperSection::class);
    }

    /**
     * The questions that belong to the Paper
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'paper_question_section');
    }

    /**
     * Get all of the userPapers for the Paper
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userPapers(): HasMany
    {
        return $this->hasMany(UserPaper::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }

    public function scopeNotExpired(Builder $query)
    {
        return $query->where('expire_at', '>', now())
            ->where('activate_at', '<', now());
    }

    public function isActive()
    {
        if ($this->status && $this->expire_at > now() && $this->activate_at < now()) {
            return true;
        }
    }

    public function status()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function notExpired()
    {
        if ($this->expire_at > now() && $this->activate_at < now()) {
            return true;
        }
    }

    public function hasAttempts()
    {
        if(!$this->attempts_limit){
            return true;
        }

        if(!$this->user_papers_count)
        {
            $this->loadCount(['userPapers' => function ($query) {
                $query->where('user_id', auth()->id());
            }]);
        }

        if($this->attempts_limit > $this->user_papers_count){
            return true;
        }

        return false;
    }
}
