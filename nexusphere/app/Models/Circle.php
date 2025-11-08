<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Circle extends Model
{
    protected $table = 'circles';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['circle_id','circle_name','category','sentence','icon','owner_id','members_count'];
    protected $primaryKey = 'circle_id';

}
