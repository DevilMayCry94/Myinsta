<?php
namespace Application\Controller;

use Application\Model\User;
use Application\Model\UserTable;
use Zend\Mvc\Controller\AbstractActionController;

class SocialController extends AbstractActionController
{
    public function facebookAction()
    {
        $provider = new \League\OAuth2\Client\Provider\Facebook([
            'clientId'          => CLIENT_ID,
            'clientSecret'      => SECRET,
            'redirectUri'       => REDIRECT,
            'graphApiVersion'   => 'v2.4',
        ]);
        $this->getData($provider);
    }

    public function googleAction()
    {
        $provider = new \League\OAuth2\Client\Provider\Google([
            'clientId' => '564845408196-ps9621pb6purdqbk6vba8jh9h2csbaur.apps.googleusercontent.com',
            'clientSecret'      => 'aC75BdPjsFCurnRZ8t34hw-u',
            'redirectUri'  => 'http://test.com/social/google',
        ]);
        $this->getData($provider);
    }

    public function getData($provider)
    {
        if (empty($_GET['code'])) {
            $authUrl = $provider->getAuthorizationUrl([
                'scope' => ['email'],
            ]);
            $_SESSION['oauth2state'] = $provider->getState();
            //$this->redirect()->toUrl($authUrl);
            header('Location: ' . $authUrl);
            exit;
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            echo 'Invalid state.';
            exit;
        }
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        try {
            $userdata = $provider->getResourceOwner($token);
            $_SESSION['user'] = $userdata->getName();
            $_SESSION['userEmail'] = $userdata->getEmail();
            $data = array('idSocial' => $userdata->getId(), 'name' => $userdata->getName(), 'email' => $userdata->getEmail());
            if(property_exists('user','getPictureUrl')) {
                $data['ava'] =  $userdata->getPictureUrl();
            }
            $user = new User();
            $user->exchangeArray($data);
            $userTable = new UserTable($this->getServiceLocator()->get('UserTableGateway'));
            if($userTable->existEmailSocial($userdata->getEmail())) {
                $userTable->save($user);
            }
                $this->redirect()->toRoute(Null, ['controller' => 'user', 'action' => 'new']);

        } catch (Exception $e) {

            exit('Oh dear...');
        }
    }
}