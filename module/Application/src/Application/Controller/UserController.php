<?php
namespace Application\Controller;

use Application\Form\UploadForm;
use Application\Model\Post;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController
{
    public function indexAction()
    {
        $form = new UploadForm('upload-form');
        $postTable = $this->getServiceLocator()->get('PostTable');
        $iduser = $this->getServiceLocator()->get('UserTable');
        $id = $iduser->getBy('id',['email' => $_SESSION['userEmail']]);
        $posts = $postTable->show($id);
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

        return new ViewModel(['form' => $form, 'posts' => $posts, 'postTable' => $postTable]);
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

    public function SearchAction()
    {
        return "1aaa";
    }





}