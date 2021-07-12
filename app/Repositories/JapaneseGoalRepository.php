<?php

namespace App\Repositories;


use App\Models\Attachment;
use App\Models\JapaneseGoal;
use ppeCore\dvtinh\Services\AttachmentService;

class JapaneseGoalRepository
{
    public function __construct(AttachmentService $attachment_service)
    {
        $this->attachment_service = $attachment_service;
    }
    public function createJapaneseGoal($args){
        $japanese = JapaneseGoal::create($args);
        return $japanese;
    }
    public function updateJapaneseGoal($args){
        return tap(JapaneseGoal::findOrFail($args["id"]))
            ->update($args);
    }
    public function deletejapaneseGoal($args){
        $args = array_diff_key($args, array_flip(['directive']));
        $delete = JapaneseGoal::find($args['id']);
        return $delete->delete();
    }
    public function detailJapaneseGoal($args){
        $temps = JapaneseGoal::where('id',$args["id"])->get();
        $fIds1 = $temps->pluck('attachments_1')->flatten();
        $fIds2 = $temps->pluck('attachments_2')->flatten();
        $fIds3 = $temps->pluck('attachments_3')->flatten();
        $fID = $fIds1->merge($fIds2)->merge($fIds3);
       $attachments = Attachment::WhereIn('id', $fID)->get()->keyBy('id');
       $temps = $temps->map(function ($temp) use ($attachments, $fIds1, $fIds2, $fIds3){
          $temp->attachments_1 = $fIds1->map(function ($id) use ($attachments){
             $data = @$this->getAttachments($attachments[$id]);
              $result['id'] = @$data->id;
              $result['thumb'] = @$data->thumb;
              $result["file"] = @$data->file;
              return $result;
          });
           $temp->attachments_2 = $fIds2->map(function ($id) use ($attachments){
               $data = @$this->getAttachments($attachments[$id]);
               $result['id'] = @$data->id;
               $result['thumb'] = @$data->thumb;
               $result["file"] = @$data->file;
               return $result;
           });
           $temp->attachments_3 = $fIds3->map(function ($id) use ($attachments){
               $data = @$this->getAttachments($attachments[$id]);
               $result['id'] = @$data->id;
               $result['thumb'] = @$data->thumb;
               $result["file"] = @$data->file;
               return $result;
           });
            return @$temp;
       });


        return $temps->first();
    }
    public function getAttachments($attachments){
        [$thumb,$file] = $this->attachment_service->getThumbFile($attachments->file_type,$attachments->file);
        $attachments->thumb = $thumb;
        $attachments->file = $file;
        return $attachments;
    }
}
