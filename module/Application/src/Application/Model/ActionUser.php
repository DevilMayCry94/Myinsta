<?php
namespace Application\Model;

class ActionUser
{
    public $id;
    public $idPost;
    public $idUser;
    public $comment;
    public $like;

    function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->idPost = (isset($data['idPost'])) ? $data['idPost'] : null;
        $this->idUser = (isset($data['idUser'])) ? $data['idUser'] : null;
        $this->comment = (isset($data['comment'])) ? $data['comment'] : null;
        $this->like = (isset($data['like'])) ? $data['like'] : null;
    }
}
