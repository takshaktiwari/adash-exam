<?php

namespace Takshak\Exam\Imports;

use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Takshak\Exam\Models\Question;
use Takshak\Exam\Models\QuestionGroup;
use Takshak\Exam\Models\QuestionOption;

class QuestionsImport implements ToModel, WithBatchInserts, WithHeadingRow, WithChunkReading, ShouldQueue
{
    /**
     * @param Collection $collection
     */
    public $row;
    public $rowsCount = 0;

    public function model(array $row)
    {
        $this->row = $row;
        $this->rowsCount++;
        if ($this->rowsCount > 250) {
            abort(403, 'Cannot upload more than 250 questions at a time');
        }
        if (
            empty($this->row['question']) ||
            empty($this->row['option_1']) ||
            empty($this->row['option_2']) ||
            empty($this->row['correct_ans']) ||
            !in_array($this->row['correct_ans'], [1, 2, 3, 4, 5])
        ) {
            return null;
        }

        if(!empty($this->row['id'])) {
            $question = Question::where('id', $this->row['id'])->first();
        }

        if(empty($question)) {
            $question = Question::where('question', $this->row['question'])->first();
        }

        $parent_id = null;
        if(isset($this->row['parent_id'])){
            $parent_id = $this->row['parent_id'];
        }
        if(isset($this->row['parent'])){
            $parent_id = $this->row['parent'];
        }

        $object = [
            'question_id'   => $parent_id,
            'question'      => $this->row['question'],
            'answer'        => $this->row['answer'],
            'marks'         => $this->row['marks'],
        ];

        if ($question) {
            $question->update($object);
        } else {
            $question = Question::create($object);
        }

        $groups = explode('|', $this->row['groups']);
        $groups = array_map(function ($item) {
            return trim($item);
        }, $groups);

        $question->questionGroups()->sync(
            QuestionGroup::whereIn('name', $groups)->pluck('id')
        );

        QuestionOption::where('question_id', $question->id)->delete();

        $this->update_option($question, 'option_1', $this->row['option_1']);
        $this->update_option($question, 'option_2', $this->row['option_2']);
        if ($this->row['option_3']) {
            $this->update_option($question, 'option_3', $this->row['option_3']);
        }
        if ($this->row['option_4']) {
            $this->update_option($question, 'option_4', $this->row['option_4']);
        }
        if ($this->row['option_5']) {
            $this->update_option($question, 'option_5', $this->row['option_5']);
        }

        return null;
    }

    public function update_option($question, $key, $opt)
    {
        $correct_ans = false;
        $exp = explode('_', $key);

        if (trim($this->row['correct_ans']) == trim($exp['1'])) {
            $correct_ans = true;
        }

        QuestionOption::create([
            'question_id'    =>    $question->id,
            'option_text'    =>    $opt,
            'correct_ans'    =>    $correct_ans,
        ]);
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
