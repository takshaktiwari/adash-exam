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

Additionally you can publish just required assets. 

- Publish Seeders: `php artisan vendor:publish --tag="adash-exam-seeds". You will get the seeders in seeders folder.

- Publish Routes: `php artisan vendor:publish --tag="adash-exam-routes". You will get a file named *exam.php* in routes folder.

- Publish Views: `php artisan vendor:publish --tag="adash-exam-views". You will get the views in *exam* folder in components and in admin folder.
