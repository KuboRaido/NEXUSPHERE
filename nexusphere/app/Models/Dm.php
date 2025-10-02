<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dm extends Model
{
    use SoftDeletes;
    
    protected $table = 'dms';
    protected $primaryKey = 'dm_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['circle_id','user_id','group_id','sender_id','receiver_id','message_text','conversation_id','attachments','parent_id','reply_to_dm_id'];

    protected $casts = ['attachments' => 'array',];

    public function replyTo() {return $this->belongsTo(self::class,'reply_to_dm_id','dm_id');}#このメッセージが’どのメッセージに対する引用返信か’
    public function repliedBy() {return $this->hasMany(self::class,'reply_to_dm_id','dm_id');}#このメッセージを”引用返信しているメッセージ一覧”
    public function parent() {return $this->belongsTo(self::class,'parent_id','dm_id');}#このメッセージの”親（直上のコメント）”
    public function replies() {return $this->hasMany(self::class,'parent_id','dm_id');}#このメッセージの"子"

    public function sender(){
        return $this->belongsTo(User::class,'sender_id','user_id');
    }

    public function receiver(){
        return $this->belongsTo(User::class,'receiver_id','user_id');
    }

    protected static function booted()
    {
        static::saving(function (Dm $dm){
            if (!isset($dm->sender_id, $dm->receiver_id)){
                throw new \InvalidArgumentException('sender_idとreceiver_idは必須です。');
            }

            $a = (int) $dm->sender_id;
            $b = (int) $dm->receiver_id;
            $low = min($a,$b);
            $high = max($a,$b);

            $dm->dm_key = "{$low}-{$high}";
            
        });
    }

    public $timestamps = true;

    public function attachments()
    {
       return $this->hasMany(Images_and_video::class, 'dm_id', 'dm_id');
    }

}
