<?php
namespace App\GraphQL\Mutations;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Auth;

class PaymentMutations
{
    private $payment_repository;

    public function __construct(PaymentRepository $payment_repository)
    {
        $this->payment_repository = $payment_repository;
    }

    public function upsertPayment($_, array $args)
    {
        $args['user_id'] = Auth::id();
        $payment = Payment::updateOrCreate(
            [
                'add_user_id' => @$args['add_user_id'],
                'goal_id' => @$args['goal_id'],
            ],
            $args
        );
        return $payment;
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
