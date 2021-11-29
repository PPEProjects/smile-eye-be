<?php

namespace App\Repositories;

use App\Models\Goal;
use App\Models\GoalMember;
use App\Models\GoalTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class GoalTemplateRepository{

    private $goalMember_repository;
    private $generalInfo_repository;
    public function __construct(
        GoalMemberRepository $goalMember_repository,
        GeneralInfoRepository $generalInfo_repository
    ) {
        $this->goalMember_repository = $goalMember_repository;
        $this->generalInfo_repository = $generalInfo_repository;
    }
    public function createGoalTemplate($args)
    {
        $args['user_id'] = Auth::id();
            return GoalTemplate::updateOrCreate([
                        'goal_id' => $args['goal_id']
                        ],$args);
    }

    public function updateGoalTemplate($args)
    {    
        $args['user_id'] = Auth::id();   
        $args = array_diff_key($args, array_flip(['goal_id']));  
        return tap(GoalTemplate::findOrFail($args["id"]))->update($args);
    }

    public function deleteGoalTemplate($args)
    {
        $GoalTemplate = GoalTemplate::find($args['id']);
        return $GoalTemplate->delete();
    }

    public function detailGoalTemplate($args){
        if(isset($args['goal_id']))
        {
            $GoalTemplate = GoalTemplate::where('goal_id', $args['goal_id'])->first();
        }
        else
        {
            $GoalTemplate = GoalTemplate::find($args['id']);
        }
        return $GoalTemplate;
    }

    public function myGoalTemplate(){     
        return GoalTemplate::where('user_id',Auth::id())->get();
    }
    public function listGoalTemplates($args){
        $status = @$args["status"] ?? "all";
        switch ($status) 
        {
            case 'all':
                $goalTemplate = GoalTemplate::all();
                break;  
            default:
                $goalTemplate = GoalTemplate::where('status', 'like', $status)->get();
                break;
        }     
            
        $goalIds = $goalTemplate->pluck('goal_id');
        $goals = Goal::whereIn('id', @$goalIds ?? [])->get()->keyBy('id');
        $goals = $this->generalInfo_repository
            ->setType('goal')
            ->get($goals);
        $getId = $goals->pluck('id');
        $goalTemplate = $goalTemplate->whereIn('goal_id', @$getId ?? [])
                                    ->sortByDESC('id'); 
        $goalTemplate = $goalTemplate->map(function($template) use($goals) {
           $goalMember = $this->goalMember_repository->CountNumberMemberGoal($template->goal_id);
           $template->goal = @$goals[$template->goal_id];
           $template->number_member = $goalMember->number_member; 
            return $template;
        });
        return @$goalTemplate;
    }
    
}