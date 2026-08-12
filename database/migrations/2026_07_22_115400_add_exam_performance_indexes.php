<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Add performance indexes for the exam module.
     *
     * Without these indexes:
     * - user_questions.user_paper_id: full table scan on every question navigation
     * - user_questions composite (user_paper_id, question_id): used by updateOrCreate in questionSave
     * - papers.status: scanned on exam listing page
     */
    public function up(): void
    {
        Schema::table('user_questions', function (Blueprint $table) {
            // Check and add index on user_paper_id if not already present
            // (FK constraints create an index but only on the single column)
            if (!$this->indexExists('user_questions', 'user_questions_user_paper_question_idx')) {
                $table->index(
                    ['user_paper_id', 'question_id'],
                    'user_questions_user_paper_question_idx'
                );
            }

            if (!$this->indexExists('user_questions', 'user_questions_status_idx')) {
                $table->index('status', 'user_questions_status_idx');
            }
        });

        Schema::table('papers', function (Blueprint $table) {
            if (!$this->indexExists('papers', 'papers_status_idx')) {
                $table->index('status', 'papers_status_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_questions', function (Blueprint $table) {
            if ($this->indexExists('user_questions', 'user_questions_user_paper_question_idx')) {
                $table->dropIndex('user_questions_user_paper_question_idx');
            }
            if ($this->indexExists('user_questions', 'user_questions_status_idx')) {
                $table->dropIndex('user_questions_status_idx');
            }
        });

        Schema::table('papers', function (Blueprint $table) {
            if ($this->indexExists('papers', 'papers_status_idx')) {
                $table->dropIndex('papers_status_idx');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );
        return !empty($indexes);
    }
};
