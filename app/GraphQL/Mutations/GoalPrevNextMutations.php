<?php
namespace App\GraphQL\Mutations;

use App\Models\GoalPrevNext;

class GoalPrevNextMutations
{
    public function upsertGoalPrevNext($_, array $args)
    {
        $goalPrevNext = GoalPrevNext::updateOrCreate(
            ['root_id' => @$args['root_id']],
            $args
        );
        return $goalPrevNext;
    }
}
