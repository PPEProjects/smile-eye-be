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
        $japaneseGoal = JapaneseGoal::where('id',$args["id"])->get();
        $attachmentIds1 = $japaneseGoal->pluck('attachments_1')->flatten();
        $attachmentIds2 = $japaneseGoal->pluck('attachments_2')->flatten();
        $attachmentIds3 = $japaneseGoal->pluck('attachments_3')->flatten();
        $attachmentIds = $attachmentIds1->merge($attachmentIds2)->merge($attachmentIds3);
       $attachments = Attachment::WhereIn('id', $attachmentIds)->get()->keyBy('id');
       $attachments = $attachments->map(function ($attachment){
           [$thumb,$file] = $this->attachment_service->getThumbFile($attachment->file_type,$attachment->file);
           $attachment->thumb = $thumb;
           $attachment->file = $file;
           return $attachment;
       });
       $temps = $japaneseGoal->map(function ($jpGoal) use ($attachments, $attachmentIds1, $attachmentIds2, $attachmentIds3){
           $jpGoal->attachments_1 = $attachmentIds1->map(function ($id) use ($attachments){
              return $attachments[$id];
          });
           $jpGoal->attachments_2 = $attachmentIds2->map(function ($id) use ($attachments){
               return $attachments[$id];
           });
           $jpGoal->attachments_3 = $attachmentIds3->map(function ($id) use ($attachments){
               return $attachments[$id];
           });
            return @$jpGoal;
       });

        $japaneseGoal = $japaneseGoal->first();
        return $japaneseGoal;
    }

}
