<?php
namespace Application\Model;

class SocialAuth
{
    public function get_token($code) {
        $ku = curl_init();

        $query = "client_id=".CLIENT_ID."&redirect_uri=".urlencode(REDIRECT)."&client_secret=".SECRET."&code=".$code;

        curl_setopt($ku,CURLOPT_URL,TOKEN."?".$query);
        curl_setopt($ku,CURLOPT_RETURNTRANSFER,TRUE);

        $result = curl_exec($ku);
        if(!$result) {
            exit(curl_error($ku));
        }

        if($i = json_decode($result)) {
            if($i->error) {
                exit($i->error->message);
            }
        }
        else {

            parse_str($result,$token);

            if($token['access_token']) {
                return $token['access_token'];
            }
        }
    }

    public function get_data($token) {

        $ku = curl_init();

        $query = "access_token=".$token;

        curl_setopt($ku,CURLOPT_URL,GET_DATA."?".$query);
        curl_setopt($ku,CURLOPT_RETURNTRANSFER,TRUE);

        $result = curl_exec($ku);
        if(!$result) {
            exit(curl_error($ku));
        }

        return json_decode($result);

    }

    public function FacebookAuth()
    {
        $provider = new \League\OAuth2\Client\Provider\Facebook([
            'clientId'          => CLIENT_ID,
            'clientSecret'      => SECRET,
            'redirectUri'       => REDIRECT,
            'graphApiVersion'   => 'v2.4',
        ]);

        if (!isset($_GET['code'])) {

            $authUrl = $provider->getAuthorizationUrl([
                'scope' => ['email'],
            ]);
            $_SESSION['oauth2state'] = $provider->getState();

            echo '<a href="'.$authUrl.'">Log in with Facebook!</a>';
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

            $user = $provider->getResourceOwner($token);

        } catch (Exception $e) {

            exit('Oh dear...');
        }

//        echo '<pre>';
//        var_dump($token->getToken());
//
//        var_dump($token->getExpires());
//        echo '</pre>';
//        $user = $provider->getResourceOwner($token);
//
//        $id = $user->getId();
//        var_dump($id);
//# string(1) "4"
//
//        $name = $user->getName();
//        var_dump($name);
//# string(15) "Mark Zuckerberg"
//
//        $firstName = $user->getFirstName();
//        var_dump($firstName);
//# string(4) "Mark"
//
//        $lastName = $user->getLastName();
//        var_dump($lastName);
//# string(10) "Zuckerberg"
//
//# Requires the "email" permission
//        $email = $user->getEmail();
//        var_dump($email);
//# string(15) "thezuck@foo.com"
//
//# Requires the "user_hometown" permission
//        $hometown = $user->getHometown();
//        var_dump($hometown);
//# array(10) { ["id"]=> string(10) "12345567890" ...
//
//# Requires the "user_about_me" permission
//        $bio = $user->getBio();
//        var_dump($bio);
//# string(426) "All about me...
//
//        $pictureUrl = $user->getPictureUrl();
//        var_dump($pictureUrl);
//# string(224) "https://fbcdn-profile-a.akamaihd.net/hprofile- ...
//
//        $coverPhotoUrl = $user->getCoverPhotoUrl();
//        var_dump($coverPhotoUrl);
//# string(111) "https://fbcdn-profile-a.akamaihd.net/hphotos- ...
//
//        $gender = $user->getGender();
//        var_dump($gender);
//# string(4) "male"
//
//        $locale = $user->getLocale();
//        var_dump($locale);
//# string(5) "en_US"
//
//        $link = $user->getLink();
//        var_dump($link);
    }

    public function saveUser(User $user )
    {

    }


}