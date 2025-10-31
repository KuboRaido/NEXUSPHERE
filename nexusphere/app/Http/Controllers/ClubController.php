<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Circle;

class CircleController extends Controller
{
    /**
     * サークル一覧を表示
     */
    public function index(Request $request)
    {
        // 🔍 検索キーワード取得
        $keyword = $request->input('keyword');

        // 🔄 検索条件付きでDBから取得
        $query = Circle::query();

        if (!empty($keyword)) {
            $query->where('name', 'LIKE', "%{$keyword}%")
                  ->orWhere('category', 'LIKE', "%{$keyword}%");
        }

        $circles = $query->orderBy('id', 'desc')->paginate(10); // ページネーションも対応

        return view('circles.index', compact('circles', 'keyword'));
    }

    /**
     * サークル参加処理（ボタン用）
     */
    public function join($id)
    {
        // TODO: ログインユーザーの参加処理を書く（中間テーブルなどが必要）
        // ここでは仮処理としてメッセージだけ返す
        return redirect()->back()->with('success', 'サークルに参加しました！');
    }
}
