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

class DmController extends Controller
{
   private function avatarUrl(?User $u): string
   {
      $default = asset('images/default-avatar.png');
      if (!$u)return $default;

      $path = $u->avatarUrl 
         ?? $u->icon
         ?? $u->icon_path
         ?? $u->avatar_path
         ?? $u->profile_photo_path
         ?? null;

      if (!$path) return $default;

      if (\Illuminate\Support\Str::startsWith($path, ['http://','https://','/'])){
         return $path;
      }

      if (file_exists(public_path('icons/' . $path))){
         return asset('storage/icons/' . $path);
      }

      return asset($path);
   }
   # DM一覧（フロント）
   public function dmlistfront(){
      return view('dm-list');
   }

   # DM一覧（バック）相手ごとの「最後の一通」
   public function dmlistback(Request $request){
      $me = $request->user()?->getAuthIdentifier() ?? Auth::id();
      abort_if(!$me, 401, 'Unauthenticated');

      $dmTable = (new Dm)->getTable();

      # (a,b) = (min(sender,receiver),max(sender,receiver))ごとに最新行ID
      $lastPerPair = Dm::selectRaw(
         'LEAST(sender_id,receiver_id) AS a,'.//ペアの一人目
         'GREATEST(sender_id,receiver_id) AS b,'.//ペアの二人目
         'MAX(dm_id) AS last_id'                //最新のメッセージID
      )
      ->whereNull('circle_id')
      ->whereNull('group_id')
      ->where(function ($q) use ($me) {
         $q->where('sender_id', $me)->orWhere('receiver_id', $me);
      })
      ->groupBy('a','b');
      # 最新メッセージを取得して新しい順に並べる
      $rows = Dm::from($dmTable . ' AS dms')
            ->joinSub($lastPerPair, 'lp', function ($join) {
               $join->on('dms.dm_id', '=', 'lp.last_id');
            })
            ->orderBy('dms.created_at', 'desc')
            ->get(['dms.*','lp.a','lp.b']);

      # 相手ユーザー情報をまとめて取得
      $list = [];
      $partnerIds = [];

      foreach ($rows as $r) {
         $partnerId = ((int)$r->a ===(int)$me) ? (int)$r->b : (int)$r->a;
         $list[] = [
            'conversation_id' => $r->conversation_id,
            'dm_key' => $r->dm_key ?? (min($r->sender_id,$r->receiver_id).'-'.max($r->sender_id,$r->receiver_id)),
            'partner_id' => $partnerId,
            'partner_name' => null, 
            'partner_icon' => null,
            'last_message' => $r->message_text,
            'last_time'    => $r->created_at ?->toISOString(),
         ];
         $partnerIds[] = $partnerId;
   }

   //グループ取得
   $myGroups = \App\Models\Group::whereHas('members', function($q) use ($me) {
      $q->where('groupmembers.user_id', $me);
   })->with(['latestMessage'])->get();

   foreach ($myGroups as $g){
      $lastTime = $g->latestMessage ?-> created_at ??  $g->created_at;

      $iconUrl = $g->icon ? asset('storage/icons/' . $g->icon) : asset('images/default-avatar.png');

      $list[] = [
            'conversation_id' => 'group_' . $g->group_id,//Idをかぶらないようにする
            'partner_id' => $g->group_id,
            'partner_name' => $g->group_name, 
            'partner_icon' => $iconUrl,
            'last_message' => $g->latestMessage?->message_text ?? '',
            'last_time'    => $lastTime?->toISOString(),
            'is_group'     => true, //フロントで判別するためにフラグを立てる
            'icon'         => $g->icon,
      ];
   }

   //usort ユーザー定義のルールで配列を並び替える関数
   usort($list, function ($a,$b) {
      return ($b['last_time'] ?? '') <=> ($a['last_time'] ?? '');
   });

   $userPk = (new User)->getKeyName();
   $partnerIds[] = (int)$me;
   $users = User::whereIn($userPk, array_unique($partnerIds))
            ->get()->keyBy($userPk);
   // 自分のアイコンURL
   $meUser = $users[(int)$me] ?? null;
   $meIcon = $this->avatarUrl($meUser);
   foreach($list as &$t){
      if (!empty($t['is_group'])) continue; // グループの場合は名前などを上書きしない

      $u = $users[$t['partner_id']] ?? null;
      $t['partner_name'] = $u?->name ?? 'Unknown';
      $t['partner_icon'] = $u ? $this->avatarUrl($u) : ((int)$t['partner_id'] === (int)$me ? $meIcon : asset('images/default-avatar.png'));
   }
   unset($t);

   // dm一覧の未読数表示
   $unreadRows = DB::table('dms as dm')
      ->select('dm.sender_id as partner_id', DB::raw('COUNT(*) AS unread'))
      ->leftJoin('dm_reads as dr', function($join) use ($me) {
         $join->on('dr.partner_id', '=' , 'dm.sender_id')
            ->where('dr.user_id', '=', $me);
      })
      ->where('dm.receiver_id', $me)
      ->whereNull('dm.deleted_at')
      ->where(function ($q) {
         $q->whereNull('dr.last_read_at')
            ->orWhere('dm.created_at', '>', DB::raw('dr.last_read_at'));
      })
      ->groupBy('dm.sender_id')
      ->pluck('unread', 'partner_id');

   foreach($list as &$row){
         $partnerId = (int) $row['partner_id'];
         $row['unread_count'] = (int) ($unreadRows[$partnerId] ?? 0);
      }
      unset($row);

   return response()->json([
      'me_icon' => $meIcon,
      'data'    => $list,
   ]);
}

# 会話画面（フロント）
public function dmfront(Request $r){
      $to = $r->query('to');
      $partnerId = ($to === 'me' || $to === null) ? Auth::id() : (int) $to;
      $group = $r->group_id;
      if($group){
         $partnerName = \App\Models\Group::where('group_id', $group)->value('group_name');
      }else{
         $partnerName = User::where('user_id', $partnerId)->value('name');
      }
      return view('dm', compact('partnerId', 'partnerName'));
   }

# 会話ログ取得（バック）
public function dmback(?int $partner=null){
   $me = Auth::id();
   abort_if(!$me, 401, 'Unauthenticated');

   $userPk = (new User) -> getKeyName(); 
   $meUser = Auth::user();
   $partnerUser = User::where($userPk,$partner)->firstOrFail();
   $partnerReadAt = DB::table('dm_reads')
      ->where('user_id', $partner)
      ->where('partner_id', $me)
      ->value('last_read_at');
   
   if($partner === $me){
      //自分にDM
      $messages = Dm::where('sender_id',$me)
      ->where('receiver_id',$me)
      ->orderBy('created_at','asc')
      ->get(['dm_id','sender_id','receiver_id','message_text','dm_key','created_at']);
   } else{
      $messages = Dm::where(function($q) use($me,$partner){
         $q->where('sender_id',$me)->where('receiver_id',$partner);
      })->orWhere(function($q) use($me,$partner){
         $q->where('sender_id',$partner)->where('receiver_id',$me);
      })
      ->orderBy('created_at','asc')
      ->get(['dm_id','sender_id','receiver_id','message_text','created_at','dm_key']);
   }
   
   return response()->json([
      'participants' => [
         'me'     =>['id'=>$meUser->$userPk, 'name'=>$meUser->name, 'icon'=>$meUser->avatar_url],
         'partner'=>['id'=>$partnerUser->$userPk, 'name'=>$partnerUser->name,'icon'=>$partnerUser->avatar_url]
      ],
      'dms' => $messages->map(function($m) use ($partnerReadAt, $meUser, $userPk){
         $isMine = ((int) $m->sender_id === (int) $meUser->$userPk);
         $is_read = $isMine && $partnerReadAt ? $m->created_at <= $partnerReadAt : false;

         return[
            'id'        =>$m->dm_id,
            'from_id'   =>$m->sender_id,
            'to_id'     =>$m->receiver_id,
            'text'      =>\App\Support\TextHelper::linkify($m->message_text ?? ''),
            'dm_key'    =>$m->dm_key,
            'created_at'=>$m->created_at?->toISOString(),
            'is_read'   =>$is_read,
            'attachments'=>$m->Images_and_videos->map(function($rec){
                  $path = $rec->image ?: $rec->video;
                  $url  = $path ? asset('storage/dms/' . $path) : null;
                  $type = $rec->image ? 'image' : ($rec->video ? 'video' : 'file');
                  return ['type'=>$type,'url' =>$url];
            })->values(),
         ];
      }),
   ]);
}

   public function dmCircleBack(Circle $circle){
      $m = Dm::with('sender')->where('circle_id', $circle->circle_id)->whereNull('receiver_id')->orderBy('created_at')->get();
      abort_if(!$circle->members()->where('circle_users.user_id', Auth::id())->exists(), 403, 'サークルに参加していません');

      return response()->json([
         'participants' => [
            'me'     =>['id' => Auth::id()],
            'circle' =>['id' => $circle->circle_id, 'name' => $circle->circle_name],
         ],
         'dms' => $m->map(function (DM $dm){
            return[
            'id'        =>$dm->dm_id,
            'from_id'   =>$dm->sender_id,
            'text'      =>\App\Support\TextHelper::linkify($dm->message_text),
            'icon'      =>$this->avatarUrl($dm->sender),
            'created_at'=>$dm->created_at?->toISOString(),
            'is_read'   =>$dm->is_read,
            'attachments'=>$dm->Images_and_videos->map(function($rec){
                  return [
                     'type'=>$rec->image ? 'image' : ($rec->video ? 'video' : 'file'),
                     'url' =>asset('storage/dms/' . $rec->image ?: $rec->video),
                  ];
               }),
            ];
         }),
      ]);
   }

   public function dmGroup(Group $group){
      $m = Dm::with('sender')->where('group_id', $group->group_id)->whereNull('receiver_id')->orderBy('created_at')->get();
      abort_if(!$group->members()->where('groupmembers.user_id', Auth::id())->exists(), 403, 'グループに参加していません');
      
      return response()->json([
         'participants' => [
            'me'     =>['id' => Auth::id()],
            'group' =>['id' => $group->group_id, 'name' => $group->group_name],
         ],
         'dms' => $m->map(function (DM $dm){
            return[
            'id'        =>$dm->dm_id,
            'from_id'   =>$dm->sender_id,
            'text'      =>\App\Support\TextHelper::linkify($dm->message_text ?? ''),
            'icon'      =>$this->avatarUrl($dm->sender),
            'created_at'=>$dm->created_at?->toISOString(),
            'is_read'   =>$dm->is_read,
            'attachments'=>$dm->Images_and_videos->map(function($rec){
                  return [
                     'type'=>$rec->image ? 'image' : ($rec->video ? 'video' : 'file'),
                     'url' =>asset('storage/dms/' . $rec->image ?: $rec->video),
                  ];
               }),
            ];
         }),
      ]);
   }

   public function dmGroupCreate(Request $request){
         $request->validate([
            'group_name' => ['required','string','max:255',new NgWord],
            'user_ids'   => 'required|array',
            'user_ids.*' => 'integer|exists:users,user_id',
            'icon' => [ 'nullable','image','max:2048' ],
         ]);

         $iconPath = null;
         if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('', 'direct');
         }

         $meId = Auth::id();

         $group = Group::create([
            'group_name'    => $request->group_name,
            'members_count' => 0,
            'icon'          => $iconPath,
         ]);

         // 作成者と選択メンバーをマージして登録
         $memberIds = array_unique(array_merge([$meId], $request->user_ids));

         // メンバーを一括追加
         $group->members()->sync($memberIds);

         // メンバー数更新
         $group->update([
            'members_count' => count($memberIds),
         ]);

         return response()->json(['ok' => true, 'message' => 'グループを作成しました。']);
   
   }

   // public function dmGroupJoin(Request $request){
   //    $request->validate([
   //          'group_name' => 'required|string|max:255',
   //          'user_ids'   => 'required|array',
   //          'user_ids.*' => 'integer|exists:users,user_id',
   //       ]);

   //    $meId = Auth::id();

   //    // 作成者と選択メンバーをマージして登録
   //    $memberIds = array_unique(array_merge([$meId], $request->user_ids));

   //    // メンバーを一括追加
   //    $group->members()->sync($memberIds);

   //    // メンバー数更新
   //    $group->update([
   //       'members_count' => count($memberIds),
   //    ]);
   // }

   public function dmsendback(Request $request)
   {
      $me = $request ->user()?->getAuthIdentifier() ?? Auth::id();
      abort_if(!$me, 401, 'Unauthenticated');

      $circle_id = $request -> integer('circle_id');
      $group_id = $request -> integer('group_id');
      $userPk = (new User)->getKeyName();

      $baseRules = [
         'text' => ['nullable','string','max:5000'],
         'files.*' => ['nullable','file','max:51200','mimetypes:image/*,video/*'],
      ];
      if($circle_id){
         $data = $request->validate($baseRules + [
            'circle_id' => ['required', 'integer', 'exists:circles,circle_id'],
         ]);

         $dm = Dm::create([
         'sender_id'    => $me,
         'receiver_id'  => null,
         'message_text' => $data['text'] ?? null,
         'user_id'      => $me,
         'circle_id'    => $circle_id,
      ]);
      } elseif($group_id) {
         $data = $request->validate($baseRules + [
            'group_id' => ['required', 'integer', 'exists:groups,group_id'],
         ]);

         $dm = Dm::create([
         'sender_id'    => $me,
         'receiver_id'  => null,
         'message_text' => $data['text'] ?? null,
         'user_id'      => $me,
         'group_id'     => $group_id,
      ]);
      } else {
         $data = $request->validate($baseRules + [
            'to' => ['required', 'integer', "exists:users,{$userPk}"],
         ]);

         $dm = Dm::create([
         'sender_id'    => $me,
         'receiver_id'  => $data['to'],
         'message_text' => $data['text']??null,
         'user_id'      => $me,
         'circle_id'    => null,
         'group_id'     => null,
         ]);
      }

      $attachments = [];
      if($request->hasFile('files')){
         foreach ($request->file('files') as $file){
            $path  = $file->store('','dm');
            $mime  = $file->getMimeType();
            $isImg = str_starts_with($mime,'image/');
            $isMov = str_starts_with($mime,'video/');

         $rec = \App\Models\Images_and_videos::create([
               'image'  => $isImg ? $path : null,
               'video'  => $isMov ? $path : null,
               'dm_id'  => $dm->dm_id,
            ]);

            $attachments[] = [
               'type' => $rec->type,
               'url'  => $rec->url,
            ];
         }
      }
      return response()->json([
         'id'         => $dm->dm_id,
         'from_id'    => (int)$dm->sender_id,
         'to_id'      => (int)$dm->receiver_id,
         'text'       => \App\Support\TextHelper::linkify($dm->message_text ?? ''),
         'dm_key'     => $dm->dm_key,
         'created_at' => $dm->created_at->toISOString(),
         'attachments'=> $attachments,
      ], 201);
   }

   public function read(User $partner, Request $req)
   {
      $me = $req->user() ?? Auth::user();
      if(!$me) {
         return response()->json(['message' => 'Unauthenticated'], 401);
      }
      $partnerId = $partner->getKey();
      $circle = $req->integer('circle_id');
      $group = $req->integer('group_id');

      if($circle > 0){
         $meId = $me->getKey();
         DB::table('dm_reads')->upsert(
         [[
            'user_id'      => $meId, 
            'circle_id'    => $circle,
            'last_read_at' => now(),
            'updated_at'   => now(),
            'created_at'   => now(),
         ]],
         ['user_id','circle_id'], //衝突キー（unique）
         ['last_read_at','updated_at']//更新する列
         );

         $last = DB::table('dm_reads')
         ->where('user_id', $meId)
         ->where('circle_id', $circle)
         ->value('last_read_at');

         $unread = DB::table('dms')
         ->where('circle_id', $circle)   // 相手から
         ->when($last, fn($q) => $q->where('created_at', '>', $last))
         ->count();

         return response()->json(['ok'=>true, 'unread_count' => $unread]);
      }elseif($group > 0){
         $meId = $me->getKey();
         DB::table('dm_reads')->upsert(
         [[
            'user_id'      => $meId, 
            'group_id'    => $group,
            'last_read_at' => now(),
            'updated_at'   => now(),
            'created_at'   => now(),
         ]],
         ['user_id','group_id'], //衝突キー（unique）
         ['last_read_at','updated_at']//更新する列
         );

         $last = DB::table('dm_reads')
         ->where('user_id', $meId)
         ->where('group_id', $group)
         ->value('last_read_at');

         $unread = DB::table('dms')
         ->where('group_id', $group)   // 相手から
         ->when($last, fn($q) => $q->where('created_at', '>', $last))
         ->count();

         return response()->json(['ok'=>true, 'unread_count' => $unread]);
      }

      if ($partnerId !== null){
            $meId = $me->getKey();
            DB::table('dm_reads')->upsert(
            [[
               'user_id'      => $meId, 
               'partner_id'   => $partnerId,
               'last_read_at' => now(),
               'updated_at'   => now(),
               'created_at'   => now(),
            ]],
            ['user_id','partner_id'], //衝突キー（unique）
            ['last_read_at','updated_at']//更新する列
         );

         $meId      = Auth::id();

         $last = DB::table('dm_reads')
         ->where('user_id', $meId)
         ->where('partner_id', $partnerId)
         ->value('last_read_at');

         $unread = DB::table('dms')
         ->where('receiver_id', $meId)
         ->where('sender_id', $partnerId)   // 相手から
         ->when($last, fn($q) => $q->where('created_at', '>', $last))
         ->count();

         return response()->json(['ok'=>true, 'unread_count' => $unread]);

      }
   }
}
?>