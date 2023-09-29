<?php

namespace Takshak\Exam\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Get all of the options for the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    /**
     * The questionGroups that belong to the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function questionGroups(): BelongsToMany
    {
        return $this->belongsToMany(QuestionGroup::class);
    }

    /**
     * Get the correctOption associated with the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function correctOption(): HasOne
    {
        return $this->hasOne(QuestionOption::class)->where('correct_ans', true);
    }

    /**
     * The sections that belong to the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(PaperSection::class, 'paper_question_section');
    }

    /**
     * Get the userQuestion associated with the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userQuestion(): HasOne
    {
        return $this->hasOne(UserQuestion::class);
    }

    /**
     * The papers that belong to the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function papers(): BelongsToMany
    {
        return $this->belongsToMany(Paper::class, 'paper_question_section');
    }
}
