<?php

namespace App\Repositories;

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
           $payment = Payment::Where('add_user_id', $args['user_id'])
                                ->OrderBy('id','DESC')
                                ->get();
        }
        else {
            $payment = Payment::find($args['id']);
        }
        return $payment;
    }
   
}
