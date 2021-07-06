<?php

namespace App\Repositories;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Goal;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use App\Repositories\GeneralInfoRepository;

class AttachmentRepository
{
    private $generalinfo_repository;

    public function __construct(GeneralInfoRepository $generalinfo_repository)
    {
        $this->generalinfo_repository = $generalinfo_repository;
    }

    public function updateByCol($col, $val, $args)
    {
        if (!empty($args['media_id'])) {
            $val = array_fill_keys([$col], $val);
            $nullVal = array_fill_keys([$col], null);
            Attachment::where($val)->update($nullVal);
            Attachment::where('id', $args['media_id'])->update($val);
        }
    }

    public function updateAttachment($args)
    {

        $fileType = $args['file_type'];
        $fileName = $args['file'];
        $path = asset('storage' . $this->getPath($fileType, $fileName));
        $args = array_diff_key($args, array_flip(['directive']));
        $args['file'] = $path;
        $update = tap(Attachment::findOrFail($args["id"]))
            ->update($args);
        return $update;
    }

    public function getPath($type, $name)
    {
        //media/images/2021-06-09-1623211801-1850027332-1.wepb
//        $filePath = "/app/public/application/";

        switch ($type) {
            case 'image':
                return '/media/images/' . $name;
                break;
            case 'aplication':
                $tail = explode('.', $name)[1];
                return self::checkAndGet($tail, $name);
                break;
            case 'text' :
                return '/application/txt/' . $name;
                break;
            case 'video' :
                return '/application/mp4/' . $name;
                break;
        }
    }

    public function checkAndGet($tail, $name)
    {
        $path = '/application/';
        switch ($tail) {
            case 'doc' || 'docx' :
                return $path . 'doc/' . $name;
                break;
            case 'xlsx' :
                return $path . 'excel/' . $name;
                break;
            case 'rar':
                return $path  . 'rar/' . $name;
                break;
            case 'zip':
                return $path . 'rar/' . $name;
                break;
            case 'exe':
                return $path. 'exe/' . $name;
                break;
        }
    }
    public function beforDelete($args){
        $arr = [];
        $id_attachment = $args["id"];
        $user = Auth::user();
        if ($user->avatar_attachment_id == $id_attachment){
            $arr["avatar"]["id"] = $id_attachment;
        }

        if ($user->background_attachment_id == $id_attachment){
            $arr["background"]["id"] = $id_attachment;
        }

        //check in task
        $tasks = Task::orderBy('id', 'desc')
            ->where("user_id",Auth::id())
            ->get();
        $tasks = $this->generalinfo_repository
            ->setType('task')
            ->get($tasks)
            ->keyBy("id");

        foreach ($tasks as $key=>$t){
            $attachment_ids = @$t->general_info->attachment_ids ;
            foreach ($attachment_ids as $id){
                if ($id == $id_attachment){
                    $arr["task"]["id"] = $key;
                    $arr["task"]["name"] = $t->name;
                }
            }
        }

        //check in goal
        $goals = Goal::orderBy('id', 'desc')
            ->where("user_id",Auth::id())
            ->get();
        $goals = $this->generalinfo_repository
            ->setType('goal')
            ->get($goals)
            ->keyBy("id");
        foreach ($goals as $key=>$g){
            $attachment_ids = @$g->general_info->attachment_ids ;
            foreach ($attachment_ids as $id){
                if ($id == $id_attachment){
                    $arr["goal"]["id"] = $key;
                    $arr["goal"]["name"] = $g->name;
                }
            }
        }
        // check in comment

        $comments = Comment::where("user_id",Auth::id())
            ->get()
            ->keyBy("id");
        foreach ($comments as $key => $cmt){
            $attachment_ids = @$cmt->attachment_ids ;
            foreach ($attachment_ids as $id){
                if ($id == $id_attachment){
                    $arr["comment"]["id"] = $key;
                    $arr["comment"]["name"] = $cmt->content;
                }
            }
        }
        return $arr;
    }
}
