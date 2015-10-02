<?php
namespace Application\Model;
class User
{
    public $id;
    public $name;
    public $email;
    public $password;
    public $ava;
    public $idSocial;
    public $codeActivation;
    public $link;
    public function setPassword($pass)
    {
        $this->password = md5($pass);
    }

    function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
        $this->ava = (isset($data['ava'])) ? $data['ava'] : 'default-avatar-male.png';
        $this->idSocial = (isset($data['idSocial'])) ? $data['idSocial'] : null;
        $this->codeActivation = (isset($data['codeActivation'])) ? $data['codeActivation'] : null;
        $this->link = (isset($data['link'])) ? $data['link'] : $data['name'];
        if(isset($data['password']))
        {
            $this->setPassword($data['password']);
        }
    }
}