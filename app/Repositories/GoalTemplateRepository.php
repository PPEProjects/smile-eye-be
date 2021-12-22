<?php

namespace App\Repositories;

use App\Models\Achieve;
use App\Models\GeneralInfo;
use App\Models\Goal;
use App\Models\GoalMember;
use App\Models\GoalTemplate;
use App\Models\Payment;
use App\Models\PublishInfo;
use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class GoalTemplateRepository{

    private $goalMember_repository;
    private $generalInfo_repository;
    private $notification_repository;

    public function __construct(
        GoalMemberRepository $goalMember_repository,
        NotificationRepository $notification_repository,
        GeneralInfoRepository $generalInfo_repository
    ) {
        $this->goalMember_repository = $goalMember_repository;
        $this->generalInfo_repository = $generalInfo_repository;
        $this->notification_repository = $notification_repository;
    }
    public function createGoalTemplate($args)
    {
        $args['user_id'] = Auth::id();
        $user = User::where('id', Auth::id())
                        ->where('roles', 'LIKE', '%admin%')
                        ->first();
        $checkGoal = Goal::find($args['goal_id']);
        if(@$args['status'] != 'pending' && !isset($checkGoal->price) || @$checkGoal->price == 0.00){
             throw new Error("Please set price for the goal.");          
        }
        if(isset($user)){
            $goalTemplate = GoalTemplate::where('goal_id', $args['goal_id'])
                                            ->first();
            $args['checked_time'] = (@$goalTemplate->checked_time ?? 0) + 1;
        }
        $template = GoalTemplate::updateOrCreate([
                        'goal_id' => $args['goal_id']
                        ],$args);
        if(strtolower(@$args['status']) == 'inviting')
        {
            $this->notification_repository
                ->staticNotification('goal_to_template', $template->goal_id, $template, [$template->goal->user->id]);
        }
        return $template;   
    }

    public function updateGoalTemplate($args)
    {    
        $args['user_id'] = Auth::id();   
        $user = User::where('id', Auth::id())
                        ->where('roles', 'LIKE', '%admin%')
                        ->first();
        if(isset($user)){
            $goalTemplate = GoalTemplate::where('goal_id', $args['goal_id'])
                                            ->first();
            $args['checked_time'] = @$goalTemplate->checked_time ?? 0 + 1;
        }
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
            $goalTemplate = GoalTemplate::where('goal_id', $args['goal_id'])->first();
        }
        else
        {
            $goalTemplate = GoalTemplate::find($args['id']);
        }
        $goalMember = GoalMember::where('goal_id', @$goalTemplate->goal_id)->get();
        $getIdUser = $goalMember->pluck('add_user_id');
        $members = User::whereIn('id', @$getIdUser ?? [])->get();
        $goalTemplate->members = $members;
        return $goalTemplate;
    }

    public function myGoalTemplate($args){ 
        $status = @$args['status'] ?? 'all';
         $myGoals = Goal::whereNull('parent_id')
                         ->where('user_id', Auth::id())
                         ->get();
        $getIds = $myGoals->pluck('id');
        $goalTemplate = GoalTemplate::whereIn('goal_id',@$getIds ?? [])->get();
        if($status != 'all'){
            $goalTemplate = $goalTemplate->filter(function ($template) use ($status) {
                return false !== stristr($template->status, $status);
            });
        }
        $goalTemplate = $goalTemplate->map(function($template) {
            $goalMember = $this->goalMember_repository
                                   ->CountNumberMemberGoal($template->goal_id);
            $numberBuyOn = $this->CountMemberPayment($template->goal_id, ['pending', 'sentReceipt','onBuy']);
            $numberPaid = $this->CountMemberPayment($template->goal_id, ['accept', 'paidConfirmed', 'done']);
            $template->number_member = $goalMember->number_member;
            $template->number_buy_on = $numberBuyOn->sum;
            $template->number_paid   = $numberPaid->sum;   
            $price  = (int) @$template->goal->price ?? 0;
            $template->sum_price = $price * $numberPaid->sum;
            $template->number_done = 0;
            $template->number_trials = 0;
            return $template;
        });   
        return $goalTemplate;
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
           $sumAchieve = @$this->countAchieve($template->goal->general_info->id);
           $sumShare = @$this->countShare($template->goal->general_info->id);
           $sum = $sumShare->sum_share + $sumAchieve->sum_achieve;
           $template->goal = @$goals[$template->goal_id];
           $template->goal->sum_achieve_share = $sum;
           $template->number_member = $goalMember->number_member; 
            return $template;
        });
        return @$goalTemplate;
    }
    
    public function CountMemberPayment($goalid, $status = [])
    {
        $payMent = Payment::selectRaw("COUNT(goal_id) as `sum`")
                            ->where('goal_id', $goalid)
                            ->whereIn('status', $status)
                            ->first();
        return $payMent;
    }
    public function myGoalTemplateUnpaid($args)
    {   
        $status = ['accept', 'paused','paid','confirm', "paidConfirmed", "done"];
        $goalMember = GoalMember::where('add_user_id', Auth::id())->get();
        $getIdgoals = $goalMember->pluck('goal_id');
        $goalTemplate = GoalTemplate::wherein('goal_id', @$getIdgoals ?? [])
                                     ->whereIn('status', $status)
                                     ->get();
        $idGoalTemplate = $goalTemplate->pluck('goal_id')->toArray();
        $payment = Payment::where('add_user_id', Auth::id())
                        ->whereIn('status', $status)
                        ->whereIn('goal_id', @$idGoalTemplate ?? [])
                        ->get();
        $idGoalPayment = $payment->pluck('goal_id')->toArray();
        $idGoal = array_diff(@$idGoalTemplate ?? [], @$idGoalPayment ?? []); 
        $goal = Goal::whereIn('id', @$idGoal ?? [])->get();
        return $goal;
    }
    public function listGoalPotential($args){
        $numberPotential = @$args['number_potential'] ?? 0;
        $listGoalTemplate = $this->listGoalTemplates(['all']);
        $getIdTemplate = $listGoalTemplate->pluck('goal_id');
        $goals = Goal::whereNull('parent_id') 
                        ->whereNotIn('id', @$getIdTemplate ?? [])
                        ->get();
        $goals = $this->generalInfo_repository
                ->setType('goal')
                ->get($goals);
        $potential = [];
        foreach($goals as $goal){
            $sumAchieve = @$this->countAchieve($goal->general_info->id);
            $sumShare = @$this->countShare($goal->general_info->id);
            $sum = $sumShare->sum_share + $sumAchieve->sum_achieve;
            if( $sum >= $numberPotential){
                $goal->sum_achieve_share = $sum;
                $goal->status = 'potential';
                $potential[] = $goal;
            }
        }
        return $potential;
    }

    public function countAchieve($general_id){
        $sumAchieve = Achieve::selectRaw("COUNT(general_id) as `sum_achieve`")
                                ->where('general_id', $general_id)
                                ->where('status', 'LIKE' ,'%accept%')
                                ->first();
        return $sumAchieve;
    }
    public function countShare($general_id){
        $sumShare = PublishInfo::selectRaw("COUNT(general_id) as `sum_share`")
                                ->where('general_id', $general_id)
                                ->where('status', 'LIKE' ,'%accept%')
                                ->first();
        return $sumShare;
    }
}