<?php

namespace App\Repositories;

use App\Models\CoachMember;
use App\Models\Goal;
use App\Models\GoalMember;
use App\Models\JapaneseGoal;
use App\Models\JapanesePost;
use App\Models\Note;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;
use ppeCore\dvtinh\Services\MediaService;

class CoachMemberRepository
{
    private $goal_repository;

    public function __construct(
            GoalRepository $goal_repository,
            AttachmentService $attachmentService
        )
    {
        $this->goal_repository = $goal_repository;
        $this->attachment_service = $attachmentService;
    }
    public function createCoachMember($args)
    {
        $args["user_id"] = Auth::id();
        $args = array_diff_key($args, array_flip(['teacher_id']));
        return CoachMember::create($args);
    }
    public function upsertCoachMember($args)
    {
        if(!isset($args['user_id'])){
            $args['user_id'] = Auth::id();
        }
        $coachMember =  CoachMember::updateOrCreate(
                            ['user_id' => $args['user_id']],
                            $args
                            );
        return  $coachMember;
    }
    public function updateCoachMember($args)
    {
        $update = tap(CoachMember::findOrFail($args["id"]))
                ->update($args);
        return $update;
    }
    public function deleteCoachMember($args)
    {
        $delete = CoachMember::find($args['id']);
        return $delete->delete();
    }
    public function deleteMyMember($args)
    {
        $user_id = Auth::id();
        $coachMember = CoachMember::where('user_id',$args['user_id'])->first();
        $delete = array_diff(@$coachMember->teacher_ids ?? [], [$user_id]);
        $update = tap(CoachMember::findOrFail($coachMember->id))
                            ->update(['teacher_ids' => $delete]);
        return  $update ;
    }
    public function myListCoachMembers($args)
    {
        $userId = Auth::id();
        $goalMembers = GoalMember::SelectRaw("id, goal_id, add_user_id as user_id, teacher_id, created_at")
                                    ->where('teacher_id',  $userId)
                                    ->get();
        $goalIds = $goalMembers->pluck('goal_id');
        $goals = Goal::whereIn('id', @$goalIds ?? [])->get();
        $getIds = $goals->pluck('id');
        $listMembers = $goalMembers->whereIn('goal_id', @$getIds ?? []);
        $typeNotis = ["diary", "achieve", "edit_diary", "communication", "sing_with_friend"];

        $listMembers = $listMembers->map(function($list) use($typeNotis){
            $numberMember = $this->numberMember($list->user_id);
            $notification = $this->notification($list->user_id, Auth::id(), $typeNotis);
            $countMissing = [
                            'message' => 0,
                            'call' => 0,
                            'notification' => @$notification['count'] ?? 0
                            ];
            $list->user->number_member = $numberMember->number_member;
            $list->user->count_missing = $countMissing;
            return $list;
        });
        return $listMembers;
    }
    public function numberMember($userId)
    {
        $numberMember = GoalMember::SelectRaw('Count(teacher_id) as number_member')
                                        ->where('teacher_id', $userId)
                                        ->first();
        return $numberMember;
    }
    public function notification($userId, $userReceiveId, $type)
    {
        $notification = Notification::selectRaw("count(*) as count")
                                        ->where("user_id", $userId)
                                        ->where("user_receive_id", $userReceiveId)
                                        ->whereIn("type", $type)
                                        ->whereRaw("(is_read is null or is_read = 0)")
                                        ->first()
                                        ->toArray();
        return $notification;
    }
    public function myListSupportMembers($args)
    {
        $userId = Auth::id();
        $payments = Payment::all();
        $getIds = $payments->pluck('goal_id');
        $goals = Goal::whereIn('id', @$getIds ?? [])->get();
        $checkIdGoals = $goals->pluck('id');
        $payments = $payments->whereIn('goal_id', @$checkIdGoals ?? []);
        
        $payments = $payments->map(function($payment) {
            $numberMember = $this->numberMember($payment->add_user_id);
            $payment->user = @$payment->add_user;
            $payment->user->number_member = $numberMember->number_member;
            $payment->user->status = @$payment->status ?? "trial";
            return $payment;
        });
        return $payments;
    }

    public function detailCoachMembers($args)
    {
        $userId = $args['user_id'];
        $goalMember = GoalMember::where('add_user_id', $userId)
                                    ->where('teacher_id', Auth::id())
                                    ->get();
        $getIds = $goalMember->pluck('goal_id');
        $goals = Goal::whereIn('id', @$getIds ?? [])->get();

        $posts = JapanesePost::where('user_id', $userId)
                                ->orderBy('created_at', 'DESC')
                                ->take(3)
                                ->get();

        $diary = JapaneseGoal::where('user_id', $userId)
                                ->where('type', 'like', 'diary')
                                ->take(3)
                                ->get();
        
        $achieves = $this->goal_repository->myGoalsAchieve(Auth::id());
        $achieves = $achieves->where('user_id', $userId);
        
        $notes = Note::where('user_id', $userId)->take(3)->get();
        $goals = $goals->map(function($goal) use ($posts, $diary, $notes){
                $goal->posts = @$posts;
                $goal->diary = @$diary;
                $goal->notes = @$notes;
                return $goal;
        });
        $user = User::find($userId);

        $user = $this->attachment_service->mappingAvatarBackgroud($user);
        $user->achieves = @$achieves;
        $user->goals = @$goals;
        return $user;   
    }
}
