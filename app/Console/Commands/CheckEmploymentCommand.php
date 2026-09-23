<?php

namespace App\Console\Commands;

use App\Jobs\CheckEmploymentJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckEmploymentCommand extends Command
{
    protected $signature = 'app:check-employment-command {--reset : Aylanishni boshidan boshlash}';

    protected $description = 'Userlarning hozirgi ish joyini egov orqali tekshiradi (minutiga 50 ta request)';

    public function handle()
    {
        if ($this->option('reset')) {
            Cache::forget(CheckEmploymentJob::CURSOR_KEY);
        }

        // Oldingi job hali navbatda yoki ishlayotgan bo'lsa ShouldBeUnique uni qo'shmaydi
        CheckEmploymentJob::dispatch();

        $this->info('Cursor: ' . (int) Cache::get(CheckEmploymentJob::CURSOR_KEY, 0));
    }
}
