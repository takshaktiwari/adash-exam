<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_paper_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('paper_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_option_id')->nullable()
                ->default(null)
                ->constrained('question_options')
                ->onDelete('cascade');
            $table->foreignId('correct_option_id')->nullable()
                ->default(null)
                ->constrained('question_options')
                ->onDelete('cascade');
            $table->string('status')->nullable()->default(null);
            $table->text('user_answer_text')->nullable()->default(null);
            $table->text('correct_answer_text')->nullable()->default(null);
            $table->float('marks', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_questions');
    }
};
