<?php

namespace Takshak\Exam\Imports;

use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Takshak\Exam\Models\Question;
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
        if($this->rowsCount > 200) {
            die();
        }
        $this->rowsCount++;

        $this->row = array_filter($row, fn ($i) => $i);

        if (
            empty($this->row['question']) ||
            empty($this->row['option_1']) ||
            empty($this->row['option_2']) ||
            empty($this->row['option_3']) ||
            empty($this->row['option_4']) ||
            empty($this->row['correct_ans']) ||
            !in_array($this->row['correct_ans'], [1, 2, 3, 4])
        ) {
            return false;
        } else {
            $question = Question::where('question', $this->row['question'])->first();

            $object = [
                'question'    =>    $this->row['question'],
                'answer'    =>    $this->row['answer'],
                'marks'     =>  $this->row['marks'],
            ];

            if ($question) {
                $question->update($object);
            } else {
                $question = Question::create($object);
            }

            QuestionOption::where('question_id', $question->id)->delete();

            $this->update_option($question, 'option_1', $this->row['option_1']);
            $this->update_option($question, 'option_2', $this->row['option_2']);
            $this->update_option($question, 'option_3', $this->row['option_3']);
            $this->update_option($question, 'option_4', $this->row['option_4']);
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
