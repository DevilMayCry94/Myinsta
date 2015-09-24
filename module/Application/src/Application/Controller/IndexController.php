<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;


use Application\Model\User;
use Application\Model\UserTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        if(isset($_SESSION['user']))
        {
            $this->redirect()->toRoute(Null,['controller' => 'user', 'action'=> 'new']);
        }

        if(!$this->request->isPost())
        {
            $form = $this->getServiceLocator()->get('RegisterForm');
            $viewModel = new ViewModel(['form' => $form]);
            return $viewModel;
        }

        $post = $this->request->getPost();
        if(isset($post->submitlog)) {
            $this->getServiceLocator()->get('getAuthService')->getAdapter()->setIdentity(
                $post->emailLogin)->setCredential($post->passLogin);
            $login = $this->getServiceLocator()->get('getAuthService')->authenticate();
            if($login->isValid())
            {
                $_SESSION['user'] = $post->emailLogin;
                return $this->redirect()->toRoute(Null,array(
                    'controller' => 'user',
                    'action' => 'new',
                ));
            } else {
                return $this->redirect()->toRoute(Null, array(
                    'controller' => 'user',
                    'action' => 'login'
                ));
            }
        }

        $form = $this->getServiceLocator()->get('RegisterForm');
        $form->setData($post);
        if(!$form->isValid())
        {
            $model = new ViewModel(array(
                'error' => true,
                'form' => $form,
            ));
            $model->setTemplate('application/index/index');
            return $model;
        }
        return ($this->createUser($form->getData()))? $this->redirect()->toRoute(Null,array(
            'controller' => 'user',
            'action' => 'confirmreg'
        )) : false;

    }

    public function createUser(array $data)
    {
        $user = new User();
        $user->exchangeArray($data);
        $userTable = new UserTable($this->getServiceLocator()->get('UserTableGateway'));
        $userTable->save($user);
        return true;
    }



}
