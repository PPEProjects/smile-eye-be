<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use GraphQL\Error\Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;

use function PHPSTORM_META\type;

class LetterAttachmentController extends Controller
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
   
    public function store($type, Request $request){
        $file = $request->file('file');
        $checkTail = $file->getClientOriginalExtension();
        $fileRootName = $file->getClientOriginalName();
        $filePath = storage_path() . "/app/public";
        if ($type == "example-hiragana") {
            $folder = 'hiragana';
            if ($checkTail == "mp3"){
                $folder = $folder."/audio";
            }
        }
        else if($type == "example-katakana"){
            $folder = 'katakana';
            if ($checkTail == "mp3"){
                $folder = $folder."/audio";
            }
        }
        else if ($type == "audio" && $checkTail == "mp3") {
            $folder = 'a';
        }
        else{
            return response()->json([
                "status"=>false
            ]);
        }
        $file->move($filePath . '/Letter/'. $folder, $fileRootName);
        return response()->json([
            "status"=>true
        ]);
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
            'file_size' => filesize($filePath)
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
