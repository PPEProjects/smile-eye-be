<?php
namespace App\GraphQL\Mutations;


use App\Models\Attachment;
use App\Models\UserAvatar;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserAvatarRepository;
use mysql_xdevapi\Exception;
use phpDocumentor\Reflection\Types\Boolean;
use PhpParser\Node\Scalar\String_;

class UserAvatarMutations
{
    private $userAvatarRepository ;
    public function __construct(UserAvatarRepository $avatarRepository)
    {
        $this->userAvatarRepository = $avatarRepository;
    }
    public function createUserAvatar($_, array $args):UserAvatar
    {
        $args['user_id'] = Auth::id();
        $userAvatar = UserAvatar::create($args);
        if(isset($args["attachment_id"] ))
        {
            $attachment_id = $args["attachment_id"];
            $attachment = Attachment::find($attachment_id) ;
            $attachment->user_image_id = $userAvatar->id ;
            $attachment->save();
        }
        return $userAvatar;
    }


    public function updateUserAvatar($_, array $args):UserAvatar
    {
        return $this->userAvatarRepository->updateUserAvatar($args) ;
    }


    public function deleteUserAvatar($_, array $args):bool
    {
        $id = UserAvatar::where('user_id',Auth::id())->first()->toArray();
        $user_avatar = UserAvatar::find($id['id']);

        return $user_avatar->delete();
    }

}
