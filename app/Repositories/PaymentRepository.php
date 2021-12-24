<?php

namespace App\Repositories;

use App\Models\Goal;
use App\Models\GoalTemplate;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentRepository
{
    public function createPayment($args)
    {
        $args["user_id"] = Auth::id();
        return Payment::create($args);
    }
     public function updateTrial($goal_id, $addUserId){
         $status = ['accept', 'paused', 'confirmed', "paidConfirmed", "done"];
         $payment= Payment::where('goal_id', $goal_id)
             ->where("add_user_id", $addUserId)
             ->first();
         $template = GoalTemplate::where('goal_id', $goal_id)
             ->whereIn('status', $status)
             ->first();
         if(!isset($payment) && isset($template)){
             $data = [
                 'goal_id' => $goal_id,
                 'user_id' => Auth::id(),
                 'add_user_id' => $addUserId,
                 'status' => 'trial'
             ];
             $createPayment = Payment::create($data);
             return $createPayment;
         }
         return ;
     }
    public function updatePayment($args)
    {
        return tap(Payment::findOrFail($args["id"]))->update($args);
    }
    public function deletePayment($args)
    {
        return Payment::where("id",$args["id"])->forceDelete();
    }
    public function myPayments($args)
    {
        return Payment::where('user_id', Auth::id())->get();
    }
    public function detailPayments($args)
    {
        if(isset($args['user_id'])){
            $type = @$args['type'] ?? "all";
            $payment = Payment::Where('add_user_id', $args['user_id'])
                                ->OrderBy('id','DESC')
                                ->get();
            if($type != 'all'){
                $payment = $payment->where('type', $type);
            }
        }
        else if(isset($args['goal_id'])){
            $payment = Payment::Where('add_user_id', Auth::id())
                                ->where('goal_id', $args['goal_id'])
                                ->first();
        }
        else {
            $payment = Payment::find($args['id']);
        }

        if(isset($args['status'])){
            $payment = $payment->where('status', 'LIKE', $args['status']);
        }      
        if(isset($payment))
        {
            $getIdGoals = $payment->pluck('goal_id'); 
            $checkGoal = Goal::whereIn('id', @$getIdGoals ?? [])
                            ->get()
                            ->pluck('id'); 
            $payment = $payment->whereIn('goal_id', @$checkGoal ?? []);
        }
        return $payment;
    }
   public function summaryPayments($args)
   {
      $status = @$args['status'] ?? 'all';
      switch ($status) {
          case 'all':
              $payments = Payment::selectRaw("*, COUNT(add_user_id) as `number_member`")
                                    ->groupByRaw('goal_id, DATE(created_at)')
                                    ->get();
              break;
          
          default:
              $payments = Payment::selectRaw("*, COUNT(add_user_id) as `number_member`")
                                    ->where('status', 'LIKE', '%'.$status.'%')
                                    ->groupByRaw('goal_id, DATE(created_at)')
                                    ->get();
              break;
      }
      $getIdGoals = $payments->pluck('goal_id');
      $checkGoals = Goal::whereIn('id', @$getIdGoals ?? [])
                            ->get()
                            ->pluck('id');
        $payments = $payments->whereIn('goal_id', @$checkGoals ?? []);
      return $payments->sortBy('created_at');
   }
}
