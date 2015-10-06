<?php
namespace Application\Model;

class ActionUser
{
    public $idAction;
    public $idPost;
    public $idUser;
    public $comment;
    public $like;

    function exchangeArray($data)
    {
        $this->idAction = (isset($data['idAction'])) ? $data['idAction'] : null;
        $this->idPost = (isset($data['idPost'])) ? $data['idPost'] : null;
        $this->idUser = (isset($data['idUser'])) ? $data['idUser'] : null;
        $this->comment = (isset($data['comment'])) ? $data['comment'] : null;
        $this->like = (isset($data['like'])) ? $data['like'] : null;
    }
}
