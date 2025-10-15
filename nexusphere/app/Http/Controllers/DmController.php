<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Dm;
use App\Models\User;


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

      if (Storage::disk('public')->exists($path)){
         return Storage::url($path);
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
         'LEAST(sender_id,receiver_id) AS a,'.
         'GREATEST(sender_id,receiver_id) AS b,'.
         'MAX(dm_id) AS last_id'
      )
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

   $userPk = (new User)->getKeyName();
   $partnerIds[] = (int)$me;
   $users = User::whereIn($userPk, array_unique($partnerIds))
            ->get()->keyBy($userPk);

   $meUser = $users[(int)$me] ?? null;
   $meIcon = $this->avatarUrl($meUser);
   foreach($list as &$t){
      $u = $users[$t['partner_id']] ?? null;
      $t['partner_name'] = $u?->name ?? 'Unknown';
      $t['partner_icon'] = $u ? $this->avatarUrl($u) : ((int)$t['partner_id'] === (int)$me ? $meIcon : asset('images/default-avatar.png'));
   }
   unset($t);

  return response()->json([
   'me_icon' => $meIcon,
   'data'    => $list,
  ]);
 }

 # 会話画面（フロント）
 public function dmfront(Request $r){

      $to = $r->query('to');
      $partnerId = ($to === 'me' || $to === null) ? Auth::id() : (int) $to;
      return view('dm', compact('partnerId'));
   }

 # 会話ログ取得（バック）
 public function dmback(int $partner){
   $me = Auth::id();
   abort_if(!$me, 401, 'Unauthenticated');

   $userPk = (new User) -> getKeyName(); 
   $meUser = Auth::user();
   $partnerUser = User::where($userPk,$partner)->firstOrFail();

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
         'me'     =>['id'=>$meUser->$userPk, 'name'=>$meUser->name, 'avatar'=>$meUser->avatar_url],
         'partner'=>['id'=>$partnerUser->$userPk, 'name'=>$partnerUser->name,'avatar'=>$partnerUser->avatar_url]
      ],
      'dms' => $messages->map(function($m){
         return[
            'id'        =>$m->dm_id,
            'from_id'   =>$m->sender_id,
            'to_id'     =>$m->receiver_id,
            'text'      =>$m->message_text,
            'dm_key'    =>$m->dm_key,
            'created_at'=>$m->created_at?->toISOString(),
            'attachments'=>$m->Images_and_videos->map(function($rec){
                   $path = $rec->image ?: $rec->video;
                   $url  = $path ? Storage::url($path) : null;
                   $type = $rec->image ? 'image' : ($rec->video ? 'video' : 'file');
                   return ['type'=>$type,'url' =>$url];
            })->values(),
         ];
      }),
   ]);
   }

   public function dmsendback(Request $request)
   {
      $me = $request ->user()?->getAuthIdentifier() ?? Auth::id();
      abort_if(!$me, 401, 'Unauthenticated');

      $userPk = (new User)->getKeyName();
      $data = $request->validate([
         'to'    => ['required','integer', "exists:users,{$userPk}"],
         'text'  => ['nullable','string','max:5000'],
         'files.*' => ['nullable','file','max:51200','mimetypes:image/*,video/*'],
      ],[],['to' =>'宛先ユーザーID','text'=>'メッセージ本文']);
   
      $dm = Dm::create([
         'sender_id'    => $me,
         'receiver_id'  => $data['to'],
         'message_text' => $data['text']??null,
         'user_id'      => $me,
         'circle_id'    => null,
      ]);

      $attachments = [];
      if($request->hasFile('files')){
         foreach ($request->file('files') as $file){
            $path  = $file->store('dm/'.date('Y/m/d'),'public');
            $mime  = $file->getMimeType();
            $isImg = str_starts_with($mime,'image/');
            $isMov = str_starts_with($mime,'video/');

         $rec = \App\Models\Images_and_videos::create([
               'prc_id' => 0,
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
         'text'       => (string)($dm->message_text ?? ''),
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

      $meId = $me->getKey();
      $partnerId = $partner->getKey();
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
      $partnerId = $partner->getKey();

      $last = DB::table('dm_reads')
       ->where('user_id', $meId)
       ->where('partner_id', $partnerId)
       ->value('last_read_at');

      $unread = DB::table('dms')
       ->where('receiver_id', $meId)      // 自分あて
       ->where('sender_id', $partnerId)   // 相手から
       ->when($last, fn($q) => $q->where('created_at', '>', $last))
       ->count();

      return response()->json(['ok'=>true, 'unread_count' => $unread]);
   }
}
?>