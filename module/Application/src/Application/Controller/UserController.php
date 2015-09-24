<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
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
        return new ViewModel();
    }

    public function LoginAction()
    {
        return new ViewModel();
    }

    public  function LogoutAction()
    {
        session_destroy();
        $this->redirect()->toRoute('home');
    }


}