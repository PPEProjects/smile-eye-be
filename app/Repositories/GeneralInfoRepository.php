<?php

namespace App\Repositories;

use App\Models\Achieve;
use App\Models\GeneralInfo;
use App\Models\PublishInfo;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Models\User;
use ppeCore\dvtinh\Services\AttachmentService;

class GeneralInfoRepository
{
    private $attachment_service;

    public function __construct(
        AttachmentService $attachment_service
    ) {
        $this->attachment_service = $attachment_service;
    }

    protected $type;

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }


    public function upsert($payload)
    {
        switch ($this->type) {
            case 'goal':
                $data = array_merge($payload['general_info'] ?? [],
                    ['task_id' => null, 'todolist_id' => null],
                    ['user_id' => Auth::id()]
                );
                if(isset($payload['id']) && GeneralInfo::where('goal_id', $payload['id'])->exists()){
                    unset($data['user_id']);
                }
                GeneralInfo::updateOrCreate(
                    ['goal_id' => $payload['id']],
                    $data
                );
                break;
            case 'task':
                $data = array_merge($payload['general_info'] ?? [],
                    ['goal_id' => null, 'todolist_id' => null],
                    ['user_id' => Auth::id()]
                );
                GeneralInfo::updateOrCreate(
                    ['task_id' => $payload['id']],
                    $data
                );
                break;
            case 'todolist':
                $data = array_merge($payload['general_info'] ?? [],
                    ['goal_id' => null, 'task_id' => null],
                    ['user_id' => Auth::id()]
                );
                GeneralInfo::updateOrCreate(
                    ['todolist_id' => $payload['id']],
                    $data
                );
                break;
        }
        return $this;
    }

    public function findByTypeId($typeId, $select = '*')
    {
        $generalInfo = GeneralInfo::selectRaw($select);
        switch ($this->type) {
            case 'goal':
                $generalInfo = $generalInfo->where('goal_id', $typeId);
                break;
            case 'task':
                $generalInfo = $generalInfo->where('task_id', $typeId);
                break;
            case 'todolist':
                $generalInfo = $generalInfo->where('todolist_id', $typeId);
                break;
        }
        $generalInfo = $generalInfo->first();
        $generalInfo = $this->attachment_service->mappingAttachment($generalInfo);
        $achieves = Achieve::where('general_id', $generalInfo->id)
            ->get();
        $generalInfo->achieves = $achieves;
        $publishs = PublishInfo::where('general_id', $generalInfo->id)
            ->get();
        $generalInfo->publishs = $publishs;
        return $generalInfo;
    }

    public function find($id, $select = '*')
    {
        return GeneralInfo::selectRaw($select)
            ->where('id', $id)
            ->first();
    }

    public function get($data, $select = '*')
    {
        $ids = $data->pluck('id')->toArray();
        $generalInfo = GeneralInfo::selectRaw($select);
        switch ($this->type) {
            case 'goal':
                $generalInfo = $generalInfo->whereIn('goal_id', $ids);
                break;
            case 'task':
                $generalInfo = $generalInfo->whereIn('task_id', $ids);
                break;
            case 'todolist':
                $generalInfo = $generalInfo->whereIn('todolist_id', $ids);
                break;
        }
        $generalInfos = $generalInfo->get();
        $generalInfos = $generalInfos->map(function ($general){
            $achieves = Achieve::where('general_id', $general->id)
                ->get();
            $achieves = $achieves->map(function ($achieve){
                $user = User::where("id",$achieve->user_invite_id)->first();

                if ($user) {
                    $user = $this->attachment_service->mappingAvatarBackgroud($user);
                    $achieve->user_invite = $user;
                }
                return $achieve;
            });
            $general->achieves = $achieves;
            $publishs = PublishInfo::where('general_id', $general->id)
                ->get();
            $general->publishs = $publishs;

            return $general;
        });

        $generalInfos = $this->attachment_service->mappingAttachments($generalInfos);
        $data = $data->map(function ($item) use ($generalInfos) {
            $item->general_info = $generalInfos
                ->where($this->type . '_id', $item->id)
                ->first();
            return $item;
        });
        return $data;
    }

    public function updateGeneralInfo($args)
    {
        $args['user_id'] = Auth::id();
        $update = tap(GeneralInfo::findOrFail($args["id"]))
            ->update($args);
        return $update;
    }
}
