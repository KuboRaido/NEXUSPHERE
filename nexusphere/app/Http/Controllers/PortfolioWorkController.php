<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PortfolioWorkService;
use DomainException;

class PortfolioWorkController extends Controller
{
    public function store(Request $request, PortfolioWorkService $service)
    {
        $site = $this->ownSite($request);
        $input = $request->only(['title','summary','tech_stack','url','team_role','why_built','why_tech','hardest_part','own_ideas','current_status']);

        $input['tech_stack'] = array_values(array_filter($request->input('tech_stack', []), fn ($tech) => ! blank($tech)));
        $newImages = array_filter($request->file('new_images', []));

        
        try{
            $service->save($site,null,$input,$newImages);
        } catch(DomainException $e) {
            return back()->withErrors(['portfolio_site' => $e->getMessage()])->withInput();
        }

        return back()->with('status','制作物の保存が完了しました。');
    }

    public function update(Request $request, PortfolioWorkService $service, int $workId)
    {
        $site = $this->ownSite($request);
        $work = $site->works()->findOrFail($workId);

        $input = $request->only(['title','summary','tech_stack','url','team_role','why_built','why_tech','hardest_part','own_ideas','current_status','delete_images']);

        $input['tech_stack'] = array_values(array_filter($request->input('tech_stack', []), fn ($tech) => ! blank($tech)));
        $newImages = array_filter($request->file('new_images', []));

        try{
            $service->save($site,$work,$input,$newImages);
        } catch(DomainException $e) {
            return back()->withErrors(['portfolio_site' => $e->getMessage()])->withInput();
        }

        return back()->with('status','制作物の保存が完了しました。');
    }
    private function ownSite(Request $request){
        return $request->user()->portfolioSite ?? abort(404);
    }
}
