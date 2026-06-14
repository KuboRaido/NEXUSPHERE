<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Dm;
use App\Models\User;
use App\Models\Circle;
use App\Models\Group;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ConversationService
{
    private function avatarUrl(?User $u): string
    {
        $default = asset('images/default-avatar.png');
        if (!$u) return $default;

        $path = $u->avatarUrl
            ?? $u->icon
            ?? $u->icon_path
            ?? $u->avatar_path
            ?? $u->profile_photo_path
            ?? null;

        if (!$path) return $default;

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        if (file_exists(public_path('icons/' . $path))) {
            return asset('storage/icons/' . $path);
        }

        return asset($path);
    }

    public function getUserConversations(int $userId, int $limit = 20): Collection
    {
        $dmTable = (new Dm)->getTable();

        $lastPerPair = Dm::selectRaw(
            'LEAST(sender_id,receiver_id) AS a,' .
            'GREATEST(sender_id,receiver_id) AS b,' .
            'MAX(dm_id) AS last_id'
        )
            ->whereNull('circle_id')
            ->whereNull('group_id')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->groupBy('a', 'b');

        $rows = Dm::from($dmTable . ' AS dms')
            ->joinSub($lastPerPair, 'lp', function ($join) {
                $join->on('dms.dm_id', '=', 'lp.last_id');
            })
            ->orderBy('dms.created_at', 'desc')
            ->get(['dms.*', 'lp.a', 'lp.b']);

        $list = [];
        $partnerIds = [];

        foreach ($rows as $r) {
            $partnerId = ((int)$r->a === (int)$userId) ? (int)$r->b : (int)$r->a;
            $list[] = [
                'conversation_id' => $r->conversation_id,
                'dm_key' => $r->dm_key ?? (min($r->sender_id, $r->receiver_id) . '-' . max($r->sender_id, $r->receiver_id)),
                'partner_id' => $partnerId,
                'partner_name' => null,
                'partner_icon' => null,
                'last_message' => $r->message_text,
                'last_time' => $r->created_at?->toISOString(),
            ];
            $partnerIds[] = $partnerId;
        }

        // グループ取得
        $myGroups = Group::whereHas('members', function ($q) use ($userId) {
            $q->where('groupmembers.user_id', $userId);
        })->with(['latestMessage'])->get();

        foreach ($myGroups as $g) {
            $lastTime = $g->latestMessage?->created_at ?? $g->created_at;
            $iconUrl = $g->icon ? asset('storage/icons/' . $g->icon) : asset('images/default-avatar.png');

            $list[] = [
                'conversation_id' => 'group_' . $g->group_id,
                'partner_id' => $g->group_id,
                'partner_name' => $g->group_name,
                'partner_icon' => $iconUrl,
                'last_message' => $g->latestMessage?->message_text ?? '',
                'last_time' => $lastTime?->toISOString(),
                'is_group' => true,
                'icon' => $g->icon,
            ];
        }

        // ソート
        usort($list, function ($a, $b) {
            return ($b['last_time'] ?? '') <=> ($a['last_time'] ?? '');
        });

        // ユーザー情報をバッチ取得
        $userPk = (new User)->getKeyName();
        $partnerIds[] = (int)$userId;
        $users = User::whereIn($userPk, array_unique($partnerIds))
            ->get()->keyBy($userPk);

        $meUser = $users[(int)$userId] ?? null;
        $meIcon = $this->avatarUrl($meUser);

        // パートナー情報を設定
        foreach ($list as &$t) {
            if (!empty($t['is_group'])) continue;

            $u = $users[$t['partner_id']] ?? null;
            $t['partner_name'] = $u?->name ?? 'Unknown';
            $t['partner_icon'] = $u ? $this->avatarUrl($u) : (((int)$t['partner_id'] === (int)$userId) ? $meIcon : asset('images/default-avatar.png'));
        }
        unset($t);

        // 未読数を取得
        $unreadRows = DB::table('dms as dm')
            ->select('dm.sender_id as partner_id', DB::raw('COUNT(*) AS unread'))
            ->leftJoin('dm_reads as dr', function ($join) use ($userId) {
                $join->on('dr.partner_id', '=', 'dm.sender_id')
                    ->where('dr.user_id', '=', $userId);
            })
            ->where('dm.receiver_id', $userId)
            ->whereNull('dm.deleted_at')
            ->where(function ($q) {
                $q->whereNull('dr.last_read_at')
                    ->orWhere('dm.created_at', '>', DB::raw('dr.last_read_at'));
            })
            ->groupBy('dm.sender_id')
            ->pluck('unread', 'partner_id');

        foreach ($list as &$row) {
            $partnerId = (int)$row['partner_id'];
            $row['unread_count'] = (int)($unreadRows[$partnerId] ?? 0);
        }
        unset($row);

        return collect($list)->slice(0, $limit);
    }

    public function getMessages(int $conversationId, string $type, int $page = 1): LengthAwarePaginator
    {
        $userId = auth()->id();

        $query = match ($type) {
            'direct' => $this->getDirectMessages($userId, $conversationId),
            'circle' => $this->getCircleMessages($conversationId),
            'group' => $this->getGroupMessages($conversationId),
            default => Dm::query()
        };

        return $query->paginate(20);
    }

    private function getDirectMessages(int $userId, int $partnerId): \Illuminate\Database\Eloquent\Builder
    {
        $partnerReadAt = DB::table('dm_reads')
            ->where('user_id', $partnerId)
            ->where('partner_id', $userId)
            ->value('last_read_at');

        if ($partnerId === $userId) {
            $query = Dm::where('sender_id', $userId)
                ->where('receiver_id', $userId);
        } else {
            $query = Dm::where(function ($q) use ($userId, $partnerId) {
                $q->where('sender_id', $userId)->where('receiver_id', $partnerId);
            })->orWhere(function ($q) use ($userId, $partnerId) {
                $q->where('sender_id', $partnerId)->where('receiver_id', $userId);
            });
        }

        return $query
            ->with('Images_and_videos')
            ->orderBy('created_at', 'asc')
            ->select(['dm_id', 'sender_id', 'receiver_id', 'message_text', 'created_at', 'dm_key'])
            ->chunk(50, function ($messages) use ($userId, $partnerReadAt) {
                foreach ($messages as $m) {
                    $m->append([
                        'is_read' => ((int)$m->sender_id === (int)$userId && $partnerReadAt) ? $m->created_at <= $partnerReadAt : false,
                        'text' => \App\Support\TextHelper::linkify($m->message_text ?? ''),
                        'attachments' => $m->Images_and_videos->map(fn($rec) => [
                            'type' => $rec->image ? 'image' : ($rec->video ? 'video' : 'file'),
                            'url' => $rec->url,
                        ]),
                    ]);
                }
            });
    }

    private function getCircleMessages(int $circleId): \Illuminate\Database\Eloquent\Builder
    {
        return Dm::with('sender')
            ->where('circle_id', $circleId)
            ->whereNull('receiver_id')
            ->with('Images_and_videos')
            ->orderBy('created_at', 'asc');
    }

    private function getGroupMessages(int $groupId): \Illuminate\Database\Eloquent\Builder
    {
        return Dm::with('sender')
            ->where('group_id', $groupId)
            ->whereNull('receiver_id')
            ->with('Images_and_videos')
            ->orderBy('created_at', 'asc');
    }
}
