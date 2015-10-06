<?php
namespace Application\Controller;

use Application\Form\UploadForm;
use Application\Model\Post;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
class UserController extends AbstractActionController
{
    public function indexAction()
    {
        $isFollow = false;
        $form = new UploadForm('upload-form');
        $postTable = $this->getServiceLocator()->get('PostTable');
        $userTable = $this->getServiceLocator()->get('UserTable');
        if(!isset($_GET['id'])) {
            $id = $userTable->getBy('id', ['email' => $_SESSION['userEmail']]);
        } else {
            $id = $_GET['id'];
            $isFollow = $this->isFollowing($id);
        }
        $posts = $postTable->show($id);
        $user = $userTable->getUser($id);
        $inf['postCount'] = $postTable->getCountPost($id);
        $inf['followersCount'] = $this->countFollower($id);
        $inf['followingCount'] = $this->countFollowing($id);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($post);
            if ($form->isValid()) {
                $dataForm = $form->getData();
                $data = array(
                    'urlImg'  => $dataForm['image-file']["tmp_name"],
                    'comment' => $this->request->getPost()->post,
                    );
                $this->savePost($data);

                return $this->redirect()->toRoute(null, ['controller' =>'user','action' => 'index']);
            }
        }
        //print_r($user);die;

        return new ViewModel(['form' => $form, 'posts' => $posts,'user' => $user, 'isFollow' => $isFollow, 'inf' => $inf]);
    }

    public function newAction()
    {
        if(isset($_SESSION['user'])) {
            $type = (isset($_GET['type'])) ? $_GET['type'] : 'all-news';
            $userTable = $this->getServiceLocator()->get('UserTable');
            $sql = $this->getServiceLocator()->get('Sql');
            $news = $userTable->news($sql, $type);
            $action = $this->getServiceLocator()->get('ActionTable');
            if($news) {
                foreach ($news as $n) {
                    $n['countLike'] = $action->countLike($n['id_post']);
                    $n['countComment'] = $action->countComment($n['id_post']);
                    $data[] = $n;
                }

                $viewModel = new ViewModel(['news' => $data]);
                return $viewModel;
            } else {
                return new ViewModel();
            }
        } else {
            $this->redirect()->toRoute('home');
        }
    }

    public function confirmregAction()
    {
        $confirm = $this->getServiceLocator()->get('UserTable');
        $confirm->activation();
        return new ViewModel();
    }

    public function LoginAction()
    {
        $form = $this->getServiceLocator()->get('LoginForm');
        $post = $this->request->getPost();
        $log = true;
        if (isset($post->submitlog)) {
            $this->getServiceLocator()->get('getAuthService')->getAdapter()->setIdentity(
                $post->emailLogin)->setCredential($post->passLogin);
            $login = $this->getServiceLocator()->get('getAuthService')->authenticate();
            if ($login->isValid()) {
                $_SESSION['user'] = $post->emailLogin;
                $this->redirect()->toRoute(Null, array(
                    'controller' => 'user',
                    'action' => 'new',
                ));
                $log = true;
            } else {
                $log = false;
            }
        }
        return new ViewModel(['log' => $log, 'form' => $form]);
    }

    public  function LogoutAction()
    {
        session_destroy();
        $this->redirect()->toRoute('home');
    }

    public function savePost($data)
    {
        $iduser = $this->getServiceLocator()->get('UserTable');
        $data['idUser'] = $iduser->getBy('id',['email' => $_SESSION['userEmail']]);
        $post = new Post();
        $post->exchangeArray($data);
        $userTable = $this->getServiceLocator()->get('PostTable');
        $userTable->save($post);
        return true;

    }

    public function ShowimgAction()
    {
        $post = $this->getServiceLocator()->get('PostTable');
        $usertable = $this->getServiceLocator()->get('UserTable');
        $img = $post->getPostbysrc($_POST['src']);
        $user = $usertable->getUser($img->idUser);
        $actionTable = $this->getServiceLocator()->get('ActionTable');
        $idPost = $img->id;
        $comment = $actionTable->getComment($idPost);
        $idUsersComment = $actionTable->getAllIdUserComment($idPost);
        foreach($idUsersComment as $id)
        {
            $nameUsers[] = $usertable->getUser($id)->name;
        }
        $inf_post = array(
            'countLike'         => $actionTable->countLike($idPost),
            'countComment'      => $actionTable->countComment($idPost),
        );
        $data = array(
            'idImg'             => $_POST['idImg'],
            'src'               => $_POST['src'],
            'own_comment'       => $img->comment,
            'inf_user'          => $user,
            'inf_post'          => $inf_post,
            'comment'           => $comment
            );
        $result = new JsonModel($data);
        return $result;
    }

    public function EditAction()
    {
        return new ViewModel();
    }

    public function isFollowing($id)
    {
        $sql = $this->getServiceLocator()->get('Sql');
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getBy('id',['email'=>$_SESSION['userEmail']]);
        $select = $sql->select();
        $select->from('follow');
        $select->where(['idUser' => $user, 'idFollower' => $id]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute()->current();
        if($row)
        {
            return true;
        } else {
            return false;
        }
    }

    public function countFollower($idUser)
    {
        $sql = $this->getServiceLocator()->get('Sql');
        $select = $sql->select();
        $select->from('follow');
        $select->where(['idFollower'=>$idUser]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        return $row->count();
    }

    public function countFollowing($idUser)
    {
        $sql = $this->getServiceLocator()->get('Sql');
        $select = $sql->select();
        $select->from('follow');
        $select->where(['idUser'=>$idUser]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        return $row->count();
    }




}