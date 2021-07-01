<?php

namespace App\Repositories;

use App\Models\Attachment;

class AttachmentRepository
{
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
                return $path . '/aplication/' . 'doc/' . $name;
                break;
            case 'xlsx' :
                return $path . '/aplication/' . 'excel/' . $name;
                break;
            case 'rar':
                return $path . '/aplication/' . 'rar/' . $name;
                break;
            case 'zip':
                return $path . '/aplication/' . 'rar/' . $name;
                break;
            case 'exe':
                return $path . '/aplication/' . 'exe/' . $name;
                break;
        }
    }
}
