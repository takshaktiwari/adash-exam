<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Takshak\Exam\Models\Question;
use Takshak\Exam\Models\QuestionGroup;
use Takshak\Exam\Models\QuestionOption;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 500; $i++) {
            $question = Question::create([
                'question'        =>    fake()->realText(rand(20, 250), 2),
                'answer'        =>    fake()->realText(rand(20, 250), 2),
                'marks'         =>  rand(1, 2),
            ]);

            for ($j = 0; $j < rand(2, 5); $j++) {
                QuestionOption::create([
                    'question_id'    =>    $question->id,
                    'option_text'    =>    fake()->realText(rand(20, 200), 2),
                    'correct_ans'    => false
                ]);
            }

            $correctOption = QuestionOption::where('question_id', $question->id)->inRandomOrder()->first();
            $correctOption->correct_ans = true;
            $correctOption->save();

            $groups = QuestionGroup::inRandomOrder()->limit(rand(1, 5))->pluck('id');
            $question->questionGroups()->sync($groups);
        }
    }
}
