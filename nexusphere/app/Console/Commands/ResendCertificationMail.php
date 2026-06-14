<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\VerificationEmail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ResendCertificationMail extends Command
{
    protected $signature = 'resend-mail';
    protected $description = '認証メール再送信';

    public function handle(): int
    {
        $users = User::whereNull('email_verified_at')->get();

        foreach ($users as $user) {
            Mail::to($user->mail)->send(new VerificationEmail($user));
        }

        $this->info((string) $users->count() . '件のユーザに認証メールを再送しました。');

        return Command::SUCCESS;
    }
}

