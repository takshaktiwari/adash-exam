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
        Schema::create('papers', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->float('minus_mark_percent', 10, 2)->nullable()->default(0)->comment('minus marks in percent on all marks');
            $table->integer('total_time')->comment('in minutes');
            $table->dateTime('activate_at')->comment('date time');
            $table->dateTime('expire_at')->comment('date time');
            $table->text('instruction')->nullable();
            $table->boolean('shuffle_questions')->nullable()->default(false);
            $table->boolean('status')->nullable()->default(false);
            $table->string('security_code')->nullable()->default(null);
            $table->integer('attempts_limit')->nullable()->default(null);
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
        Schema::dropIfExists('papers');
    }
};
