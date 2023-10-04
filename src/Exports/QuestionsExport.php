<?php

namespace Takshak\Exam\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Takshak\Exam\Models\Question;

class QuestionsExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    public function query()
    {
        return Question::query()
            ->with('questionGroups')
            ->when(request('question_id'), function ($query) {
                $query->where('question_id', request('question_id'));
            })
            ->when(request('question'), function ($query) {
                $query->where('question', 'like', "%" . request('question') . "%");
            })
            ->when(request('question_group_id'), function ($query) {
                $query->whereHas('questionGroups', function ($query) {
                    $query->where('question_groups.id', request('question_group_id'));
                });
            })
            ->with('options');
    }

    public function headings(): array
    {
        return [
            'Id',
            'Parent Id',
            'Question',
            'Answer',
            'Groups',
            'Option 1',
            'Option 2',
            'Option 3',
            'Option 4',
            'Option 5',
            'Correct Ans',
            'Marks',
        ];
    }

    public function map($question): array
    {
        $arr = [
            $question->id,
            $question->question_id,
            $question->question,
            $question->answer,
            $question->questionGroups->pluck('name')->implode(' | ')
        ];

        $correctAns = 0;
        for ($i = 1; $i <= 5; $i++) {
            $option = isset($question->options[$i]) ? $question->options[$i] : null;
            $arr[] = $option?->option_text;

            if ($option && $option->correct_ans) {
                $correctAns = $i;
            }
        }

        $arr[] = $correctAns;
        $arr[] = $question->marks;

        return $arr;
    }
}
