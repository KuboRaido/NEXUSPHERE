<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nice extends Model
{
    protected $table = 'nices';
    protected $primaryKey = 'nice_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['prc_id', 'user_id'];

    public function post()
    {
        return $this->belongsTo(Prc::class, 'prc_id', 'prc_id');
    }
}
