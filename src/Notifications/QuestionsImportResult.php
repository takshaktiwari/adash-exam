<?php

namespace Takshak\Exam\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class QuestionsImportResult extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $status,
        public string $fileName,
        public int $processed = 0,
        public int $imported = 0,
        public int $skipped = 0,
        public array $skipReasons = [],
        public ?string $errorMessage = null,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        if ($this->status === 'failed') {
            return [
                'title'     => 'Question import failed',
                'message'   => "Importing \"{$this->fileName}\" failed: {$this->errorMessage}",
                'status'    => 'failed',
                'file_name' => $this->fileName,
                'error'     => $this->errorMessage,
            ];
        }

        $message = "{$this->imported} of {$this->processed} question(s) imported from \"{$this->fileName}\".";
        if ($this->skipped > 0) {
            $message .= " {$this->skipped} skipped.";
        }

        return [
            'title'        => 'Question import finished',
            'message'      => $message,
            'status'       => 'completed',
            'file_name'    => $this->fileName,
            'processed'    => $this->processed,
            'imported'     => $this->imported,
            'skipped'      => $this->skipped,
            'skip_reasons' => $this->skipReasons,
        ];
    }
}
