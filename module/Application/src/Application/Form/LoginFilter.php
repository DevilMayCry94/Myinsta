<?php
namespace Application\Form;

use Zend\InputFilter\InputFilter;
class LoginFilter extends  InputFilter
{
    public function __construct($adapter=Null, $message=Null){
        $this->add(array(
            'name' => 'email',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'domain' => true,
                    )
                ),
            ),
        ));
        $this->add(array(
            'name' => 'password',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags',
                ),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 6,
                        'max' => 1000,
                        "message" => array(\Zend\Validator\StringLength::TOO_SHORT =>
                            "Password is too short",
                            \Zend\Validator\StringLength::TOO_LONG => 'Password is too long',
                        ),
                    ),
                )
            )
        ));


    }
}