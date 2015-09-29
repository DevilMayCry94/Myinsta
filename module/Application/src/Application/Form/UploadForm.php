<?php
/**
 * Created by PhpStorm.
 * User: dokuen
 * Date: 28.09.15
 * Time: 13:08
 */
namespace Application\Form;
use Zend\Form\Element\File;

class UploadForm extends \Zend\Form\Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name,$options);
        $this->addElements();
        $this->addInputFilter();
    }

    public function addElements()
    {
        $file = new File('image-file');
        $file->setLabel('Image Upload')->setAttribute('id', 'image-file');
        $this->add($file);
    }

    public function addInputFilter()
    {
        $inputFilter = new \Zend\InputFilter\InputFilter();

        // File Input
        $fileInput = new \Zend\InputFilter\FileInput('image-file');
        $fileInput->setRequired(true);
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target'    => BASE_PATH . '/img/user.jpg',
                'randomize' => true,
            ));
        $inputFilter->add($fileInput);

        $this->setInputFilter($inputFilter);
    }
}