<?php

namespace App\GraphQL\Queries;


use App\Models\GoalPrevNext;

class GoalPrevNextQueries
{
    public function detailGoalPrevNext($_, array $args)
    {
        $goalId = $args['goal_id'];
        $rootId = $args['root_id'];
        $goal = GoalPrevNext::where('root_id', $rootId)
            ->first()
            ->toArray();
        foreach ($goal['jp_ids'] as $key => $jp) {
            if ($jp['goal_id'] == $goalId) {
                $current = $jp;
                $prev = @$goal['jp_ids'][$key - 1];
                $next = @$goal['jp_ids'][$key + 1];
            }
        }
        return @[
            'root_id' => $rootId,
            'current' => $current,
            'prev' => $prev,
            'next' => $next,
        ];
    }
}