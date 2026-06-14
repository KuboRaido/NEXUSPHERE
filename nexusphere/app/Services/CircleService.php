<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Circle;
use App\Models\Circle_requests;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class CircleService
{
    public function createCircle(User $owner, array $data, ?string $iconPath = null): Circle
    {
        $circle = DB::transaction(function () use ($owner, $data, $iconPath) {
            $circle = Circle::create([
                'owner_id' => $owner->user_id,
                'circle_name' => $data['name'],
                'sentence' => $data['sentence'],
                'category' => $data['category'] ?? null,
                'icon' => $iconPath,
                'members_count' => 0,
            ]);

            $circle->members()->syncWithoutDetaching([$owner->user_id]);
            $circle->update(['members_count' => $circle->members()->count()]);

            return $circle;
        });

        return $circle;
    }

    public function updateCircle(Circle $circle, User $user, array $data, ?string $iconPath = null): Circle
    {
        if ($circle->owner_id !== $user->user_id) {
            abort(403, 'Unauthorized');
        }

        if ($iconPath) {
            $circle->icon = $iconPath;
        }

        if (!empty($data['circle_name'])) {
            $circle->circle_name = $data['circle_name'];
        }

        if (!empty($data['sentence'])) {
            $circle->sentence = $data['sentence'];
        }

        $circle->save();

        return $circle;
    }

    public function leaveCircle(Circle $circle, User $user): void
    {
        DB::transaction(function () use ($circle, $user) {
            $remainingMembers = $circle->members()
                ->where('circle_users.user_id', '!=', $user->user_id)
                ->orderBy('created_at')
                ->get();

            $circle->members()->detach($user->user_id);

            if ($remainingMembers->isEmpty()) {
                $circle->delete();
            } elseif ($circle->owner_id === $user->user_id) {
                $nextOwner = $remainingMembers->first();
                $circle->owner_id = $nextOwner->user_id;
                $circle->members_count = $remainingMembers->count();
                $circle->save();
            }
        });
    }

    public function requestJoin(Circle $circle, User $user): void
    {
        try {
            Circle_requests::updateOrCreate(
                ['user_id' => $user->user_id, 'circle_id' => $circle->circle_id],
                ['status' => 'pending', 'request_at' => now()]
            );
        } catch (QueryException $e) {
            report($e);
            throw new \Exception('参加申請が送信できませんでした');
        }
    }

    public function approveRequest(Circle_requests $circleRequest, User $owner): void
    {
        if ($circleRequest->circle->owner_id !== $owner->user_id) {
            abort(403, 'Unauthorized');
        }

        DB::transaction(function () use ($circleRequest) {
            $circleRequest->update(['status' => 'approved']);
            $circleRequest->circle->members()->syncWithoutDetaching($circleRequest->user_id);
            $circleRequest->circle->update([
                'members_count' => $circleRequest->circle->members()->count(),
            ]);
        });
    }

    public function rejectRequest(Circle_requests $circleRequest, User $owner): void
    {
        if ($circleRequest->circle->owner_id !== $owner->user_id) {
            abort(403, 'Unauthorized');
        }

        $circleRequest->update(['status' => 'rejected']);
    }
}
