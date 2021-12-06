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
        $payment = [];
        $data = array_diff_key($args, array_flip(['goal_id']));
        foreach($args['goal_id'] as $goal_id)
        { 
            $detailPayment = Payment::where('goal_id', $goal_id)
                                    ->where('add_user_id', $data['add_user_id'])
                                    ->first();
            if(isset($detailPayment->attachments) && isset($data['attachments'])){
                $data['attachments'] = array_merge($data['attachments'],$detailPayment->attachments);
            }
            $data['goal_id'] = $goal_id; 
                $payment[] = Payment::updateOrCreate(
                [
                    'add_user_id' => @$data['add_user_id'],
                    'goal_id' => $goal_id,
                ],
                $data
            );
        }
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
