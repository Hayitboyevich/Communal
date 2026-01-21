<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\Apartment\Models\Letter;

class LetterCommand extends Command
{

    protected $signature = 'app:letter';

    public function handle()
    {
        $letters = Letter::query()
            ->where('status', '<>', 3)
            ->get();

        if ($letters->isEmpty()) {
            $this->info('Yuborilmagan xatlar yo‘q');
            return;
        }

        $token = $this->authPost();

        $bar = $this->output->createProgressBar($letters->count());
        $bar->start();

        foreach ($letters as $letter) {
            $url = config('apartment.hybrid.url') . '/api/mail/' . $letter->letter_id;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url);

            if ($response->successful()) {
                $arr = $response->json();

                if (!empty($arr['IsSent']) && $arr['IsSent']) {
                    $letter->update(['status' => 2]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Barcha xatlar tekshirildi');
    }


    private function authPost()
    {
        $token = null;
        $url = config('apartment.hybrid.url').'/token';

        $data = [
            'grant_type' => config('apartment.hybrid.grant_type'),
            'username'   => config('apartment.hybrid.username'),
            'password'   => config('apartment.hybrid.password'),
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->withBody(http_build_query($data), 'application/x-www-form-urlencoded')
            ->get($url);

        if ($response->successful()) {
            $token = $response->json()['access_token'];
        }

        return $token;
    }


}
