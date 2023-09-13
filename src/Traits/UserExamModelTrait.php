<?php

namespace Takshak\Exam\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Takshak\Exam\Models\Paper;

trait UserExamModelTrait
{
    /**
     * The papers that belong to the UserExamModelTrait
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function papers(): BelongsToMany
    {
        return $this->belongsToMany(Paper::class, 'user_papers')->withTimestamps();
    }
}
