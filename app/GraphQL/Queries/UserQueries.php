<?php

namespace App\GraphQL\Queries;

use App\Models\Attachment;
use App\Models\Notification;
use App\Models\User;
use App\Models\Goal;
use App\Repositories\GoalRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;

class UserQueries
{
    public function __construct(
        AttachmentService $attachmentService,
        NotificationRepository $notificationRepository,
        UserRepository $user_repository,
        GoalRepository $goal_repository
    ) {
        $this->attachment_service = $attachmentService;
        $this->notificationRepository = $notificationRepository;
        $this->user_repository = $user_repository;
        $this->goal_repository = $goal_repository;
    }

    public function me()
    {
        $user = User::where("id", Auth::id())->first();
        $user->notification = Notification::selectRaw("type, count(*) as count")
            ->where("user_receive_id", Auth::id())
            ->whereRaw("(is_read is null or is_read = 0)")
            ->groupBy("type")
            ->get()
            ->pluck('count', 'type')
            ->toArray();
        $user = $this->attachment_service->mappingAvatarBackgroud($user);
        return $user;
    }

    public function user($_, array $args)
    {
        return $this->user_repository->user($args);
    }
    public function listUsers($_, array $args)
    {
        $orderBy = $args["orderBy"];
        $users = User::selectRaw("*, DATE(created_at) as start_smile_eye_time")                      
                        ->orderBy($orderBy['column'], $orderBy['order'])
                        ->paginate($args["first"], ['*'], 'page', $args["page"]);
        $page = $users->toArray()["last_page"];
        $userIds = $users->pluck('id');
        $goals = Goal::whereIn("user_id", $userIds)
                        ->whereNull('parent_id')
                        ->get()->groupBy('user_id');
        $listUsers["data"] = $users->map(function($user) use ($goals){
            $user->self_goals = @$goals[$user->id];
            $user->inviation_goals = @$this->goal_repository->myGoalsAchieve($user->id);
            $user->shared_goals = @$this->goal_repository->myGoalShare($user->id);
            $user->org = "ppe";
            $user->member_number = random_int(0,100);
            $user->business_field = "edu";
            $user->relation_level = random_int(1,5);
            $user->profit_forSelf = random_int(0, 99999);
            $user->profit_for_smile_eye = random_int(0, 99999);
            return $user;
        });
        $listUsers["total_page"] = $page;
        return  $listUsers;
    }
}