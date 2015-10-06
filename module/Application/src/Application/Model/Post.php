<?php
/**
 * Created by PhpStorm.
 * User: dokuen
 * Date: 29.09.15
 * Time: 11:49
 */

namespace Application\Model;

class Post
{
    public $id_post;
    public $idUser;
    public $comment;
    public $urlImg;

    function exchangeArray($data)
    {
        $this->id_post = isset($data['id_post']) ? $data['id_post'] : null;
        $this->idUser = isset($data['idUser']) ? $data['idUser'] : null;
        $this->comment = isset($data['comment']) ? $data['comment'] : null;
        $this->urlImg = isset($data['urlImg']) ? $data['urlImg'] : null;
    }
}