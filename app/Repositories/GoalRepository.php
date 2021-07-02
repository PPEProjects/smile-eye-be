<?php

namespace App\Repositories;

use App\Models\Achieve;
use App\Models\GeneralInfo;
use App\Models\Goal;
use App\Models\Task;
use App\Models\Todolist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;

class GoalRepository
{
    private $attachment_service;
    private $user_repository;
    private $attachment_repository;
    private $generalinfo_repository;

    public function __construct(
        UserRepository $UserRepository,
        AttachmentService $AttachmentService,
        AttachmentRepository $attachment_repository,
        GeneralInfoRepository $generalinfo_repository
    ) {
        $this->user_repository = $UserRepository;
        $this->attachment_service = $AttachmentService;
        $this->attachment_repository = $attachment_repository;
        $this->generalinfo_repository = $generalinfo_repository;
    }

    public function find($id)
    {
        return Goal::find($id);
    }

    public function getByParentGoal($cons)
    {
        $goals = Goal::where('parent_id', null)
            ->where('id', $cons['id']);
        $goals = $goals->get();
        $gId = $goals->pluck('id')->flatten();
        $gs = Goal::where('parent_id', $gId)->get();
        $gs = $gs->map(function ($g) use ($gs) {
            $gId = $g['id'];
            $gchilds = Goal::where('parent_id', $gId)->get();
            $g['childs'] = $gchilds->toArray();
            return $g;
        });
        $goals['0']['childs'] = $gs;
        return $goals;
    }

    public function getChildrenGoals($parentId)
    {
        $goals = Goal::orderBy('id', 'desc');
        $goals = $goals->where('parent_id', $parentId);
        $goals = $goals->get();
        return $goals;
    }

    public function getGoalsByTaskId($taskId)
    {
        $goals = Goal::orderBy('id', 'desc');
        $goals = $goals->where('task_id', $taskId);
        $goals = $goals->get();
        return $goals;
    }

    public function getDetailGoal($id)
    {
        $goal = Goal::where('id', $id)->first();
        if ($goal) {
            $generalInfo = $this->generalinfo_repository
                ->setType('goal')
                ->findByTypeId($goal->id);
            $goal->general_info = $generalInfo;
            return $goal;
        }
        return null;
    }

    public function getByIds($goalIds)
    {
        $goals = Goal::whereIn('id', $goalIds)->get();
        return $goals;
    }


// Ver 2
    public function findByTaskId($taskId)
    {
        return Goal::selectRaw('goals.*')
            ->join('tasks', 'goals.id', '=', 'tasks.goal_id')
            ->where('tasks.id', $taskId)
            ->first();
    }

    public function getTreeSortByGoalId($goalId, $userId = null)
    {
        $goals = Goal::selectRaw('id, id as value, name, name as title, parent_id')
            ->orderBy('id', 'desc');
        if ($userId) {
            $goals = $goals->where("user_id", $userId);
        }
        $goals = $goals->get();

        $tree = self::buildTree($goals->toArray(), $goalId);
        $pTree = $goals->where('id', $goalId)->first();
        if ($pTree) {
            $pTree->children = $tree;
            return ['tree' => [$pTree], 'goals' => $goals];
        }
        return ['tree' => [], 'goals' => $goals];
    }

    public function goalsAchieveTreeSort($goalId)
    {
        $goal_ids = $this->myGoalsAchieve()->pluck("id")->toArray();
        $temp = in_array($goalId, $goal_ids);
        if ($temp) {
            $goals = Goal::selectRaw('id, id as value, name, name as title, parent_id, status')
                ->orderBy('id', 'desc')
                ->get();
            $goals = $this->goalAll($goals);
            $tree = self::buildTree($goals->toArray(), $goalId);
            $pTree = $goals->where('id', $goalId)->first();
            if ($pTree) {

                $pTree->children = $tree;
                return ['tree' => [$pTree], 'goals' => $goals];
            }
            return ['tree' => [], 'goals' => $goals];
        }
    }

    public function buildTree(array $elements, $parentId = 0)
    {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = self::buildTree($elements, $element['value']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    public function mapProcess($goals)
    {

        $goal_ids = $goals->pluck("id");
        $task = Task::whereIn("goal_id", $goal_ids);
        $task_ids = $task->pluck("id");

        $todolist = Todolist::selectRaw("task_id, count(*) as count")
            ->whereIn("task_id", $task_ids)
            ->where("status", "done")
            ->groupBy("task_id")
            ->get();
        return $goals = $goals->map(function ($g) use ($todolist) {
            if ($g->status == "done") {
                $g->process = 100;
                return $g;
            }
            $task = Task::where("goal_id", $g->id)->first();
            if ($task) {
                $todo = $todolist->where("task_id", $task->id)->first();

                if ($todo) {
                    $star_day = Carbon::parse($g->start_day);
                    $end_day = Carbon::parse($g->end_day);
                    $day = $end_day->diffInDays($star_day) + 1;

                    if ($day > 0) {
                        $count = $todo->count;
                        $process = $count / $day * 100;
                        $process = $process > 100 ? 100 : $process;
                        $g->process = $process;
                    }
                } else {
                    $g->process = 0;
                }

            } else {
                $g->process = 0;
            }
            return @$g;
        });
    }

    public function mapProcessSingle($goal)
    {
        if ($goal->status == "done") {
            $goal->process = 100;
            return $goal;
        }
        $task = Task::where("goal_id", $goal->id)
            ->first();
        if ($task) {
            $todolist = Todolist::where('task_id', $task->id)
                ->where("status", "done")
                ->where("user_id", Auth::id())
                ->get();
            $count = count($todolist) == 0 ? 1 : count($todolist);

            $star_day = Carbon::parse($goal->start_day);
            $end_day = Carbon::parse($goal->end_day);

            $day = $end_day->diffInDays($star_day) + 1;
            //process
            $process = round($count / $day * 100, 2);
            $goal->process = $process;
            return $goal;
        }
        $goal->process = 0;
        return $goal;
    }

    public function myGoalsAchieve()
    {
        $general_ids = Achieve::where("user_invite_id", Auth::id())
            ->where("status", "accept")
            ->get()
            ->pluck("general_id")
            ->toArray();
        $general_ids = array_unique($general_ids, 0);
        $goal_ids = GeneralInfo::whereIn("id", $general_ids)
            ->get()
            ->pluck("goal_id")
            ->toArray();
        $goal_ids = array_unique($goal_ids, 0);
        $goals = Goal::whereIn('id', $goal_ids)->get();
        $goals = $this->mapProcess($goals);
        $goals = $this->generalinfo_repository
            ->setType('goal')
            ->get($goals);
        return $goals;
    }

    public function countGoals($args)
    {
        $goals = Goal::where('user_id', Auth::id());
        switch ($args['parent_id']) {
            case 'all':
                break;
            case 'root':
                $goals = $goals
                    ->where('parent_id', null)
                    ->orderBy('is_pined', 'desc')
                    ->orderBy('updated_at', 'desc');
                break;
            default:
                $goals = $goals
                    ->where('parent_id', $args['parent_id'])
                    ->orderBy('id', 'desc');
                break;
        }
        $count = $goals->selectRaw("status, count(*) as count")
            ->groupBy("status")
            ->get()
            ->pluck('count', 'status')
            ->toArray();
        $count["todo"] += @$count[""];
        $count["todo"] += @$count["null"];
        $count = array_diff_key($count, array_flip(["", "null"]));

        $temp = [];
        foreach ($count as $key => $c) {
            $t["status"] = $key;
            $t["number"] = $c;
            array_push($temp, $t);
        }
        return $temp;
    }

    public function myGoals($args)
    {
        $goals = Goal::where('user_id', Auth::id());
        switch ($args['parent_id']) {
            case 'all':
                break;
            case 'root':
                $goals = $goals
                    ->whereNull('parent_id')
                    ->orderBy('is_pined', 'desc')
                    ->orderBy('updated_at', 'desc');
                break;
            default:
                $goals = $goals
                    ->where('parent_id', $args['parent_id'])
                    ->orderBy('id', 'desc');
                break;
        }
        $goals = $goals->get();
        $goals = $this->generalinfo_repository
            ->setType('goal')
            ->get($goals);
//        dd($goals->toArray());
        if ($args["parent_id"] == "root") {
            $root_ids = $goals->pluck("id");
            return $this->goalAll($goals, "root", $root_ids);
        }
        return $this->goalAll($goals);
    }

    public function goalAll($goals, $type = "all", $root_ids = [])
    {
        $parentGoal_ids = $goals->whereNotNull("parent_id")
            ->pluck("parent_id")
            ->toArray();
        $parentGoal_ids = array_unique($parentGoal_ids);
        //find goal not have child not in parentGoal_ids
        $goalNochild = $goals->whereNotIn("id", $parentGoal_ids);
//        $goalNochild = $this->mapProcess($goalNochild);
        $goalNochild = $goalNochild->map(function ($goal) {
            $goal = $this->mapProcessSingle($goal);
            return $goal;
        });

        //find parent goal and caculate process on goalNoChild
        $goalParent = $goals->whereIn("id", $parentGoal_ids);
        foreach ($goalParent as $goal) {
            $goal = $this->getProcessChild($goal, $goalNochild);
        }
        $goalParent = $goalParent->merge($goalNochild);
        if ($type == "root") {
            return $goalParent->whereIn("id", $root_ids);
        }

        return $goalParent;
    }

    public function getProcessChild($goal, $goalNochild)
    {
        if ($goal->status == "done") {
            $goal->process = 100;
            return $goal;
        }
        $childs = $goalNochild->where("parent_id", $goal->id);
        $process_s = $childs->pluck("process");
        $process = 0;
        foreach ($process_s as $p) {
            $process += $p;
        }
        $count = count($process_s) == 0 ? 1 : count($process_s);
        $process = $process / $count;


        if ($process == 100) {
            $arr["status"] = "done";
            Goal::where("id", $goal->id)
                ->update($arr);
            $goal->status = 'done';
        }

        $goal->process = round($process, 2);

        return $goal;
    }

//    public function buildTree1(&$list, array $elements, $parentId = 0)
//    {
//        $branch = array();
//        foreach ($elements as $element) {
//            if ($element['parent_id'] == $parentId) {
//                $children = self::buildTree1($list, $elements, $element['value']);
//                if ($children) {
//                    $element['children'] = $children;
//                }
//                $list[] =
//                $branch[] = $element;
//            }
//        }
//        return $branch;
//    }


    public function calculatorProcessTodolist($goal)
    {
        if (Goal::where('parent_id', $goal->id)->first()) {
            return $this->calculatorProcessUpdate($goal);
        }
        $progress = $goal->status == 'done' ? 100 : 0;
        $task = Task::where('goal_id', $goal->id)
            ->first();
        if ($task) {
            $startDay = Carbon::parse($goal->start_day);
            $endDay = Carbon::parse($goal->end_day);
            $todolist = Todolist::where('task_id', $task->id)
                ->whereBetween('checked_at', [$startDay->format('Y-m-d'), $endDay->format('Y-m-d')])
                ->where('status', 'done')
                ->where('user_id', Auth::id())
                ->get();
            $count = $todolist->count();
            $day = $endDay->diffInDays($startDay) + 1;
            $progress = round(($count / $day) * 100, 2);
        }
        $goal->progress = $progress;
        $goal->status = $progress >= 100 ? 'done' : 'todo';
        Goal::where('id', $goal->id)
            ->update(['progress' => $goal->progress, 'status' => $goal->status]);
        return $this->calculatorProcessUpdate($goal);
    }

    public function calculatorProcessUpdate($goal)
    {
//        $goalId = $goal->id;
//        $status = $goal->status;
        $goals = Goal::selectRaw('*, id as value')
            ->orderBy('id', 'desc')
            ->where('user_id', Auth::id())
            ->get()
            ->toArray();
        $goals = $this->buildTree($goals);

        $listP = [];
        $itemP = [];
        $listU = [];
        array_walk_recursive($goals, function ($val, $key) use (&$listP, &$itemP, &$listU) {
            if ($key == 'id' || $key == 'parent_id' || $key == 'status') {
                $itemP[$key] = $val;
            }
            if ($key == 'status') {
                $listP[$itemP['parent_id']][] = $itemP['status'];
                $listU[$itemP['parent_id']][] = $itemP;
            }
        });
//        dd($listP);
        if ($goal->status == 'done') {
            foreach ((@$listU[$goal->id] ?? []) as $item) {
                Goal::where('id', $item['id'])
                    ->update([
                        'progress' => 100,
                        'status'   => 'done',
                    ]);
            }
        }

        foreach ($listP as $goalId => $item) {
            $done = @array_count_values($item)['done'] ?? 0;
            $progress = ($done / count($item)) * 100;
            $status = $progress == 100 ? 'done' : 'todo';
            if ($goal->id == $goalId) {
                $goal->progress = $progress;
                $goal->status = $status;
            }
            Goal::where('id', $goalId)
                ->update(['progress' => $progress, 'status' => $status]);
        }
        return $goal;
    }

}
