<?php
namespace App\GraphQL\Mutations;
use App\Repositories\PaymentRepository;

class PaymentMutations
{
    private $payment_repository;

    public function __construct(PaymentRepository $payment_repository)
    {
        $this->payment_repository = $payment_repository;
    }

    public function createPayment($_, array $args)
    {
        return $this->payment_repository->createPayment($args);
    }


    public function updatePayment($_, array $args)
    {
        return $this->payment_repository->updatePayment($args);

    }


    public function deletePayment($_, array $args)
    {
        return $this->payment_repository->deletePayment($args);
    }

}
