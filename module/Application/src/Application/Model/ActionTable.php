<?php
namespace Application\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

class ActionTable
{
    protected $tableGateWay;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateWay = $tableGateway;
    }

    public function save(ActionUser $action)
    {
        $data = array(
            'idPost'    => $action->idPost,
            'idUser'    => $action->idUser,
            'comment'   => $action->comment,
            'like'      => $action->like
        );
        $this->tableGateWay->insert($data);
    }

    public function getAction($idPost)
    {
        return $resultSet = $this->tableGateWay->select(['idPost' => $idPost])->current();
    }

    public function countLike($idPost)
    {
        $resultSet = $this->tableGateWay->select(['idPost' => $idPost, 'like' => 1]);
        return $resultSet->count();
    }

    public function countComment($idPost)
    {
        $resultSet = $this->tableGateWay->select(['idPost' => $idPost,
            new \Zend\Db\Sql\Predicate\IsNotNull('comment')]);
        return $resultSet->count();
    }

    public function getComment($idPost)
    {
        $resultSet = $this->tableGateWay->select(['idPost' => $idPost,
            new \Zend\Db\Sql\Predicate\IsNotNull('comment')]);
        foreach($resultSet as $r)
        {
            $comment[] = $r;
        }
        return $comment;
    }

    public function isLike($idPost, $idUser)
    {
        $rowset = $this->tableGateWay->select(array('idPost' => $idPost, 'idUser' => $idUser));
        if($rowset)
        {
            return true;
        } else {
            return false;
        }
    }

    public function getAllIdUserComment($idPost)
    {
        $resultSet = $this->tableGateWay->select(['idPost' => $idPost,
            new \Zend\Db\Sql\Predicate\IsNotNull('comment')]);
        foreach($resultSet as $r)
        {
            $idusers[] = $r->idUser;
        }
        return $idusers;
    }


}