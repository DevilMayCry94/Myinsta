<?php
/**
 * Created by PhpStorm.
 * User: dokuen
 * Date: 29.09.15
 * Time: 11:52
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class PostTable
{
    protected $tableGateWay;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateWay = $tableGateway;
    }

    public function save(Post $post)
    {
        $data = array(
            'idUser' => $post->idUser,
            'urlImg' => $post->urlImg,
            'comment' => $post->comment,
        );
        $this->tableGateWay->insert($data);
    }

    public function show($id)
    {
        $rowset = $this->tableGateWay->select(['idUser' => $id]);
        return $rowset;

    }

    public function CountComment($idImg)
    {
        return '1';
    }
}