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
   public function totalIncome($args){
        $status = ['paidConfirmed', 'done', 'Confirmed'];
       $payments = Payment::selectRaw("id, add_user_id, money, status, goal_id")
                            ->whereIn("status", $status);
        if(isset($args) && @$args != [])
        {
            $month = @$args['month'] ?? "";
            $year = @$args['year'] ?? date('Y');
            switch ($month){
                case "":
                   $payments = $payments->selectRaw(" DATE_FORMAT(updated_at, '%Y-%m') as `date`")
                                        ->whereRaw("YEAR(updated_at) = '".$year."'");
                    break;
                default:
                    if(intval($month) < 10){ $month = "0".$month; }
                   $payments = $payments->selectRaw(" DATE(updated_at) as `date`")
                                        ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m') = '".$year."-".$month."'");
            }
            if($payments->get()->toArray() == []) return ;
        }
        else{
            $payments = $payments->selectRaw("DATE(updated_at) as `date`");
        }
        $payments = $payments->get();
       $checkGoals = [];
       $goals = [];
       foreach ($payments as $payment){
           if (isset($payment->goal) && !isset($goals[$payment->goal->id])){
               $checkGoals[] = $payment->goal_id;
               $goals[$payment->goal->id] = $payment->goal;
           }
       }
       $payments = $payments->whereIn('goal_id', $checkGoals);
        $getDate = $payments->pluck('date')->toArray();
        $total = [];
        $totalIncome = [];
        $moneyTotal = [];
        $percent = [];
        $getDate = array_unique($getDate);
       foreach ($payments as $payment) {
           $ownerPercent = @$payment->goal->owner_percent ?? 0;
           $adminPercent = 100 - intval($ownerPercent);
           $percent[$payment->goal->id]["owner_percent"] = intval($ownerPercent);
           $percent[$payment->goal->id]["admin_percent"] = $adminPercent;
       }
       $moneyTotal["sum_total_income"]["name"] = "SUM total income";
       $moneyTotal["sum_admin_income"]["name"] = "SUM admin income";
       $moneyTotal["sum_owner_income"]["name"] = "SUM owner income";
       $sumAll = $payments->sum('money');
       $moneyTotal["sum_total_income"]["sum"] = $sumAll;
       $moneyTotal["sum_admin_income"]["sum"] = $sumAll;
       $moneyTotal["sum_owner_income"]["sum"] = $sumAll;
       foreach (@$checkGoals ?? [] as $id){
                 $totalOwner = 0;
                 $totalAdmin = 0;
                 $sumOwner = 0;
                 $sumAdmin = 0;
           foreach ($getDate as $date){
                   $moneyDay = $payments->where('goal_id', $id)
                                        ->where('date', $date)
                                        ->sum('money');

                   $totalAdmin = $totalAdmin + ( $moneyDay * $percent[$id]["admin_percent"]);
                   $total[$id]["total_admin"] = $totalAdmin/100;
                   $totalOwner = $totalOwner + ( $moneyDay * $percent[$id]["owner_percent"]);
                   $total[$id]["total_owner"] = $totalOwner/100;

                    $sumAdmin = $sumAdmin + ( $moneyDay * $percent[$id]["admin_percent"]);
                    $total[$date]["total_admin"] = $totalAdmin/100;
                    $sumOwner = $sumOwner + ( $moneyDay * $percent[$id]["owner_percent"]);
                    $total[$date]["total_owner"] = $totalOwner/100;

                    $sumDate = $payments->where('date', $date)
                                            ->sum('money');

                    $moneyTotal["sum_total_income"]["admin"] = $total[$date]["total_admin"];
                    $moneyTotal["sum_total_income"]["owner"] = $total[$date]["total_owner"];
                    $moneyTotal["sum_total_income"]["date".$date] =  $sumDate;

                    $moneyTotal["sum_admin_income"]["admin"] = $total[$date]["total_admin"];
                    $moneyTotal["sum_admin_income"]["owner"] = $total[$date]["total_owner"];
                    $moneyTotal["sum_admin_income"]["date".$date] =  $sumDate;

                    $moneyTotal["sum_owner_income"]["admin"] = $total[$date]["total_admin"];
                    $moneyTotal["sum_owner_income"]["owner"] = $total[$date]["total_owner"];
                    $moneyTotal["sum_owner_income"]["date".$date] =  $sumDate;


                $totalIncome[$id]["date".$date] =  $moneyDay;
                }
                     $sumGoal = $payments->where('goal_id', $id)
                                            ->sum('money');
                $getSum = [
                        'name'=> $goals[$id]->name,
                        "owner_percent" => $percent[$id]["owner_percent"],
                        "admin_percent" => $percent[$id]["admin_percent"],
                        "owner" => $total[$id]["total_owner"],
                        "admin" => $total[$id]["total_admin"],
                        "sum" => $sumGoal,
                ];
                $totalIncome[$id] = array_merge($getSum, $totalIncome[$id]);

            }

        $totalIncome = array_merge($totalIncome, $moneyTotal);
       return  array_values($totalIncome);
   }
}
