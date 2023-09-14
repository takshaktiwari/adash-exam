<?php

namespace Takshak\Exam;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class ExamServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //$this->commands([InstallCommand::class]);
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'exam');
        $this->loadViewComponentsAs('exam', [
            View\Components\Exam\AdminSidebarLinks::class,
            View\Components\Exam\ExamLayout::class,
            View\Components\Exam\ExamSidebar::class,
            View\Components\Exam\Papers::class,
            View\Components\Exam\UserPapers::class,
            View\Components\Exam\UserPaperDetail::class,
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');


        $this->publishes([
            __DIR__ . '/../database/seeders' => database_path('seeders'),
            __DIR__ . '/../resources/views' => resource_path('views'),
            __DIR__ . '/../storage/questions_import_sample.xlsx' => storage_path('app/downloadable/questions_import_sample.xlsx'),
        ]);

        $this->publishes([
            __DIR__ . '/../database/seeders' => database_path('seeders'),
        ], 'adash-exam-seeds');

        Paginator::useBootstrap();
    }

}
