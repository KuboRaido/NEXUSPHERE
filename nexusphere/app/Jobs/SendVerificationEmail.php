<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\VerificationEmail;
class SendVerificationEmail implements ShouldQueue
{
    #ジョブとして動くために必要なLaravelの標準機能セット（トレイト）を取り込んでいます。
    use Dispatchable,InteractsWithQueue, Queueable, SerializesModels;

    public User $user;
    private int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->userId = $user->getKey();
    }

    /*Execute the job.*/
    public function handle(): void
    {
        $user = User::find($this->userId);
        if (!$user){
            return;
        }
        Mail::to($user->mail)->send(new VerificationEmail($user));
    }
}
