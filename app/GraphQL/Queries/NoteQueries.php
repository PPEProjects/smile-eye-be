<?php
namespace App\GraphQL\Queries ;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Repositories\NoteRepository;

class NoteQueries
{
    private $note_repository;
    public function __construct(NoteRepository $note_repository)
    {
        $this->note_repository = $note_repository;
    }

    public function myNotes($_,array $args)
    {
        return $this->note_repository->myNotes($args);
    }
    public function detailNotes($_,array $args)
    {
        return $this->note_repository->detailNotes($args);
    }
}