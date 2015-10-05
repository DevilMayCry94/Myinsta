<?php
/**
 * Created by PhpStorm.
 * User: dokuen
 * Date: 29.09.15
 * Time: 11:52
 */

namespace Application\Model;

use Zend\Db\Sql\Select;
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
        $rowset = $this->tableGateWay->select(function (Select $select) use ($id) {
            $select->where->equalTo('idUser', $id);
            $select->order('id DESC')->limit(9);
        });
        return $rowset;

    }

    public function getPostbysrc($src)
    {
        $rowset = $this->tableGateWay->select(function (Select $select) use ($src) {
            $select->where->like('urlImg','%'.$src.'%');
        });
        return $rowset->current();
    }

}