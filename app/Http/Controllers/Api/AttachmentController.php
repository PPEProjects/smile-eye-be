<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;
use Exception;

class AttachmentController extends Controller
{

    private $attachment_service;

    public function __construct(
        AttachmentService $AttachmentService
    ) {
        $this->attachment_service = $AttachmentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $file = $request->file('file');
        $audio_base64 = base64_encode($request->audio_base64);
       
        if(base64_encode(base64_decode($request->audio_base64, true)) === $request->audio_base64){
            $isset_base64 = true;
        }
        else $isset_base64 = false;

        if (!$isset_base64)
        {
            $fileRootName = $file->getClientOriginalName();
            try {
                $type = preg_replace('#\/.*?$#mis', '', $file->getMimeType());
            } catch (Exception $e) {
                $type = preg_replace('#\/.*?$#mis', '', $file->getClientMimeType());
            }
        } 
        else $type = 'audio';
        
      
       

        

        switch ($type) {
            case 'image':
                $path = $file->getRealPath();
                $fileName = 'media/images/' . date('Y-m-d') . '-' . time() . '-' . rand() . '-' . Auth::id() . '.wepb';
                $filePath = storage_path() . "/app/public/$fileName";
                $isConverted = $this->attachment_service->jcphp01_generate_webp_image($path, $filePath);
                $this->attachment_service->saveThumbImage($path, $filePath);
                if ($isConverted) {
                    $attachment = array_merge($request->all(), [
                        'user_id'   => Auth::id(),
                        'file'      => $fileName,
                        'file_type' => 'image',
                        'file_name' => $fileRootName,
                        'file_size' => $file->getSize()
                    ]);
                    $create = Attachment::create($attachment);
                    if ($create) {
                        [$thumb, $file] = $this->attachment_service->getThumbFile($create->file_type, $create->file);

//                        $image =  asset('storage/' .$create->file);
                        $create->file = $file;
                        $create->thumb = $thumb;
                    }
                }
                return response()->json(@$create);
                break;
            case 'video':
                $tail = $file->getClientOriginalExtension();
                $fileName = date('Y-m-d') . '-' . time() . '-' . rand() . '-' . Auth::id() . '.' . $tail;
                $filePath = storage_path() . "/app/public/media/videos";
                $attachment = array_merge($request->all(), [
                    'user_id'   => Auth::id(),
                    'file'      => 'media/videos/' . $fileName,
                    'file_type' => 'video',
                    'file_name' => $fileRootName,
                    'file_size' => $file->getSize()
                ]);
                $create = Attachment::create($attachment);
                if ($create) {
                    $file->move($filePath, $fileName);
                    [$thumb, $file] = $this->attachment_service->getThumbFile($create->file_type, $create->file);
                    $create->thumb = $thumb;
                    $create->file = $file;
                }
                return response()->json(@$create);
                break;
                case 'audio':
                    $filePath = storage_path() . "/app/public/media/audios";
                    if ($isset_base64){
                       $tail = "wav";
                       $size = (int) (strlen(rtrim($audio_base64, '=')) * 3 / 4);
                       
                    } else {
                        $tail = $file->getClientOriginalExtension();
                        $size = $file->getSize();
                    }
                    $fileName = date('Y-m-d') . '-' . time() . '-' . rand() . '-' . Auth::id() . '.' . $tail;    
                   if(!isset($fileRootName)){
                       $fileRootName = $fileName;
                   }
                    $attachment = array_merge($request->all(), [
                        'user_id'   => Auth::id(),
                        'file'      => 'media/audios/'.$fileName,
                        'file_type' => 'audio',
                        'file_name' => $fileRootName,
                        'file_size' => $size
                    ]);
                    $create = Attachment::create($attachment);
                    if ($create) {
                        if($isset_base64){
                            file_put_contents($filePath.'/'.$fileName, base64_decode($request->audio_base64));
                        }else $file->move($filePath, $fileName);
                        
                        [$thumb, $file] = $this->attachment_service->getThumbFile($create->file_type, $create->file);
                        $create->thumb = $thumb;
                        $create->file = $file;
                    }
                    return response()->json(@$create);
                    break;
            case 'application' :
                $tail = (string)$file->getClientOriginalExtension();
                $create = $this->saveFile($file, $request, $tail);
                return response()->json($create);
                break;
            case 'text':
                $tail = (string)$file->getClientOriginalExtension();
                $create = $this->saveFile($file, $request, $tail);
                return response()->json($create);
                break;
        }
    }

    /**
     *
     */
    public function saveFile(
        $file,
        $request,
        $tail
    ) {
        $filePath = storage_path() . "/app/public/application/";
        $fileType = "";
        $folder = "";
        if ($tail == 'doc' || $tail == 'docx') {
            $filePath = $filePath . 'doc';
            $folder = "doc";
            $fileType = "doc";
        }
        if ($tail == 'json' || $tail == 'txt') {
            $fileType = "doc";
            $filePath = $filePath . 'doc';
            $folder = "doc";
        }
        if ($tail == 'pptx') {
            $fileType = "pptx";
            $filePath = $filePath . 'pptx';
            $folder = "pptx";
        }
        if ($tail == 'exe') {
            $fileType = "exe";
            $filePath = $filePath . 'exe';
            $folder = "exe";
        }
        if ($tail == 'xlsx' || $tail == 'xls') {
            $fileType = "excel";
            $filePath = $filePath . 'excel';
            $folder = "excel";
        }
        if ($tail == 'rar' || $tail == 'zip') {
            $fileType = "rar";
            $filePath = $filePath . 'rar';
            $folder = "rar";
        }
        $fileName = 'application/' . $folder . '/' . date('Y-m-d') . '-' . time() . '-' . rand() . '-' . Auth::id() . '.' . $tail;
        $file->move($filePath, $fileName);

        $attachment = array_merge($request->all(), [
            'user_id'   => Auth::id(),
            'file'      => $fileName,
            'file_type' => $fileType,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize()
        ]);
        $create = Attachment::create($attachment);

        if ($create) {
            [$thumb, $file] = $this->attachment_service->getThumbFile($create->file_type, $create->file);
            $create->file = $file;
            $create->thumb = $thumb;
        }
        return @$create;
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Attachment $attachment
     * @return \Illuminate\Http\Response
     */
    public
    function show(
        Attachment $attachment
    ) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Attachment $attachment
     * @return \Illuminate\Http\Response
     */
    public
    function edit(
        Attachment $attachment
    ) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Attachment $attachment
     * @return \Illuminate\Http\Response
     */
    public
    function update(
        Request $request,
        Attachment $attachment
    ) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Attachment $attachment
     * @return \Illuminate\Http\Response
     */
    public
    function destroy(
        Attachment $attachment
    ) {
        //
    }
}
