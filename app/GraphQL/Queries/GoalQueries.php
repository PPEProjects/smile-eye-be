<?php

namespace App\GraphQL\Queries;

use App\Models\Achieve;
use App\Models\GeneralInfo;
use App\Models\Goal;
use App\Models\GoalMember;
use App\Models\GoalRank;
use App\Models\GoalTemplate;
use App\Models\JapaneseGoal;
use App\Models\JapaneseLearn;
use App\Models\PublishInfo;
use App\Models\User;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\GoalMemberRepository;
use App\Repositories\GoalRepository;
use App\Repositories\JapaneseGoalRepository;
use App\Repositories\JapaneseLearnRepository;
use App\Repositories\TodolistRepository;
use Illuminate\Support\Facades\Auth;

class GoalQueries
{
    private $generalinfo_repository;
    private $goal_repository;
    private $todolist_repository;
    private $japaneseLearn_repository;
    private $japaneseGoal_repository;
    private $goalMember_repository;

    public function __construct(
        GeneralInfoRepository   $generalinfo_repository,
        GoalRepository          $GoalRepository,
        TodoListRepository      $TodoListRepository,
        JapaneseLearnRepository $japaneseLearn_repository,
        JapaneseGoalRepository  $japaneseGoal_repository,
        GoalMemberRepository    $goalMember_repository
    )
    {
        $this->generalinfo_repository = $generalinfo_repository;
        $this->goal_repository = $GoalRepository;
        $this->todolist_repository = $TodoListRepository;
        $this->japaneseLearn_repository = $japaneseLearn_repository;
        $this->japaneseGoal_repository = $japaneseGoal_repository;
        $this->goalMember_repository = $goalMember_repository;
    }

    // public function goalsChildren($_, array $args)
    // {
    //     $cons = array_merge(['user_id' => Auth::id()], $args);
    //     $cons = array_intersect_key($cons, array_flip(['user_id', 'goal_id']));
    //     $goals = $this->goal_repository->getGoalsChildren($cons);
    //     return $goals;
    // }

    public function my_parentGoal($_, array $args)
    {
        $cons = array_merge(['user_id' => Auth::id()], $args);
        dd($cons);
        $cons = array_intersect_key($cons, array_flip(['user_id', 'parent_id', 'id']));
//        dd($cons);
        $goals = $this->goal_repository->getByParentGoal($cons);
//        dd($goals->toArray());
        return $goals;
    }


    public function myGoalsTreeSelect($_, array $args)
    {
        $goals = Goal::selectRaw('id as value, name as title, parent_id, root_id')
            ->where("root_id", $args['root_id'])
            ->orderByRaw('-`index` DESC, `created_at` ASC')
            ->get()
            ->toArray();
//        return $goals;
//        dd($goals);
        $tGoals = $this->goal_repository->buildTree($goals, $args['root_id']);
        return $tGoals;
    }

# Ver 2
    public function detailGoal($_, array $args)
    {
        $goal = Goal::where('id', $args['id'])->first();
        if ($goal) {
            if (isset($goal->parent_id)) {
                $goalRoot = Goal::where('id', $goal->root_id)->first();
            } else {
                $goalRoot = $goal;
            }
            $goal->goal_root = @$goalRoot;
            $goal->setting_for_sell = true;
            if (@$goalRoot->goalTemplate->status == "Confirmed") {
                $goal->setting_for_sell = false;
            }
            $generalInfo = $this->generalinfo_repository
                ->setType('goal')
                ->findByTypeId($goal->id);
            $goal->general_info = $generalInfo;
            $goal->type = $goal->user_id == Auth::id() ? 'goal_owner' : 'goal_member';
            return $goal;
        }
        return null;
    }

    public function myGoals1($_, array $args)
    {
        return $this->goal_repository->myGoals($args);
    }

    public function ganttChartSort($_, array $args)
    {
        return $this->goal_repository->ganttChartSort($args['id'], Auth::id());
    }

    public function myGoals($_, array $args)
    {
//        $this->goal_repository->calculatorProcessTodolist();
//        $this->goal_repository->calculatorProcessUpdate();
        $goals = Goal::SelectRaw("*, 'goal_owner' AS type")
            ->where('user_id', Auth::id())
            ->orderByRaw('`rank` ASC, `created_at` DESC');
        switch ($args['parent_id']) {
            case 'all':
                break;
            case 'root':
                $goals = $goals->where('parent_id', null);
                break;
            default:
                $goals = $goals->where('parent_id', $args['parent_id']);
                break;
        }
        $goals = $goals->get();
        //Get id goal from GoalMember
// start rank
        $myGoalIds = $goals->pluck('id')->toArray();
        $goalMember = GoalMember::where("add_user_id", Auth::id())
            ->whereNotIn('goal_id', @$myGoalIds ?? [])
            ->get()
            ->keyBy('goal_id');

//        dd($goalMember->toArray());
        $idGoalMembers = $goalMember->pluck('goal_id');
        $myGoalMember = Goal::SelectRaw("*, 'goal_member' AS type")
            ->whereIn('id', @$idGoalMembers ?? [])
            ->get();
//        $myGoalMember = $myGoalMember->map(function ($goal) use ($goalMember) {
//            $goal->created_at = @$goalMember[$goal->id]->created_at ?? $goal->created_at;
//            $goal->updated_at = @$goalMember[$goal->id]->updated_at ?? $goal->updated_at;
//            return $goal;
//        });
//        dd($myGoalMember->toArray());
        $goals = $myGoalMember->merge($goals);
        //---------//
//        $goals = $goals->sortByDESC('created_at');
        $getgoalIds = $goals->pluck('id')->toArray();
        $japaneseGoals = JapaneseGoal::whereIn('goal_id', $getgoalIds)->get();
        if (isset($japaneseGoals) && $args['parent_id'] == 'root') {
            $getIdJapaneseGoals = $japaneseGoals->pluck('goal_id')->toArray();
            $goals = $goals->whereNotIn('id', $getIdJapaneseGoals);
        }

        $goalIds = $goals->pluck('id')->toArray();
        // $goalTemplate = GoalTemplate::whereIn('goal_id',@$goalIds ?? [])
        //                                 ->get()
        //                                 ->keyBy('goal_id');
        // $nextGoal = $this->nextGoal($goalIds);
        $goals = $this->generalinfo_repository
            ->setType('goal')
            ->get($goals);
        $goals = $goals->map(function ($goal) use ($goalMember) {
            $countMember = @$this->goalMember_repository
                ->CountNumberMemberGoal($goal->id);
//            $rank = @$goal->rank;
//            if(isset($goalMember[$goal->id])){
//                $rank = @$goalMember[$goal->id]->rank;
//            }
//            $goal->rank = $rank;
// end rank
            $goal->number_member = $countMember->number_member;
            $goal->template = @$goal->goalTemplate;
            $goal->redirect_autoplay = url('/api/redirect/autoplay?root_id=' . $goal->id . '&user_id=' . Auth::id());
            // $goal->next_goal = @$nextGoal[$goal->id];
            return $goal;
        });

//        dd($goals
//            ->sortByDESC('updated_at')
//            ->pluck('id', 'updated_at')
//            ->toArray());
//        dd($goals->sortBy('rank')
//            ->sortByDESC('updated_at')
//            ->pluck('id', 'rank', 'updated_at'));
        // START RANK
        $ids = GoalRank::where('user_id', Auth::id())
            ->orderBy('pin_index', 'asc')
//            ->orderBy('updated_at', 'desc')
            ->get()
            ->pluck('goal_id');
//            ->toArray();
        $goalSorts = $goals->whereNotIn('id', $ids);
        $sorted = $ids->map(function ($id) use ($goals) {
            return $goals->where('id', $id)->first();
        });

        $sorted = $sorted->whereNotNull('id');
        $goalSorts = $goalSorts->merge($sorted);

//        dd($goalRankIds, $goals->toArray());
//        $goalRes = [];
//        foreach ($goalRankIds as $goalRankId) {
//
//        }
        // END RANK
        return $goalSorts;
//        return $goals;
//            ->sortBy('rank')
//            ->sortByDESC('updated_at');
    }

    public function nextGoal($goalIds = [])
    {
        $listGoals = Goal::whereIn('root_id', $goalIds)->orderByRaw('-`index` DESC')->get();
        foreach ($goalIds as $value) {
            $children[$value] = $this->japaneseGoal_repository->findBlock($listGoals, [$value]);
        }

        $japaneseLearn = JapaneseLearn::where('user_id', Auth::id())->get();
        $getIds = $japaneseLearn->pluck('goal_id')->toArray();
        $nextGoal = [];
        foreach ($goalIds as $value) {
            $findIdLearn = array_intersect($children[$value], $getIds);
            if ($findIdLearn != []) {
                $JapaneseLearn = $japaneseLearn->whereIn('goal_id', $findIdLearn)->sortByDESC('id')->first();
                $nextJapanseseLearn = $this->findNextGoals($JapaneseLearn->goal_id);

                if (isset($nextJapanseseLearn) || isset($prevJapanseseLearn)) {
                    $nextGoal[$value] = $nextJapanseseLearn;
                }
            }
            if (!isset($nextGoal[$value])) {
                $getInfoGoal = $this->findNextGoals(current($children[$value]));
                if (isset($getInfoGoal)) {
                    $nextGoal[$value] = $getInfoGoal;
                }
            }
        }
        return $nextGoal;
    }

    public function findNextGoals($id)
    {
        $japaneseGoal = $this->japaneseGoal_repository->getJapaneseGoal('goal_id', $id)->first();
        $getNameGoal = $this->japaneseGoal_repository->findGoal($id);
        $nextGoal = null;
        if (isset($japaneseGoal)) {
            $nextGoal = [
                'id' => @$japaneseGoal->goal_id,
                'name' => @$getNameGoal->name,
                'type' => @$japaneseGoal->type
            ];
        }
        return $nextGoal;
    }

    public function countGoals($_, array $args)
    {
        return $this->goal_repository->countGoals($args);
    }

    public function myGoalsAchieve($_, array $args)
    {
        $goals = $this->goal_repository->myGoalsAchieve();
        //NEXT GOAL
        $goalIds = $goals->pluck('id')->toArray();
        $nextGoal = $this->nextGoal($goalIds);
        $goals = $goals->map(function ($goal) use ($nextGoal) {
            $goal->next_goal = @$nextGoal[$goal->id];
            return $goal;
        });
        return $goals;
    }

    public function myGoalsTreeSort($_, array $args)
    {
        if (isset($args['not_auth'])) {
            return $this->goal_repository->getTreeSortByGoalId($args['id']);
        }
        $userId = Auth::id();
        //Get id goal from GoalMember
        $goalMember = GoalMember::where("add_user_id", $userId)
            ->where('goal_id', $args['id'])
            ->first();
        if (isset($goalMember)) {
            $userId = $goalMember->user_id;
        }
        return $this->goal_repository->getTreeSortByGoalId($args['id'], $userId);
    }

    public function goalsAchieveTreeSort($_, array $args)
    {
        if (!isset($args['id'])) {
            return false;
        }
//        return $this->goal_repository->goalsAchieveTreeSort($args['id']);
        $achieve = Achieve::where('general_infos.goal_id', $args['id'])
            ->join('general_infos', 'general_infos.id', '=', 'achieves.general_id')
            ->first();
        return $this->goal_repository->getTreeSortByGoalId(@$achieve->goal_id, null, @$achieve->goal->root_id);
    }

    public function reportGoal($_, array $args)
    {
        return $this->goal_repository->reportGoal($args);
    }

    public function myGoalShare($_, array $args)
    {
        return $this->goal_repository->myGoalShare();
    }

    public function goalShareTreeSort($_, array $args)
    {
        return $this->goal_repository->goalShareTreeSort($args);
    }

    public function listGoalsRoot($_, array $args)
    {
        $goals = Goal::select('*')->whereNull('parent_id');
        $orderBy = $args["orderBy"];
        if (isset($args["search"])) {
            foreach ($args['search'] as $key => $value) {
                $goals = $goals->where($key, 'like', '%' . $value . '%');
            }
        }
        $goals = $goals->orderBy($orderBy['column'], $orderBy['order'])
            ->paginate($args["first"], ['*'], 'page', $args["page"]);
        $page = $goals->toArray()["last_page"];
        $getIds = $goals->pluck('id');
        $getUserId = $goals->pluck('user_id');
        $checkUser = User::whereIn('id', @$getUserId ?? [])->get()->pluck('id');
        $goals = $goals->whereIn('user_id', @$checkUser ?? []);
        $templates = GoalTemplate::whereIn('goal_id', @$getIds ?? [])
            ->get()
            ->keyBy('goal_id');
        $goals = $goals->map(function ($goal) use ($templates) {
                $goal->status = @$templates[$goal->id]->status;
                $goalMember = $this->goalMember_repository->CountNumberMemberGoal($goal->id);
                $goal->number_member = $goalMember->number_member;
                return $goal;
        });
        $goalsRoot = ["goals" => $goals, "total_page" => $page];
        return $goalsRoot;
    }

    public function myGoalsPublish($_, array $args)
    {
        $goals = Goal::whereNull('parent_id')->get();
        $goalIds = $goals->pluck('id');
        $generalInfo = GeneralInfo::whereIn('goal_id', @$goalIds ?? [])->get();
        $generalIds = $generalInfo->pluck('id');
        $achieves = Achieve::whereIn('general_id', @$generalIds ?? [])
            ->where('status', 'like', 'accept')
            ->get();

        $listAchieves = [];
        $listIdAchieves = [];
        foreach ($achieves as $achieve) {
            $idAchieve = @$achieve->general->goal->id;
            $user = $achieve->user_invite;
            if (isset($idAchieve) && isset($user)) {
                $listAchieves[$idAchieve][] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => "achieve"
                ];
                $listIdAchieves[] = $idAchieve;
            }
        }
        $listShares = [];
        $listIdShares = [];
        $shares = PublishInfo::whereIn('general_id', @$generalIds ?? [])
            ->get();
        foreach ($shares as $share) {
            $idShare = @$share->general->goal->id;
            $user = $share->user_invite;
            if (isset($idShare) && isset($user)) {
                $listShares[$idShare][] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => "share " . @$share->rule ?? 'view'
                ];
                $listIdShares[] = $idShare;
            }
        }
        $idGoals = array_merge($listIdAchieves, $listIdShares);
        $goals = $goals->whereIn('id', @$idGoals ?? []);
        $goals = $goals->map(function ($goal) use ($listShares, $listAchieves) {
            $goal->achieves = @$listAchieves[$goal->id] ?? [];
            $goal->shares = @$listShares[$goal->id] ?? [];
            return $goal;
        });
        return $goals;
    }

}