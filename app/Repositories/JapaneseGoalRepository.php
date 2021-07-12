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
        $attachmentIds1 = $temps->pluck('attachments_1')->flatten();
        $attachmentIds2 = $temps->pluck('attachments_2')->flatten();
        $attachmentIds3 = $temps->pluck('attachments_3')->flatten();
        $attachmentIds = $attachmentIds1->merge($attachmentIds2)->merge($attachmentIds3);
       $attachments = Attachment::WhereIn('id', $attachmentIds)->get()->keyBy('id');
       $temps = $temps->map(function ($temp) use ($attachments, $attachmentIds1, $attachmentIds2, $attachmentIds3){
          $temp->attachments_1 = $attachmentIds1->map(function ($id) use ($attachments){
             $attachment = @$this->getAttachments($attachments[$id]);
              $result['id'] = @$attachment->id;
              $result['thumb'] = @$attachment->thumb;
              $result["file"] = @$attachment->file;
              return $result;
          });
           $temp->attachments_2 = $attachmentIds2->map(function ($id) use ($attachments){
               $attachment = @$this->getAttachments($attachments[$id]);
               $result['id'] = @$attachment->id;
               $result['thumb'] = @$attachment->thumb;
               $result["file"] = @$attachment->file;
               return $result;
           });
           $temp->attachments_3 = $attachmentIds3->map(function ($id) use ($attachments){
               $attachment = @$this->getAttachments($attachments[$id]);
               $result['id'] = @$attachment->id;
               $result['thumb'] = @$attachment->thumb;
               $result["file"] = @$attachment->file;
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
