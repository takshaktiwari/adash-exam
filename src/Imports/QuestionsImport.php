<?php

namespace Takshak\Exam\Imports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Takshak\Exam\Models\Question;
use Takshak\Exam\Models\QuestionGroup;
use Takshak\Exam\Models\QuestionOption;
use Takshak\Exam\Notifications\QuestionsImportResult;
use Throwable;

class QuestionsImport implements ToModel, WithBatchInserts, WithHeadingRow, WithChunkReading, WithColumnLimit, WithEvents, ShouldQueue
{
    /**
     * Row-skip reasons, keyed by the heading-row field they check.
     */
    public const REASONS = [
        'question'    => 'Missing question text',
        'option_1'    => 'Missing Option 1',
        'option_2'    => 'Missing Option 2',
        'correct_ans' => "Missing or invalid 'Correct Ans' column (must be 1-5)",
    ];

    public $row;
    public $rowsCount = 0;
    public $uidQuestions = [];

    public string $importId;

    public function __construct(public ?int $userId = null, public string $fileName = '')
    {
        $this->importId = (string) Str::uuid();
    }

    public function model(array $row)
    {
        $this->row = $row;
        $this->row = array_map('trim', $this->row);
        $this->rowsCount++;

        if ($this->rowsCount > 250) {
            abort(403, 'Cannot upload more than 250 questions at a time');
        }

        $reason = $this->validationFailureReason($this->row);
        if ($reason) {
            $this->recordSkip($reason);

            return null;
        }

        if (empty($question)) {
            $question = Question::where('question', $this->row['question'])->first();
        }

        $parent_id = null;
        if (!empty($this->row['uid']) && empty($this->row['context'])) {
            $parent_id = $this->uidQuestions[$this->row['uid']];
        }

        $object = [
            'question_id'   => $parent_id,
            'question'      => $this->row['question'],
            'context'       => $this->row['context'] ?? '',
            'answer'        => $this->row['answer'],
            'marks'         => $this->row['marks'],
        ];

        if ($question) {
            $question->update($object);
        } else {
            $question = Question::create($object);
        }

        if (!empty($this->row['uid']) && !empty($this->row['context'])) {
            $this->uidQuestions[$this->row['uid']] = $question->id;
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

        $this->recordImport();

        return null;
    }

    protected function validationFailureReason(array $row): ?string
    {
        if (empty($row['question'])) {
            return 'question';
        }
        if (empty($row['option_1'])) {
            return 'option_1';
        }
        if (empty($row['option_2'])) {
            return 'option_2';
        }
        if (empty($row['correct_ans']) || !in_array($row['correct_ans'], [1, 2, 3, 4, 5])) {
            return 'correct_ans';
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

    public function endColumn(): string
    {
        return 'L';
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => [$this, 'onAfterImport'],
        ];
    }

    public function onAfterImport(AfterImport $event): void
    {
        if (!isset($this->importId)) {
            return;
        }

        $processed = (int) Cache::get($this->cacheKey('processed'), 0);
        $imported  = (int) Cache::get($this->cacheKey('imported'), 0);
        $skipped   = (int) Cache::get($this->cacheKey('skipped'), 0);

        $reasons = [];
        foreach (self::REASONS as $key => $label) {
            $count = (int) Cache::get($this->cacheKey('reason:' . $key), 0);
            if ($count > 0) {
                $reasons[$label] = $count;
            }
        }

        $this->notifyUser(new QuestionsImportResult(
            status: 'completed',
            fileName: $this->fileName,
            processed: $processed,
            imported: $imported,
            skipped: $skipped,
            skipReasons: $reasons,
        ));

        $this->forgetCache();
    }

    public function failed(Throwable $e): void
    {
        if (!isset($this->importId)) {
            return;
        }

        $this->notifyUser(new QuestionsImportResult(
            status: 'failed',
            fileName: $this->fileName,
            errorMessage: $e->getMessage(),
        ));

        $this->forgetCache();
    }

    protected function recordImport(): void
    {
        Cache::increment($this->cacheKey('processed'));
        Cache::increment($this->cacheKey('imported'));
    }

    protected function recordSkip(string $reason): void
    {
        Cache::increment($this->cacheKey('processed'));
        Cache::increment($this->cacheKey('skipped'));
        Cache::increment($this->cacheKey('reason:' . $reason));
    }

    protected function notifyUser(QuestionsImportResult $notification): void
    {
        if (!$this->userId) {
            return;
        }

        $userModel = config('auth.providers.users.model');
        $user = $userModel::find($this->userId);

        if ($user) {
            $user->notify($notification);
        }
    }

    protected function forgetCache(): void
    {
        Cache::forget($this->cacheKey('processed'));
        Cache::forget($this->cacheKey('imported'));
        Cache::forget($this->cacheKey('skipped'));
        foreach (array_keys(self::REASONS) as $key) {
            Cache::forget($this->cacheKey('reason:' . $key));
        }
    }

    protected function cacheKey(string $suffix): string
    {
        return "adash-exam:questions-import:{$this->importId}:{$suffix}";
    }
}
