<?php
namespace Application\Controller;

use Application\Model\ActionUser;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class AjaxController extends AbstractActionController
{
    public function indexAction()
    {
        $data = $this->getComment();
        return new JsonModel($data);

    }

    public function SearchAction()
    {
        $usertable = $this->getServiceLocator()->get('UserTable');
        $obj = $usertable->searchPeople($_POST['search']);
        foreach($obj as $o)
        {
            $data[] = array('id' => $o->id, 'name' => $o->name, 'ava' => $o->ava);
        }
        $result = new JsonModel($data);
        return $result;
    }

    public function myCommentAction()
    {
        $this->saveComment();
        $post = $this->getServiceLocator()->get('PostTable');
        $usertable = $this->getServiceLocator()->get('UserTable');
        $img = $post->getPostbysrc($_POST['src']);
        $user = $usertable->getUser($img->idUser);
        $data = array(
            'idUser' => $user->id,
            'name' => $user->name,
            'comment' => $_POST['textcomment']
        );
        return new JsonModel($data);
    }

    public function loadCommentAction()
    {
        $sql = $this->getServiceLocator()->get('Sql');
        $select = $sql->select();
        $select->from(array('a' => 'action'))  // base table
        ->join(array('u' => 'user'),     // join table with alias
            'a.idUser = u.id');
        $select->where(['a.idPost' => $_POST['idPost'],
            new \Zend\Db\Sql\Predicate\IsNotNull('a.comment')]);
        $select->order('a.created');
        $statement = $sql->prepareStatementForSqlObject($select);
        $rows = $statement->execute();
        if($rows->count() > $_POST['count'])
        {

        }
        return new JsonModel(["yeah" => $rows->count(),"WOAT" => $_POST['count']]);
    }

    public function followAction()
    {
        $sql = $this->getServiceLocator()->get('Sql');
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getBy('id',['email'=>$_SESSION['userEmail']]);
        if($_POST['btnValue'] == 'Follow')
        {
            $insert = $sql->insert();
            $insert->into('follow');
            $insert->values(array('idUser' => $user, 'idFollower' => $_POST['idFollower']));
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
            return new JsonModel(['value'=>'Following']);
        } else {
            $delete = $sql->delete();
            $delete->from('follow');
            $delete->where(array('idUser' => $user, 'idFollower' => $_POST['idFollower']));
            $statement = $sql->prepareStatementForSqlObject($delete);
            $statement->execute();
            return new JsonModel(['value' => 'Follow']);
        }
    }

    public function saveComment()
    {
        $postTable = $this->getServiceLocator()->get('PostTable');
        $usertable = $this->getServiceLocator()->get('UserTable');
        $img = $postTable->getPostbysrc($_POST['src']);
        $actionTable = $this->getServiceLocator()->get('ActionTable');
        $data = array(
            'idPost'    => $img->id,
            'idUser'    => $usertable->getBy('id', ['email' => $_SESSION['userEmail']]),
            'comment'   => $_POST['textcomment']
        );
        $action = new ActionUser();
        $action->exchangeArray($data);
        $actionTable->save($action);

    }

    public function getComment()
    {
        $post = $this->getServiceLocator()->get('PostTable');
        $usertable = $this->getServiceLocator()->get('UserTable');
        $img = $post->getPostbysrc($_POST['src']);
        $user = $usertable->getUser($img->idUser);
        $data = array(
            'idImg'             => $_POST['idImg'],
            'src'               => $_POST['src'],
            'own_comment'       => $img->comment,
            'inf_user'          => $user,
        );
        $actionTable = $this->getServiceLocator()->get('ActionTable');
        $idPost = $img->id;
        if($actionTable->getAction($idPost)) {

            $sql = $this->getServiceLocator()->get('Sql');
            $select = $sql->select();
            $select->from(array('a' => 'action'))// base table
            ->join(array('u' => 'user'),     // join table with alias
                'a.idUser = u.id');
            $select->where(['a.idPost' => $idPost,
                new \Zend\Db\Sql\Predicate\IsNotNull('a.comment')]);
            $statement = $sql->prepareStatementForSqlObject($select);
            $rows = $statement->execute();
            foreach ($rows as $r) {
                $comment[] = $r;
            }
            $inf_post = array(
                'countLike' => $actionTable->countLike($idPost),
                'countComment' => $actionTable->countComment($idPost),
            );
            $data['inf_post'] = $inf_post;
            $data['comment'] = $comment;
        }
        return $data;
    }


}