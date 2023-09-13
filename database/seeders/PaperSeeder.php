<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Takshak\Exam\Models\Paper;
use Takshak\Exam\Models\PaperSection;
use Takshak\Exam\Models\Question;

class PaperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 10; $i++) {
            $paper = Paper::create([
                'title'     =>  fake()->realText(rand(20, 50), 2),
                'total_time'     =>  rand(1, 9) * 10,
                'activate_at'     =>  now(),
                'expire_at'     =>  now()->addMonth(),
                'minus_mark_percent'     => ($i % 3 == 0) ? rand(2, 5) * 5 : 0,
                'instruction'     =>  fake()->realText(rand(500, 1500), 2),
                'status'     =>  rand(0, 1),
            ]);

            if ($i % 3 == 0) {
                for ($j = 0; $j < rand(3, 6); $j++) {
                    $section = PaperSection::create([
                        'paper_id' => $paper->id,
                        'name'  =>  fake()->sentence()
                    ]);

                    $questions = [];
                    foreach (Question::inRandomOrder()->limit(rand(20, 50))->pluck('id') as $id) {
                        $questions[$id] = ['paper_id'  =>  $paper->id];
                    }

                    $section->questions()->sync($questions);
                }
            } else {
                $paper->questions()->sync(Question::inRandomOrder()->limit(rand(2, 5) * 25)->pluck('id'));
            }
        }
    }
}
