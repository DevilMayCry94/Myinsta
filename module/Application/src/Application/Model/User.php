<?php
namespace Application\Model;
class User
{
    public $id;
    public $name;
    public $email;
    public $password;

    public function setPassword($pass)
    {
        $this->password = md5($pass);
    }

    function exchangeArray($data)
    {
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
        if(isset($data['password']))
        {
            $this->setPassword($data['password']);
        }
    }
}