<?php
namespace App\GraphQL\Queries ;

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

    public function myPayments($_,array $args)
    {
        return $this->payment_repository->myPayments($args);
    }
    public function detailPayments($_,array $args)
    {
        return $this->payment_repository->detailPayments($args);
    }
    
}