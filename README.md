# Introduction

An extension for exam panel for `takshak/adash` admin panel package. You will have exam paper management setup the questions for users who will attempt the exam.
Install package via composer:

    composer required takshak/adash-exam

Migrate the tables:

    php artisan migrate

To get dummy data seeded, publish the seeders and run them individually:

    php artisan vendor:publish --tag=adash-exam-seeds

Run seeders:

    php artisan db:seed --class=QuestionGroupSeeder
    php artisan db:seed --class=QuestionSeeder
    php artisan db:seed --class=PaperSeeder


Add routes to sidebar in admin (components/admin/sidebar.php)

    <x-exam-exam:admin-sidebar-links />

To customize views publish views and you will get exam layout in layouts folder, components and admin pages will be in components/exam and admin/exam respectively.

    php artisan vendor:publish --provider="Takshak\Exam\ExamServiceProvider"
