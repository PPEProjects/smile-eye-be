<?php
namespace App\GraphQL\Mutations;




use App\Models\GeneralInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Repositories\GeneralInfoRepository;

class GeneralInfoMutations
{
    private $generalinfo_repository ;
    public function __construct(GeneralInfoRepository $generalInfoRepository)
    {
        $this->generalinfo_repository = $generalInfoRepository;
    }

    public function createGeneralInfo($_, array $args)
    {

        $args['action_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $args['action_at']);
        $args['user_id'] = Auth::id();
        return GeneralInfo::create($args);
    }
    public function deleteGeneralInfo($_, array $args):bool
    {
        $generalInfo = GeneralInfo::find($args['id']);

        return $generalInfo->delete();
    }
    public function updateGeneralInfo($_, array $args)
    {
        return $this->generalinfo_repository->updateGeneralInfo($args);
    }
}
