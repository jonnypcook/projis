<?php
namespace Project\Filter;

use Zend\InputFilter\InputFilter;
 
class DocumentWizardInputFilter extends InputFilter {
 
    public function __construct(array $config = array()) {
        foreach ($config as $name => $value) {
            switch ($name) {
                case 'user':
                    if ($value==1) {
                        $this->add(array(
                            'name' => 'user',
                            'required' => true,
                        ));
                    }
                    break;
                case 'attachments':
                    $this->add(array(
                        'name' => 'AttachmentSections',
                        'required' => false,
                    ));
                    break;
            }
        }
    }
}