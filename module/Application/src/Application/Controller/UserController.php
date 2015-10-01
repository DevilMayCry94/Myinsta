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
        $form = new UploadForm('upload-form');
        $postTable = $this->getServiceLocator()->get('PostTable');
        $userTable = $this->getServiceLocator()->get('UserTable');
        if(!isset($_GET['id'])) {
            $id = $userTable->getBy('id', ['email' => $_SESSION['userEmail']]);
        } else {
            $id = $_GET['id'];
        }
        $posts = $postTable->show($id);
        $user = $userTable->getUser($id);
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

        return new ViewModel(['form' => $form, 'posts' => $posts,'user' => $user]);
    }

    public function newAction()
    {
        if(isset($_SESSION['user'])) {
            $inf = $this->getServiceLocator()->get('getAuthService')->getStorage();
            $viewModel = new ViewModel(['inf' => $inf]);
            return $viewModel;
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
        $data = ['idImg' => $_POST['idImg'], 'src' => $_POST['src']];
        $result = new JsonModel(array(
            'response' => $data
        ));
        return $result;
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

    public function EditAction()
    {
        return new ViewModel();
    }

    public function ProfileAction()
    {
        $link = explode('/',$_SERVER['REQUEST_URI']);
        $usertable = $this->getServiceLocator()->get('UserTable');
        $id = $usertable->getBy('id',['link' => $link[1]]);
        $inf = $usertable->getUser($id);
        $postTable = $this->getServiceLocator()->get('PostTable');
        $posts = $postTable->show($id);
        $form = new UploadForm('upload-form');
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
        return new ViewModel(['form' => $form, 'user' => $inf, 'posts' => $posts]);
    }




}