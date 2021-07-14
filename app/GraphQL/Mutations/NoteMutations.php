<?php
namespace App\GraphQL\Mutations;
use App\Repositories\NoteRepository;

class NoteMutations
{
    private $note_repository;

    public function __construct(NoteRepository $note_repository)
    {
        $this->note_repository = $note_repository;
    }

    public function createNote($_, array $args)
    {
        return $this->note_repository->createNote($args);
    }


    public function updateNote($_, array $args)
    {
        return $this->note_repository->updateNote($args);

    }


    public function deleteNote($_, array $args)
    {
        return $this->note_repository->deleteNote($args);
    }

}
