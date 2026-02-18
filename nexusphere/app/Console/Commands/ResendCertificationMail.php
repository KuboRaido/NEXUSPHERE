<?php

namespace App\Console\Commands;

use App\Mail\VerificationEmail;
use App\Models\User;
use AWS\CRT\Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ResendCertificationMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '認証メール再送信';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $users = User::whereNull('email_verified_at')->get();
        foreach($users as $user) {
            Mail::to($user->mail)->send(new VerificationEmail($user));
        }
        $count = count($users);
        $this->info("{$count}件のユーザに認証メールを再送しました。");

        return Command::SUCCESS;
    }
}

