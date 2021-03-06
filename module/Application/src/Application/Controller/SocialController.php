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
            'clientId' => '181375994941-pr95p66ipum3o2o7kd7m036aovhb1m8s.apps.googleusercontent.com',
            'clientSecret'      => 'Zjf-UJzb9ml8V4jqLJtbz4_S',
            'redirectUri'  => 'http://dokuen-lx.nixsolutions.com/social/google',
        ]);
        $this->getData($provider);
    }

    public function twitterAction()
    {
        $server = new \League\OAuth1\Client\Server\Twitter(array(
            'identifier' => '8WRCuOfk6P4c8UQVZXuvpi4RT',
            'secret' => 'rKFQPjH8tVQtwlc1YFabOiN3SnlPoSOqvUA7YcLwhoVAJpc4L9',
            'callback_uri' => 'http://dokuen-lx.nixsolutions.com/social/twitter',
        ));
        $temporaryCredentials = $server->getTemporaryCredentials();
        $_SESSION['temporary_credentials'] = serialize($temporaryCredentials);
        session_write_close();
        $server->authorize($temporaryCredentials);
        if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) {
            $temporaryCredentials = unserialize($_SESSION['temporary_credentials']);
            $tokenCredentials = $server->getTokenCredentials($temporaryCredentials, $_GET['oauth_token'], $_GET['oauth_verifier']);
        }
        $user = $server->getUserDetails($tokenCredentials);
        $uid = $server->getUserUid($tokenCredentials);
        $email = $server->getUserEmail($tokenCredentials);
        $screenName = $server->getUserScreenName($tokenCredentials);
        $_SESSION['user'] = $user;
        $_SESSION['userEmail'] = $email;
        $data = array('idSocial' => $uid, 'name' => $screenName, 'email' => $email);
        $user = new User();
        $user->exchangeArray($data);
        $userTable = new UserTable($this->getServiceLocator()->get('UserTableGateway'));
        if($userTable->existEmailSocial($email)) {
            $userTable->save($user);
        }
        $this->redirect()->toRoute(Null, ['controller' => 'user', 'action' => 'new']);


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
