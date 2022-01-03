<?php

namespace App\Repositories;

use App\Models\Achieve;
use App\Models\CoachMember;
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
    private $coach_member_repository;
    public function __construct(
        GoalMemberRepository $goalMember_repository,
        NotificationRepository $notification_repository,
        GeneralInfoRepository $generalInfo_repository,
        CoachMemberRepository $coach_member_repository
    ) {
        $this->goalMember_repository = $goalMember_repository;
        $this->generalInfo_repository = $generalInfo_repository;
        $this->notification_repository = $notification_repository;
        $this->coach_member_repository = $coach_member_repository;
    }
    public function createGoalTemplate($args)
    {
        $args['user_id'] = Auth::id();
        $checkGoal = Goal::find($args['goal_id']);
        if(@$args['status'] == 'pending'){
            if (!isset($checkGoal->price) || @$checkGoal->price == 0.00) {
                throw new Error("Please set price for the goal.");
            }
        }
        if(strtolower(@$args['status']) == 'accept' || strtolower(@$args['status']) == 'confirmed'){
            $coachMember = CoachMember::where('user_id', $checkGoal->user_id)->first();
            $goalIds = array_merge(@$coachMember->goal_ids ?? [], [$args['goal_id']]);
            $upsert = [
                    'user_id' => @$checkGoal->user_id,
                    'goal_ids' => $goalIds
                    ];
            $upsertCoachMember = $this->coach_member_repository->upsertCoachMember($upsert);
        }
        $user = User::where('id', Auth::id())
                        ->where('roles', 'LIKE', '%admin%')
                        ->first();
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
        $sellGoal = @$args['sell_goal'] ?? 0;
        if($sellGoal != 0){
            $this->notification_repository
                ->staticNotification('sell_goal_template', $template->goal_id, $template, [$template->goal->user->id]);
        }
        return $template;
    }

    public function updateGoalTemplate($args)
    {
        $args['user_id'] = Auth::id();
        $checkGoal = Goal::find($args['goal_id']);
        if(strtolower(@$args['status']) == 'accept' || strtolower(@$args['status']) == 'confirmed'){
            $coachMember = CoachMember::where('user_id', $checkGoal->user_id)->first();
            $goalIds = array_merge(@$coachMember->goal_ids ?? [], [$args['goal_id']]);
            $upsert = [
                    'user_id' => $checkGoal->user_id,
                    'goal_ids' => $goalIds
                    ];
            $upsertCoachMember = $this->coach_member_repository->upsertCoachMember($upsert);
        }
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
        $goalTemplate = GoalTemplate::find($args['id']);
        if (isset($goalTemplate)) {
            return $goalTemplate->delete();
        }
        return  false;
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
            $price  = $this->sumPriceSellGoal($template->goal_id);
            $template->sum_price = @$price->money ?? 0;
            $template->number_done = 0;
            $template->number_trials = 0;
            return $template;
        });
        return $goalTemplate;
    }
    public function sumPriceSellGoal($id){
        $payments = Payment::selectRaw('*, SUM(money) as money')
                            ->where('goal_id', $id)
                            ->whereIn('status', ['accept', 'paidConfirmed', 'done'])
                            ->first();
        return @$payments;
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
        $allUser = User::count('id');

        $goalTemplate = $goalTemplate->map(function($template) use($goals, $allUser) {
           $sumAchieve = @$this->countAchieve($template->goal->general_info->id);
           $sumShare = @$this->countShare($template->goal->general_info->id);
           $sum = $sumShare->sum_share + $sumAchieve->sum_achieve;

           $numberMember = $this->numberMember($template->goal_id);
           $memberPercent = $this->convertToPercent($numberMember, $allUser);

           $numberTrial = $this->percentAllPayment($template->goal_id, 'trial');
           $numberEndTrial = $this->percentAllPayment($template->goal_id, 'EndTrial');
           $numberOnBuy = $this->percentAllPayment($template->goal_id, 'onBuy');
           $numberPairConfirmed = $this->percentAllPayment($template->goal_id, 'PaidConfirmed');
           $numberDone = $this->percentAllPayment($template->goal_id, 'done');


            $trialPercent = 0;
            $endTrialPercent = 0;
            $onBuyPercent = 0;
            $paidConfirmedPercent = 0;
            $donePercent = 0;

            if ($numberMember > 0){
                $trialPercent = $this->convertToPercent($numberTrial, $numberMember);
                $endTrialPercent = $this->convertToPercent($numberEndTrial, $numberMember);
                $onBuyPercent = $this->convertToPercent($numberOnBuy, $numberMember);
                $paidConfirmedPercent = $this->convertToPercent($numberPairConfirmed, $numberMember);
                $donePercent = $this->convertToPercent($numberDone, $numberMember);
            }
           $template->goal = @$goals[$template->goal_id];
           $template->goal->sum_achieve_share = $sum;

           $template->number_member = $numberMember;
           $template->member_percent = $memberPercent;

           $template->number_trials = $numberTrial;
           $template->trials_percent = $trialPercent;

           $template->number_end_trial = $numberEndTrial;
           $template->end_trial_percent = $endTrialPercent;

           $template->number_buy_on = $numberOnBuy;
           $template->buy_on_percent = $onBuyPercent;

           $template->number_paid= $numberPairConfirmed;
           $template->paid_percent = $paidConfirmedPercent;

           $template->number_done = $numberDone;
           $template->done_percent = $donePercent;

            return $template;
        });
        return @$goalTemplate;
    }
    public function convertToPercent($number, $sum){
        return ($number / $sum) * 100;
    }
    public function percentAllPayment($goalid, $status)
    {
        $payment = Payment::where('goal_id', $goalid)
                            ->where('status', 'like', '%'.$status.'%')
                            ->count('goal_id');
        return $payment;
    }
    public function CountMemberPayment($goalid, $status = [])
    {
        $payMent = Payment::selectRaw("COUNT(goal_id) as `sum`")
                            ->where('goal_id', $goalid)
                            ->whereIn('status', $status)
                            ->first();
        return $payMent;
    }

    public function numberMember($goalid)
    {
        $goalMember = GoalMember::where('goal_id', $goalid)
                                    ->count('add_user_id');
        return $goalMember;
    }
    public function myGoalTemplateUnpaid($args)
    {
        $status = ['accept', 'paused','paid','confirmed', "paidConfirmed", "done"];
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