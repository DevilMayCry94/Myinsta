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
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
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
                $this->getServiceLocator()->get('getAuthService')->getStorage()->write(
                  $post->emailLogin);
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
        $sm = $this->getServiceLocator();
        $adapter = $sm->get('\Zend\Db\Adapter\Adapter');
        $ResultSet = new ResultSet();
        $ResultSet->getArrayObjectPrototype(new User);
        $tableGateway = new TableGateway('user',$adapter,null,$ResultSet);
        $user = new User();
        $user->exchangeArray($data);
        $userTable = new UserTable($tableGateway);
        $userTable->save($user);
        return true;
    }

}
