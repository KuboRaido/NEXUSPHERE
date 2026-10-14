<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Major;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ← ここに上のコードを貼り付ける！
        $users = DB::table('users')
            ->select('user_id', 'subject', 'major')
            ->whereNotNull('subject')
            ->whereNotNull('major')
            ->get();

        foreach ($users as $user) {
            // 学科を検索
            $subject = DB::table('subjects')
                ->where('subject_name', trim($user->subject))
                ->select('subject_id')
                ->first();

            // 専攻を検索
            $major = DB::table('majors')
                ->join('subjects', 'majors.subject_id', '=', 'subjects.subject_id')
                ->where('subjects.subject_name', trim($user->subject))
                ->where('majors.major_name', trim($user->major))
                ->select('majors.major_id')
                ->first();

            // 更新
            DB::table('users')
                ->where('user_id', $user->user_id)
                ->update([
                    'subject_id' => $subject ? $subject->subject_id : null,
                    'major_id' => $major ? $major->major_id : null
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ロールバック時は NULL にリセット
        DB::table('users')->update([
            'subject_id' => null,
            'major_id' => null
        ]);
    }
};
