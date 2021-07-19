<?php

namespace App\GraphQL\Mutations;
use App\Models\GeneralInfo;
use App\Models\Goal;
use App\Models\JapaneseGoal;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\JapaneseGoalRepository;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class JapaneseGoalMutations{
    private $japanese_goal_repository;
    private $generalinfo_repository;
    public function __construct(JapaneseGoalRepository $japanese_goal_repository, GeneralInfoRepository $generalinfo_repository)
    {
        $this->japanese_goal_repository = $japanese_goal_repository;
        $this->generalinfo_repository = $generalinfo_repository;
    }

    public function createJapaneseGoal($_,array $args){
        if (!isset($args['type'])){
            throw new Error('You must input type');
        }
        if (!isset($args['name_goal'])){
            throw new Error('You must input name goal');
        }
        if (isset($args['name_goal'])) {
            $dataGoal = ['name' => $args['name_goal'], 'user_id' => Auth::id()];
            if (isset($args['parent_id'])){
                $dataGoal['parent_id'] =  $args['parent_id'];
            }
            $goal = Goal::create($dataGoal);
            $this->generalinfo_repository
                ->setType('goal')
                ->upsert(array_merge($goal->toArray(), $args))
                ->findByTypeId($goal->id);
            $args['goal_id'] = $goal->id;
        }
       return $this->japanese_goal_repository->createJapaneseGoal($args);
    }
    public function updateJapaneseGoal($_,array $args){
        $args = array_diff_key($args, array_flip(['type']));
        return $this->japanese_goal_repository->updateJapaneseGoal($args);
    }
    public function deletejapaneseGoal($_,array $args){
        return $this->japanese_goal_repository->deletejapaneseGoal($args);
    }

    public function createBasketCard($_,array $args){
        $args["user_id"] = Auth::id();
        return JapaneseGoal::create($args);
    }

    public function updateBasketCard($_,array $args){
        return tap(JapaneseGoal::findOrFail($args["id"]))
            ->update($args);
    }
    public function deleteBasketCard($_,array $args){
        return JapaneseGoal::find($args["id"])
            ->delete();
    }
    public function createFlashCard($_, array $args){
        $args["user_id"] = Auth::id();
        return JapaneseGoal::create($args);
    }
    public function updateFlashCard($_,array $args){
        return tap(JapaneseGoal::findOrFail($args["id"]))
            ->update($args);
    }
    public function deleteFlashCard($_,array $args){
        return JapaneseGoal::find($args["id"])
            ->delete();
    }

}