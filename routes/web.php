<?php

use Takshak\Adash\Http\Middleware\GatesMiddleware;
use Illuminate\Support\Facades\Route;
use Takshak\Exam\Http\Controllers\Admin\Exam\PaperController;
use Takshak\Exam\Http\Controllers\Admin\Exam\PaperSectionController;
use Takshak\Exam\Http\Controllers\Admin\Exam\QuestionController;
use Takshak\Exam\Http\Controllers\Admin\Exam\QuestionGroupController;
use Takshak\Exam\Http\Controllers\Admin\Exam\UserPaperController as ExamUserPaperController;
use Takshak\Exam\Http\Controllers\ExamController;
use Takshak\Exam\Http\Controllers\UserPaperController;

Route::middleware('web')->group(function () {
    Route::middleware(['auth', GatesMiddleware::class])
        ->prefix('admin/exam')
        ->name('admin.exam.')
        ->group(function () {
            Route::get('questions/upload', [QuestionController::class, 'upload'])->name('questions.upload');
            Route::post('questions/upload', [QuestionController::class, 'uploadDo'])->name('questions.upload.do');
            Route::get('questions/sample-download', [QuestionController::class, 'sampleDownload'])->name('questions.sample-download');
            Route::resource('questions', QuestionController::class);

            Route::resource('question-groups', QuestionGroupController::class);

            Route::prefix('papers/{paper}')->name('papers.')->group(function () {
                Route::get('questions/edit', [PaperController::class, 'questionsEdit'])->name('questions.edit');
                Route::post('questions/update', [PaperController::class, 'questionsUpdate'])->name('questions.update');
                Route::resource('sections', PaperSectionController::class);
            });
            Route::resource('papers', PaperController::class);

            Route::prefix('user-papers')->name('user-papers.')->group(function () {
                Route::get('/', [ExamUserPaperController::class, 'index'])->name('index');
                Route::get('{userPaper}', [ExamUserPaperController::class, 'show'])->name('show');
                Route::get('delete/{userPaper}', [ExamUserPaperController::class, 'destroy'])->name('delete');
            });

            Route::prefix('htmx')->name('htmx.')->group(function () {
                Route::get('questions/list', [QuestionController::class, 'htmxList'])->name('questions.list');
            });
        });

    Route::middleware(['auth', GatesMiddleware::class])->prefix('exam')->name('exam.')->group(function () {
        Route::get('papers', [ExamController::class, 'papers'])->name('papers');
        Route::get('{paper}/instructions', [ExamController::class, 'instructions'])->name('instructions');
        Route::get('{paper}/start', [ExamController::class, 'start'])->name('start');
        Route::get('{paper}/paper', [ExamController::class, 'paper'])->name('paper');
        Route::post('{paper}/save/{question}', [ExamController::class, 'questionSave'])->name('question-save');
        Route::get('{paper}/mark/{question}', [ExamController::class, 'questionMark'])->name('question-mark');
        Route::get('{paper}/submit', [ExamController::class, 'submit'])->name('submit');

        Route::get('{paper}/user-papers', [UserPaperController::class, 'index'])->name('user-papers.index');
        Route::get('{paper}/user-papers/{userPaper}', [UserPaperController::class, 'show'])->name('user-papers.show');
    });
});
