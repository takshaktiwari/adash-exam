<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_paper_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_paper_id')->constrained()->onDelete('cascade');
            $table->foreignId('paper_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('questions')->default(0);
            $table->integer('answered')->default(0);
            $table->integer('correct')->default(0);
            $table->integer('incorrect')->default(0);
            $table->float('total_marks', 10, 2)->default(0);
            $table->float('marks', 10, 2)->default(0);
            $table->float('percentage', 10, 2)->default(0);
            $table->dateTime('user_paper_at')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_paper_reports');
    }
};
