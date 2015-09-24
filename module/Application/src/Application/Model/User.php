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
    public function setPassword($pass)
    {
        $this->password = md5($pass);
    }

    public function exchangeArray($data)
    {
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
        $this->ava = (isset($data['ava'])) ? $data['ava'] : null;
        $this->idSocial = (isset($data['idSocial'])) ? $data['idSocial'] : null;
        if(isset($data['password']))
        {
            $this->setPassword($data['password']);
        }
    }
}