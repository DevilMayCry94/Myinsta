<?php
namespace Application\Form;

use Zend\InputFilter\InputFilter;
class RegisterFilter extends  InputFilter
{
    public function __construct($adapter=Null, $message=Null){
        $this->add(array(
            'name' => 'name',
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
                        'min' => 1,
                        'max' => 255,
                    ),
                )
            )
        ));
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
                array(
                    'name' => 'dbnorecordexists',
                    'options' => array(
                        'table' => 'user',
                        'field' => 'email',
                        'adapter' => $adapter,
                        "message" => array(\Zend\Validator\Db\AbstractDb::ERROR_NO_RECORD_FOUND =>
                            "Email already exist!",
                        ),
                    ),
                )
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

        $this->add(array(
            'name' => 'confirm_password',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags',
                ),
            ),
            'validators' => array(
                array(
                    'name' => 'Identical',
                    'options' => array(
                        'token' => 'password',
                        'message' => array(\Zend\Validator\Identical::MISSING_TOKEN =>
                        'password do not match'
                        ),
                    ),
                )
            )
        ));
    }
}