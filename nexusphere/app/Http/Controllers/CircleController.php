<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Circle;
use Illuminate\Support\Facades\Storage;

class CircleController extends Controller
{
    public function circleFront()
    {
            return view('circle');
    }
    public function circleBack()
    {
        $rows = Circle::orderByDesc('created_at')->get();
        $list = $rows->map(function (Circle $circle){
            return [
                'circle_id' => $circle->circle_id,
                'circle_name' => $circle->circle_name,
                'icon' => $circle->icon,
                'category' => $circle->category,
                'members_count' => $circle->members_count,
                'sentence' => $circle->sentence,
            ];
        })->values();
        return response()->json($list);
    }
    public function circleCreateFront()
    {
            return view('circleCreate');
    }
    public function circleCreate(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sentence'    => 'required|string|max:255',
            'image'      => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'category'    => 'nullable|string',
        ]);

          $iconPath = null;
          if ($request->hasFile('images')) {
              $iconPath = $request->file('image')->store('circle-icons', 'public');
            }

        Circle::create([
            'owner_id'    => Auth::id(),
            'circle_name' => $data['name'],
            'sentence'    => $data['sentence'],
            'category'    => $data['category'],
            'icon'      => $iconPath,
        ]);


        return redirect()->route('circle')->with('status', 'プロフィールを更新しました。');
    }
    public function circleProfileFront()
    {
            return view('circleProfile');
    }
    public function circlePostFront()
    {
        return view('circlePost');
    }
}