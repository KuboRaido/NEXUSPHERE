<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Dm;
use App\Models\User;
use App\Models\Circle;
use App\Models\Group;
use App\Rules\NgWord;
use App\Services\ConversationService;
use App\Services\DirectMessageService;
use App\Services\UnreadService;

class DmController extends Controller
{
    public function __construct(
        private ConversationService $conversationService,
        private DirectMessageService $directMessageService,
        private UnreadService $unreadService
    ) {
    }

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

    public function dmlistfront()
    {
        return view('dm-list');
    }

    public function dmlistback(Request $request)
    {
        $userId = (int)($request->user()?->getAuthIdentifier() ?? Auth::id());
        abort_if(!$userId, 401, 'Unauthenticated');

        $conversations = $this->conversationService->getUserConversations($userId);
        $data = $conversations->toArray();

        $meIcon = asset('images/default-avatar.png');
        foreach ($data as $conv) {
            if ((int)$conv['partner_id'] === (int)$userId) {
                $meIcon = $conv['partner_icon'] ?? asset('images/default-avatar.png');
                break;
            }
        }

        return response()->json([
            'me_icon' => $meIcon,
            'data' => $data,
        ]);
    }

    public function dmfront(Request $r)
    {
        $to = $r->query('to');
        $partnerId = ($to === 'me' || $to === null) ? Auth::id() : (int)$to;
        $group = $r->group_id;
        if ($group) {
            $partnerName = Group::where('group_id', $group)->value('group_name');
        } else {
            $partnerName = User::where('user_id', $partnerId)->value('name');
        }
        return view('dm', compact('partnerId', 'partnerName'));
    }

    public function dmback(?int $partner = null)
    {
        $me = Auth::id();
        abort_if(!$me, 401, 'Unauthenticated');

        $query = $partner === $me
            ? Dm::where('sender_id', $me)->where('receiver_id', $me)
            : Dm::where(function ($q) use ($me, $partner) {
                $q->where('sender_id', $me)->where('receiver_id', $partner);
            })->orWhere(function ($q) use ($me, $partner) {
                $q->where('sender_id', $partner)->where('receiver_id', $me);
            });

        $messages = $query
            ->with('Images_and_videos')
            ->orderBy('created_at', 'asc')
            ->get();

        $meUser = Auth::user();
        $partnerUser = User::findOrFail($partner);
        $userPk = (new User)->getKeyName();

        return response()->json([
            'participants' => [
                'me' => ['id' => $meUser->$userPk, 'name' => $meUser->name, 'icon' => $meUser->avatar_url],
                'partner' => ['id' => $partnerUser->$userPk, 'name' => $partnerUser->name, 'icon' => $partnerUser->avatar_url]
            ],
            'dms' => $messages->map(fn($m) => [
                'id' => $m->dm_id,
                'from_id' => (int)$m->sender_id,
                'to_id' => (int)$m->receiver_id,
                'text' => \App\Support\TextHelper::linkify($m->message_text ?? ''),
                'dm_key' => $m->dm_key,
                'created_at' => $m->created_at?->toISOString(),
                'attachments' => $m->Images_and_videos->map(fn($rec) => [
                    'type' => $rec->image ? 'image' : ($rec->video ? 'video' : 'file'),
                    'url' => asset('storage/dms/' . ($rec->image ?: $rec->video)),
                ])->values(),
            ]),
        ]);
    }

    public function dmCircleBack(Circle $circle)
    {
        $m = Dm::with('sender')->where('circle_id', $circle->circle_id)->whereNull('receiver_id')->orderBy('created_at')->get();
        abort_if(!$circle->members()->where('circle_users.user_id', Auth::id())->exists(), 403, 'サークルに参加していません');

        return response()->json([
            'participants' => [
                'me' => ['id' => Auth::id()],
                'circle' => ['id' => $circle->circle_id, 'name' => $circle->circle_name],
            ],
            'dms' => $m->map(fn($dm) => [
                'id' => $dm->dm_id,
                'from_id' => $dm->sender_id,
                'text' => \App\Support\TextHelper::linkify($dm->message_text ?? ''),
                'icon' => $this->avatarUrl($dm->sender),
                'created_at' => $dm->created_at?->toISOString(),
                'is_read' => $dm->is_read,
                'attachments' => $dm->Images_and_videos->map(fn($rec) => [
                    'type' => $rec->image ? 'image' : ($rec->video ? 'video' : 'file'),
                    'url' => asset('storage/dms/' . ($rec->image ?: $rec->video)),
                ])->values(),
            ]),
        ]);
    }

    public function dmGroup(Group $group)
    {
        $m = Dm::with('sender')->where('group_id', $group->group_id)->whereNull('receiver_id')->orderBy('created_at')->get();
        abort_if(!$group->members()->where('groupmembers.user_id', Auth::id())->exists(), 403, 'グループに参加していません');

        return response()->json([
            'participants' => [
                'me' => ['id' => Auth::id()],
                'group' => ['id' => $group->group_id, 'name' => $group->group_name],
            ],
            'dms' => $m->map(fn($dm) => [
                'id' => $dm->dm_id,
                'from_id' => $dm->sender_id,
                'text' => \App\Support\TextHelper::linkify($dm->message_text ?? ''),
                'icon' => $this->avatarUrl($dm->sender),
                'created_at' => $dm->created_at?->toISOString(),
                'is_read' => $dm->is_read,
                'attachments' => $dm->Images_and_videos->map(fn($rec) => [
                    'type' => $rec->image ? 'image' : ($rec->video ? 'video' : 'file'),
                    'url' => asset('storage/dms/' . ($rec->image ?: $rec->video)),
                ])->values(),
            ]),
        ]);
    }

    public function dmGroupCreate(Request $request)
    {
        $request->validate([
            'group_name' => ['required', 'string', 'max:255', new NgWord],
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,user_id',
            'icon' => ['nullable', 'image', 'max:2048'],
        ]);

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('', 'direct');
        }

        $meId = Auth::id();

        $group = Group::create([
            'group_name' => $request->group_name,
            'members_count' => 0,
            'icon' => $iconPath,
        ]);

        $memberIds = array_unique(array_merge([$meId], $request->user_ids));
        $group->members()->sync($memberIds);
        $group->update(['members_count' => count($memberIds)]);

        return response()->json(['ok' => true, 'message' => 'グループを作成しました。']);
    }

    public function dmGroupJoin(Request $request)
    {
        $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,group_id'],
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['integer', 'exists:users,user_id'],
        ]);

        $meId = Auth::id();
        abort_if(!$meId, 401, 'ログインされていません');
        $group = Group::findOrFail($request->integer('group_id'));

        $memberIds = array_unique(array_merge([$meId], $request->input('user_ids', [])));
        $group->members()->syncWithoutDetaching($memberIds);
        $group->update(['members_count' => $group->members()->count()]);

        return back();
    }

    public function dmsendback(Request $request)
    {
        $me = $request->user()?->getAuthIdentifier() ?? Auth::id();
        abort_if(!$me, 401, 'Unauthenticated');

        $circleId = $request->integer('circle_id');
        $groupId = $request->integer('group_id');
        $userPk = (new User)->getKeyName();

        $baseRules = [
            'text' => ['nullable', 'string', 'max:5000'],
            'files.*' => ['nullable', 'file', 'max:51200', 'mimetypes:image/*,video/*'],
        ];

        if ($circleId) {
            $data = $request->validate($baseRules + [
                'circle_id' => ['required', 'integer', 'exists:circles,circle_id'],
            ]);

            $dm = $this->directMessageService->send(
                Auth::user(),
                $data['text'] ?? '',
                $request->file('files', []),
                null,
                $circleId,
                null
            );
        } elseif ($groupId) {
            $data = $request->validate($baseRules + [
                'group_id' => ['required', 'integer', 'exists:groups,group_id'],
            ]);

            $dm = $this->directMessageService->send(
                Auth::user(),
                $data['text'] ?? '',
                $request->file('files', []),
                null,
                null,
                $groupId
            );
        } else {
            $data = $request->validate($baseRules + [
                'to' => ['required', 'integer', "exists:users,{$userPk}"],
            ]);

            $dm = $this->directMessageService->send(
                Auth::user(),
                $data['text'] ?? '',
                $request->file('files', []),
                $data['to'],
                null,
                null
            );
        }

        $attachments = $dm->Images_and_videos->map(fn($rec) => [
            'type' => $rec->type,
            'url' => $rec->url,
        ])->values();

        return response()->json([
            'id' => $dm->dm_id,
            'from_id' => (int)$dm->sender_id,
            'to_id' => (int)$dm->receiver_id,
            'text' => \App\Support\TextHelper::linkify($dm->message_text ?? ''),
            'dm_key' => $dm->dm_key,
            'created_at' => $dm->created_at->toISOString(),
            'attachments' => $attachments,
        ], 201);
    }

    public function read(User $partner, Request $req)
    {
        $me = $req->user() ?? Auth::user();
        if (!$me) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $partnerId = $partner->getKey();
        $circleId = $req->integer('circle_id');
        $groupId = $req->integer('group_id');

        if ($circleId > 0) {
            $unreadCount = $this->unreadService->markAsRead((int)$me->getKey(), $circleId, 'circle');
            return response()->json(['ok' => true, 'unread_count' => $unreadCount]);
        } elseif ($groupId > 0) {
            $unreadCount = $this->unreadService->markAsRead((int)$me->getKey(), $groupId, 'group');
            return response()->json(['ok' => true, 'unread_count' => $unreadCount]);
        }

        if ($partnerId !== null) {
            $unreadCount = $this->unreadService->markAsRead((int)$me->getKey(), (int)$partnerId, 'direct');
            return response()->json(['ok' => true, 'unread_count' => $unreadCount]);
        }

        return response()->json(['ok' => false], 400);
    }
}
