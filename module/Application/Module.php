<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\Model\ActionTable;
use Application\Model\PostTable;
use Zend\Db\Sql\Sql;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Application\Model\User;
use Application\Model\UserTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'abstract_factories' => array(),
            'aliases' => array(),
            'factories' => array(
// база данных
                'Sql' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $sql  = new Sql($dbAdapter);
                    return $sql;
                },
                'UserTable' => function ($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'UserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    return new TableGateway('user', $dbAdapter, null,
                        $resultSetPrototype);
                },
                'PostTable' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Application\Model\Post());
                    $tableGateway = new \Zend\Db\TableGateway\TableGateway('post',$dbAdapter, null, $resultSetPrototype);
                    return new PostTable($tableGateway);
                },
                'ActionTable' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Application\Model\ActionUser());
                    $tableGateway = new \Zend\Db\TableGateway\TableGateway('action',$dbAdapter, null, $resultSetPrototype);
                    return new ActionTable($tableGateway);
                },
                'RegisterFilter' => function($sm) {
                    $adapter = $sm->get('\Zend\Db\Adapter\Adapter');
                    $inputFilter = new \Application\Form\RegisterFilter($adapter);
                    return $inputFilter;
                },
                'LoginFilter' => function($sm) {
                    $adapter = $sm->get('\Zend\Db\Adapter\Adapter');
                    $inputFilter = new \Application\Form\LoginFilter($adapter);
                    return $inputFilter;
                },
                'RegisterForm' => function ($sm) {
                    $form = new \Application\Form\RegisterForm();
                    $form->setInputFilter($sm->get('RegisterFilter'));
                    return $form;
                },
                'LoginForm' => function ($sm) {
                    $form = new \Application\Form\LoginForm();
                    $form->setInputFilter($sm->get('LoginFilter'));
                    return $form;
                },
                'getAuthService' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    /*$credentialValidationCallback = function($hash, $pass) {
                        return password_verify($pass, $hash);
                    };*/
                    //$dbTableAuthAdapter = new CallbackCheckAdapter($dbAdapter,'user','email','password', $credentialValidationCallback);
                    $dbTableAuthAdapter = new DbTableAuthAdapter(
                        $dbAdapter,'user','email','password', 'MD5(?)');
                    $authService = new AuthenticationService();
                    $authService->setAdapter($dbTableAuthAdapter);
                    //$this->authservice = $authService;
                    return $authService;

                },
            ),
            'invokables' => array(),
            'services' => array(),
            'shared' => array(),
        );
    }
}
