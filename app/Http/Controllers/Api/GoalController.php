<?php


namespace App\Http\Controllers\Api;


use App\Models\Goal;

class GoalController
{

    protected function _buildTree(array $elements, $parentId = 0)
    {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = self::_buildTree($elements, $element['TaskId']);
                if ($children) {
                    $element['SubTasks'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    public function ganttChart($user_id)
    {
        $goals = Goal::selectRaw('id as TaskId, name as TaskName, parent_id, start_day as StartDate,
            end_day as EndDate, progress as Progress')
            ->where("user_id", "=", $user_id)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        $goalTree = self::_buildTree($goals, null);
        $goalTree = ['Items' => $goalTree, 'Count' => count($goals)];
        return $goalTree;
    }

}