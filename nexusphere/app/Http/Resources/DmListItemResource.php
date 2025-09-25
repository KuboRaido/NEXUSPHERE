<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DmListItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'conversation_id' => $this['conversation_id']??null,
            'dm_key' => $this['dm_key'],
            'partner_id' => $this['partner_id'],
            'partner_name' => $this['partner_name'],
            'last_message' => $this['last_message'],
            'last_time' => $this['last_time'],
        ];
    }
}
