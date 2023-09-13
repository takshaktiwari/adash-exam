<?php

namespace Takshak\Exam\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaperSection extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Get the paper that owns the PaperSection
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    /**
     * The questions that belong to the PaperSection
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'paper_question_section');
    }
}
