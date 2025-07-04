<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    //   function index(){
    //全体取得　戻り値:Collection（配列）
    //      $profiles = Profile::all();
    //IDで検索　戻り値:Modelのインスタンス
     // $profile = Profile::find(1);
    //    dd($profiles);

                //Where(条件指定)
                //  $query = Profile::whre('name','=','okita');
                // これだじぇではデータはとれないので
                //条件にあう全体を取得　戻り値:Collection（配列）
                //$profile = Profile::where('name','=','okita')->get();
                //条件にあう先頭の1件を取得　戻り値:Modelのインスタンス
            // $profile = Profile::where('name','=','okita')->first();
            
        // return 'hello database'
        

}
