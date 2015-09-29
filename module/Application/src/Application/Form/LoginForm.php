<?php
/**
 * Created by PhpStorm.
 * User: dokuen
 * Date: 18.09.15
 * Time: 15:51
 */
namespace Application\Form;

use Zend\Form\Form;

class LoginForm extends Form
{
    public function __construct()
    {
        parent::__construct("Login");
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control',
                'placeholder' => 'Enter Email',
            ),
            "options" => array(
                "label" => "Email",
            ),
            "filters" => array(
                array("name" => "StringTrim"),
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-control',
                'placeholder' => 'Password',
            ),
            "options" => array(
                "label" => "Password",
            ),
        ));

        $this->add(array(
            "name" => "submitlog",
            "attributes" => array(
                "type" => "submit",
                "value" => "Login",
                "class" => "btn btn-success",
            )
        ));
    }

}