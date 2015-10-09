<?php
namespace Application\Controller;

use Application\Form\UploadForm;
use Application\Model\DirectoryManager;
use Application\Model\Post;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
class UserController extends AbstractActionController
{
    public function indexAction()
    {
        if(!isset($_SESSION['user'])) {
            $this->redirect()->toRoute('home');
        }
        $isFollow = false;
        $postTable = $this->getServiceLocator()->get('PostTable');
        $userTable = $this->getServiceLocator()->get('UserTable');
        $actionTable = $this->getServiceLocator()->get('ActionTable');
        if(!isset($_GET['id'])) {
            $id = $userTable->getBy('id', ['email' => $_SESSION['userEmail']]);
        } else {
            $id = $_GET['id'];
            $isFollow = $this->isFollowing($id);
        }
        $posts = $postTable->show($id);
        $i_posts = $postTable->show($id);

        foreach($posts as $p)
        {
            $data = get_object_vars($p);
            $data['countComment'] = $actionTable->countComment($p->id_post);
            $data['countLike'] = $actionTable->countLike($p->id_post);
            $data['isLike'] = $actionTable->isLike($p->id_post,$userTable->getBy('id', ['email' => $_SESSION['userEmail']]));
            $inf_posts[] = $data;

        }

        $user = $userTable->getUser($id);
        $inf['postCount'] = $postTable->getCountPost($id);
        $inf['followersCount'] = $this->countFollower($id);
        $inf['followingCount'] = $this->countFollowing($id);
        return new ViewModel(['posts' => $inf_posts,'user' => $user, 'isFollow' => $isFollow, 'inf' => $inf]);

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
                    $n['isLike'] = $action->isLike($n['id_post'],$userTable->getBy('id',['email'=>$_SESSION['userEmail']]));
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

    public function videoAction()
    {
        return new ViewModel();
    }

    public function videoProcessAction()
    {
        $directory  = new DirectoryManager();
        if (isset($_POST)) {

            /*************************************
             *** GET PICTURE ***
             ************************************/
            //Variables
            $path               = (string) filter_input(INPUT_GET, 'path');
            $extension          = (string) filter_input(INPUT_GET, 'extension');
            $type               = (string) filter_input(INPUT_GET, 'type');

            //Set session and directory
            $id             = 'video';
            $nameVideo             = 'user' . $this->getServiceLocator()->get('UserTable')->getBy('id',
                ['email' => $_SESSION['userEmail']]).'_' . uniqid();

            //"/media/Temp/1234/1234.yyy"
            $simplePath             = $path . $id . DIRECTORY_SEPARATOR . $nameVideo . '.' . $extension;

            //"C:/xxxx/media/Temp/1234"
            $basePath                = (string) $_SERVER['DOCUMENT_ROOT'] . $path . $id;

            //"C:/xxxx/media/Temp/1234/1234.yyy"
            $baseFilename            = (string) $basePath . DIRECTORY_SEPARATOR . $nameVideo . '.' . $extension;

            //Search inside "C:/xxxx/media/Temp/1234" directory
            $directory->setDirectoryIterator($basePath);

            //Get media
            $media = file_get_contents('php://input');

            $firstArray     = array('\\', '/', '%5C');
            $secondArray    = array('/', '/', '/');

            //Format strings
            $baseFilename   = str_replace($firstArray, $secondArray, $baseFilename);
            $simplePath     = str_replace($firstArray, $secondArray, $simplePath);

            //If a content exists, we delete and replace it
            $directory->delete($id . '.' . $extension);

            //Create file and return status
            file_put_contents($baseFilename, $media);

            //Return media url inside media directory
            echo $simplePath;
            die;
        }
    }

    public function saveVideoAction()
    {
        if(isset($_POST)) {
            $path = $_POST['path'];
            $file_name = '/img/' . explode('/', $path)[3];
            chmod(BASE_PATH . $path,0777);
            copy(BASE_PATH . $path, BASE_PATH . $file_name);
            unlink(BASE_PATH . $path);
            $iduser = $this->getServiceLocator()->get('UserTable');
            $data['idUser'] = $iduser->getBy('id',['email' => $_SESSION['userEmail']]);
            $data['urlImg'] = $file_name;
            $post = new Post();
            $post->exchangeArray($data);
            $userTable = $this->getServiceLocator()->get('PostTable');
            $userTable->save($post);
            return new JsonModel(['response' => 'success']);
        }
    }




}