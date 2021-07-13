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

    public function getJapaneseGoal($args){
        if (isset($args['id'])) {
            $japaneseGoal = JapaneseGoal::where('id', $args["id"])->get()->keyBy('id');
        }
        if(isset($args["type"])){
            $japaneseGoal = JapaneseGoal::where('type', $args["type"])->get()->keyBy('id');
        }
        $japaneseGoal = $japaneseGoal->map(function ($jpGoal) use ($japaneseGoal){
           $attachmentIds_1 = @$japaneseGoal[$jpGoal->id]->attachments_1;
           $attachmentIds_2 = @$japaneseGoal[$jpGoal->id]->attachments_2;
           $attachmentIds_3 = @$japaneseGoal[$jpGoal->id]->attachments_3;

           $jpGoal->attachments_1 = $this->getAttachments($attachmentIds_1);
           $jpGoal->attachments_2 = $this->getAttachments($attachmentIds_2);
           $jpGoal->attachments_3 = $this->getAttachments($attachmentIds_3);

            return @$jpGoal;
        });
        return $japaneseGoal->sortByDESC('id');
    }
    public function getAttachments($ids){
        $attachments = Attachment::WhereIn('id', $ids)->get();
        $attachments = $attachments->map(function ($attachment){
            [$thumb,$file] = $this->attachment_service->getThumbFile($attachment->file_type,$attachment->file);
            $getAttachment = collect();
            $getAttachment['id'] = $attachment->id;
            $getAttachment['file'] = $file;
            $getAttachment['file_type'] = $attachment->file_type;
            $getAttachment['thumb'] = $thumb;
            return $getAttachment;
        });
        return $attachments;
    }
    public function detailJapaneseGoal($args){

       $japaneseGoal = $this->getJapaneseGoal($args);
       return $japaneseGoal->first();
    }
    public function searchByTypeJapaneseGoal($args){

        return $this->getJapaneseGoal($args);
    }
}
