<?php

namespace App\Repositories;
use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NoteRepository
{
    public function createNote($args)
    {
        $args["user_id"] = Auth::id();
        return Note::create($args);
    }


    public function updateNote($args)
    {
        return tap(Note::findOrFail($args["id"]))->update($args);
    }


    public function deleteNote($args)
    {
        return Note::where("id",$args["id"])->delete();
    }
    public function myNotes($args)
    {
        if (isset($args["checked_at"])){
            $id = Auth::id();
            $date = date_format($args["checked_at"],"Y-m-d");
            $notes = Note::where("user_id",$id)
                ->where("checked_at","like",$date."%")
                ->orderBy("id","DESC")
                ->get();
            return $notes;
        }else {
            $id = Auth::id();
            return Note::where("user_id",$id)
                ->orderBy("id","DESC")
                ->get();
        }
    }
    public function detailNotes($args)
    {
        $id = $args["id"];
        return Note::find($id);
    }
    public function notesGroupByDate()
    {
       $myNotes = Note::selectRaw("*, DATE(checked_at) as day")->where("user_id", Auth::id());
        $notesByDate = $myNotes->OrderBy('id','DESC')->get()->groupBy('day')->toArray();
        $getDay = $myNotes->groupBy("day")->get();
        $notes = $getDay->map(function ($note) use ($notesByDate){
           $data = collect();
           $data['day'] = @$note->day;
           $data['list'] = $notesByDate[@$note->day];
           return $data;
      });
       return $notes->sortByDESC('day');

    }
}
