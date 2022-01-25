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
        $status = ['paidConfirmed', 'done', 'Confirmed', 'pause'];
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

       $moneyAdmin = 0;
       $moneyOwner = 0;
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

                $moneyAdmin = $moneyAdmin + $total[$id]["total_admin"];
                $moneyOwner = $moneyOwner + $total[$id]["total_owner"];

                
                $moneyTotal["sum_admin_income"]["admin"] = $moneyAdmin;
                $moneyTotal["sum_admin_income"]["owner"] = $moneyOwner;

                $moneyTotal["sum_total_income"]["admin"] = $moneyAdmin;
                $moneyTotal["sum_total_income"]["owner"] = $moneyOwner;

                
                $moneyTotal["sum_owner_income"]["admin"] = $moneyAdmin;
                $moneyTotal["sum_owner_income"]["owner"] = $moneyOwner;
                $totalIncome[$id] = array_merge($getSum, $totalIncome[$id]);

            }
            $test = [];
            foreach($getDate as $date){
                $totalAdminInDate = 0;
                $totalOwnerInDate = 0;
                $sumDate = $payments->where('date', $date)
                                        ->sum('money');
                $test[$date]['sum'] = $sumDate;
                $moneyTotal["sum_total_income"]["date".$date] =  $sumDate;
                foreach(@$checkGoals ?? [] as $id)
                {
                    $moneyDay = $payments->where('goal_id', $id)
                                            ->where('date', $date)
                                            ->sum('money');

                    $totalAdminInDate = $totalAdminInDate  + ( $moneyDay * $percent[$id]["admin_percent"]);
                    $totalOwnerInDate = $totalOwnerInDate + ( $moneyDay * $percent[$id]["owner_percent"]);
                   $test[$date][$id] = $totalOwnerInDate/100;

                    $moneyTotal["sum_admin_income"]["date".$date] =   $totalAdminInDate/100;

                    $moneyTotal["sum_owner_income"]["date".$date] =  $totalOwnerInDate/100;
                }
            }
        $otherGoal = [];
        if(@$args['all_template'])
        {
            $otherTemplate = GoalTemplate::whereIn('status', $status)
                                            ->whereNotIn('goal_id', $checkGoals)
                                            ->get();
            foreach ($otherTemplate as $template) {
                if(isset($template->goal))
                {
                    $ownerPercent = @$template->goal->owner_percent ?? 0;
                    $adminPercent = 100 - intval($ownerPercent);
                    $otherGoal[$template->goal_id] = [
                        'name' =>  $template->goal->name,
                        "owner_percent" => intval($ownerPercent),
                        "admin_percent" => $adminPercent,
                        "owner" => 0,
                        "admin" => 0,
                        "sum" => 0
                    ];
                    foreach ($getDate as $date){
                        $otherGoal[$template->goal_id]['date'.$date] = 0; 
                    }
                }
            }   
        }            
        $totalIncome = array_merge($totalIncome, $otherGoal, $moneyTotal);
       return  array_values($totalIncome);
   }

   public function paymentsList($args){
        $status = ['paidConfirmed', 'done', 'Confirmed'];
        $day = @$args['day'] ?? "0";
        $month = @$args['month'] ?? "0";
        $year = @$args['year'] ?? "0";
        $date = $year."-".$month."-".$day;
        if($day == "0" && $month == "0" && $year == "0"){
            $date = date('Y-m-d');
        }
        $payments = Payment::whereIn('status', $status)
                            ->whereRaw("DATE(updated_at) = '".$date."'")
                            ->orderBy('updated_at', 'ASC')
                            ->get();
        $getIdGoals = $payments->pluck('goal_id');
        $checkIssetGoals = Goal::whereIn('id', @$getIdGoals ?? []);
        if (isset($args["name_goal"]) || @$args["name_goal"] !=""){
            $nameGoal = $args["name_goal"];
            $checkIssetGoals = $checkIssetGoals->where('name', 'LIKE', "%".$nameGoal."%");
        }
        $checkIssetGoals = $checkIssetGoals->get();
        $payments = $payments->whereIn('goal_id', @$checkIssetGoals->pluck('id') ?? []);
        $sum = [];
        $sumMoney = $payments->sum('money');
     
        $i = 1;
        $children = [];
        $all = [];
        $key = 0;
        foreach($payments as $pay){
            $inforGoal = [
                'key' => $key,
                'id' => $pay->id,
                'name' => $pay->add_user->name,
                'goal' => $pay->goal->name,
                'status' => @$pay->status,
                'type' => @$pay->type,
                'money' => @$pay->money ?? '0',
                'note' => @$pay->note,
                'attachments' => $pay->attachments,
                'date' => $pay->updated_at
            ];
            $children[$pay->goal_id][] = $inforGoal;
            $all[] = $inforGoal;
            $key++;
        }
        $sum[] = [ 'key'=> 0,
                    'name' => 'Sum', 
                    'money' => $sumMoney, 
                    'date' => $date,
                    'children' => $all
                ];

        foreach($checkIssetGoals as $goal){
            $money = $payments->where('goal_id', $goal->id)->sum('money');
            $sum[] = [  'key'=> $i,
                        'name' => 'Sum '.$goal->name, 
                        'money' => $money, 
                        'date' => $date, 
                        'children' => $children[$goal->id]
                    ];
            $i++;
        }
    
        return $sum;
   }
}
