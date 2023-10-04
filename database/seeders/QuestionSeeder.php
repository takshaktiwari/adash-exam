<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Takshak\Exam\Models\Question;
use Takshak\Exam\Models\QuestionGroup;
use Takshak\Exam\Models\QuestionOption;
use Takshak\Imager\Facades\Picsum;

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
            $question = $this->createQuestion();

            if (($i % 4) == 0) {
                for ($j = 0; $j < rand(2, 10); $j++) {
                    $this->createQuestion($question);
                }
            }
        }
    }

    public function createQuestion($parentQuestion = null)
    {
        $arr = [0, 1, 0, 0, 0, 0, 1];
        shuffle($arr);

        $question = new Question();
        $question->question = fake()->realText(rand(20, 250), 2);
        $question->answer = fake()->realText(rand(20, 250), 2);
        $question->marks = rand(1, 2);
        $question->question_id = $parentQuestion?->id;

        if (end($arr)) {
            $question->image = str()->of(microtime())->slug('-')
                ->prepend('questions/')
                ->append('.jpg');

            Picsum::dimensions(500, 200)
                ->save(Storage::disk('public')->path($question->image));
        }
        $question->save();

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

        return $question;
    }
}
