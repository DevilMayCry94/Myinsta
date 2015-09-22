<?php
/**
 * Created by PhpStorm.
 * User: dokuen
 * Date: 18.09.15
 * Time: 15:51
 */
namespace Application\Form;

use Zend\Form\Form;

class RegisterForm extends Form
{
    public function __construct()
    {
        parent::__construct("Register");
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control',
                'placeholder' => 'Enter Name',
                'id' => 'Name',
            ),
            "options" => array(
                "label" => "Name",
            ),
        ));

        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control',
                'placeholder' => 'Enter Email',
                'id' => 'Email',
            ),
            "options" => array(
                "label" => "Email",
            ),
            "filters" => array(
                array("name" => "StringTrim"),
            ),
            "validators" => array(
                array(
                    "name" => "EmailAddress",
                    "options" => array(
                        "message" => array(\Zend\Validator\EmailAddress::INVALID_FORMAT =>
                            "Email address format is invalid",
                        ),
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-control',
                'placeholder' => 'Password',
                'id' => 'Password',
            ),
            "options" => array(
                "label" => "Password",
            ),
        ));

        $this->add(array(
            'name' => 'confirm_password',
            'attributes' => array(
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-control',
                'placeholder' => 'Confirm password',
                'id' => 'Confirm_password',
            ),
            "options" => array(
                "label" => "Confirm password",
            ),
        ));

        $this->add(array(
            "name" => "submitreg",
            "attributes" => array(
                "type" => "submit",
                "value" => "Create an account",
                "class" => "btn btn-success",
            )
        ));
    }

}