<?php

namespace App\Repositories;
use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
            $notes = User::find($id)->notes;
            return $notes->where("checked_at",$args["checked_at"]);
        }else {
            $id = Auth::id();
            return User::find($id)->notes;
        }
    }
    public function detailNotes($args)
    {
        $id = $args["id"];
        return Note::find($id);
    }
}
