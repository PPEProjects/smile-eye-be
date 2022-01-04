<?php
namespace App\GraphQL\Queries ;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Repositories\PaymentRepository;

class PaymentQueries
{
    private $payment_repository;
    public function __construct(PaymentRepository $payment_repository)
    {
        $this->payment_repository = $payment_repository;
    }

    public function payments($_,array $args)
    {
        return Payment::all();
    }

    public function myPayments($_,array $args)
    {
        return $this->payment_repository->myPayments($args);
    }
    public function detailPayments($_,array $args)
    {
        return $this->payment_repository->detailPayments($args);
    }
    public function summaryPayments($_, array $args){
        return $this->payment_repository->summaryPayments($args);
    }
    public function totalIncome($_,array $args){
        $args = array_diff_key($args, array_flip(["directive"]));
        return $this->payment_repository->totalIncome($args);
    }
    public function paymentsList($_, array $args){
        return $this->payment_repository->paymentsList($args);
    }
}