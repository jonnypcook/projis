<?php
namespace Project\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class DocumentWizardForm extends Form implements \DoctrineModule\Persistence\ObjectManagerAwareInterface
{
    public function __construct(\Doctrine\ORM\EntityManager $em, \Project\Entity\Project $project, array $config = array())
    {
        $name = preg_replace('/^[\s\S]*[\\\]([a-z0-9_]+)$/i','$1',__CLASS__);
        // we want to ignore the name passed
        parent::__construct($name);
        
        $this->setObjectManager($em);

        $this->setHydrator(new DoctrineHydrator($this->getObjectManager(),'Project\Entity\Project'));
        
        
        $this->setAttribute('method', 'post');
        
        
        // attachments need to be outside as inclusion of an attachment can add options
        if (isset($config['attachments'])) {
            $options = array();
            $defaults = array();
            foreach ($config['attachments'] as $attachment=>$switch) {
                if ($switch) {
                    $defaults[] = $attachment;
                }
                switch ($attachment) {
                    case 'tac': $options[$attachment] = 'Terms of Service'; break;
                    case 'breakdown': $options[$attachment] = 'Cost Breakdown'; break;
                    case 'model': 
                        $options[$attachment] = 'Model Forecast'; 
                        $config['modelyears'] = $project->getModel();
                        break;
                    case 'fmt': $options[$attachment] = 'Finance Model'; break;
                    case 'modelGraph': $options[$attachment] = 'Model Graph'; break;
                    case 'survey': $options[$attachment] = 'Survey Request Form'; break;
                    case 'quotation': 
                        $options[$attachment] = 'Quotation Document'; 
                        $config["billstyle"] = 1; 
                        $config['vat'] = 1;
                        $config['notes'] = 1;
                        break;
                    case 'arch': $options[$attachment] = 'Architectural Layout'; break;
                    case 'spaces': $options[$attachment] = 'Space Configuration'; break;
                }
            }

            if (!empty($options)) {
                $this->add(array(     
                    'type' => 'multicheckbox',       
                    'name' => 'AttachmentSections',
                    'attributes' =>  array(
                        'data-content' => 'Select the attachments that you would like to include',
                        'data-original-title' => 'Attachments',
                        'data-trigger' => 'hover',
                        'class' => 'popovers ',
                    ),
                    'options' => array(
                        'label' => 'Attachments',
                        'class' => 'test',
                        'value_options' => $options,
                    ),
                ));/**/

                $this->get('AttachmentSections')->setValue($defaults);
            }
            
            unset($config['attachments']);
        }
        
        
        foreach ($config as $name => $value) {
            switch ($name) {
                case 'user':
                    if ($value==1) {
                        $this->add(array(     
                            'type' => 'Select',       
                            'name' => 'user',
                            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
                            'attributes' =>  array(
                                'data-content' => 'Selected company user',
                                'data-original-title' => 'User',
                                'data-trigger' => 'hover',
                                'class' => 'span6  popovers',
                                'data-placeholder' => "Choose a User",

                                //
                            ),
                            'options' => array(
                                'label' => 'User',
                                'object_manager' => $this->getObjectManager(),
                                'target_class'   => 'Application\Entity\User',
                                'order_by'=>'forename',
                                'label_generator' => function($targetEntity) {
                                    return $targetEntity->getForename() . ' ' . $targetEntity->getSurname();
                                },/**/
                                'is_method' => true,
                                'find_method' => array(
                                    'name' => 'findBy',
                                    'params' => array(
                                        'criteria' => array(),
                                        'orderBy' => array('forename' => 'ASC')
                                    )
                                ) 
                            ),
                        ));
                    }
                    break;
                case 'dispatch':
                    if ($value==1) {
                        $this->add(array(     
                            'type' => 'Select',       
                            'name' => 'dispatch',
                            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
                            'attributes' =>  array(
                                'data-content' => 'Selected delivery note',
                                'data-original-title' => 'Delivery Note',
                                'data-trigger' => 'hover',
                                'class' => 'span6  popovers',
                                'data-placeholder' => "Choose a Delivery Note",

                                //
                            ),
                            'options' => array(
                                'label' => 'Delivery Note',
                                'object_manager' => $this->getObjectManager(),
                                'target_class'   => 'Job\Entity\Dispatch',
                                'order_by'=>'dispatchId',
                                'label_generator' => function($targetEntity) {
                                    return '#'.str_pad($targetEntity->getDispatchId(), 5, "0", STR_PAD_LEFT) . ' | Send Date: ' . $targetEntity->getSent()->format('d/m/Y') . ' ' . (empty($targetEntity->getReference())?'':' | Reference: '.$targetEntity->getReference());
                                },/**/
                                'is_method' => true,
                                'find_method' => array(
                                    'name' => 'findByProjectId',
                                    'params' => array(
                                        'project_id' => $project->getProjectId(),
                                    )
                                ) 
                            ),
                        ));
                    }
                    break;
                case 'contact':
                    if ($value==1) {
                        $this->add(array(     
                            'type' => 'Select',       
                            'name' => 'contact',
                            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
                            'attributes' =>  array(
                                'data-content' => 'Selected client contact',
                                'data-original-title' => 'Contact',
                                'data-trigger' => 'hover',
                                'class' => 'span6  popovers',
                                'data-placeholder' => "Choose a Contact",

                                //
                            ),
                            'options' => array(
                                'label' => 'Contact',
                                'object_manager' => $this->getObjectManager(),
                                'target_class'   => 'Contact\Entity\Contact',
                                'order_by'=>'forename',
                                'label_generator' => function($targetEntity) {
                                    return $targetEntity->getTitle()->getDisplay() . ' ' . $targetEntity->getForename() . ' ' . $targetEntity->getSurname();
                                },/**/
                                'is_method' => true,
                                'find_method' => array(
                                    'name' => 'findByProjectId',
                                    'params' => array(
                                        'project_id' => $project->getProjectId(),
                                    )
                                ) 
                            ),
                        ));
                    }
                    break;
                case 'billstyle':
                    $this->add(array(     
                        'type' => 'select',       
                        'name' => 'billstyle',
                        'attributes' =>  array(
                            'data-content' => 'Select the style of the quotation',
                            'data-original-title' => 'Quotation Style',
                            'data-trigger' => 'hover',
                            'class' => 'span6 popovers ',
                            'value' => 2,
                        ),
                        'options' => array(
                            'label' => 'Quotation Style',
                            'value_options' => array (
                                1=>'Standard Layout',
                                2=>'Standard Layout (No Descriptions)',
                                3=>'Quantities Layout',
                                4=>'Architectural Layout',
                                5=>'Architectural Space Layout',
                            ),
                        ),
                    ));
                    break;
                case 'proposal':
                    $this->add(array(     
                        'type' => 'select',       
                        'name' => 'proposalstyle',
                        'attributes' =>  array(
                            'data-content' => 'Select the style of the proposal',
                            'data-original-title' => 'Quotation Style',
                            'data-trigger' => 'hover',
                            'class' => 'span6 popovers ',
                            'value' => 1,
                        ),
                        'options' => array(
                            'label' => 'Proposal Style',
                            'value_options' => array (
                                1=>'Standard Layout',
                                2=>'Architectural Layout',
                                3=>'Mears Layout',
                            ),
                        ),
                    ));
                    break;
                case 'modelyears':
                    $this->add(array(     
                        'type' => 'select',       
                        'name' => 'modelyears',
                        'attributes' =>  array(
                            'data-content' => 'Select the period for which to run the model',
                            'data-original-title' => 'Model Years',
                            'data-trigger' => 'hover',
                            'class' => 'span6 popovers ',
                            'value' => $value,
                        ),
                        'options' => array(
                            'label' => 'Model Period (Years)',
                            'value_options' => array (
                                1=>'1 Year',
                                2=>'2 Years',
                                3=>'3 Years',
                                4=>'4 Years',
                                5=>'5 Years',
                                6=>'6 Years',
                                7=>'7 Years',
                                8=>'8 Years',
                                9=>'9 Years',
                                10=>'10 Years',
                                11=>'11 Years',
                                12=>'12 Years',
                            ),
                        ),
                    ));
                    break;
                case 'vat':
                    $this->add(array(     
                        'type' => 'select',       
                        'name' => 'vat',
                        'attributes' =>  array(
                            'data-content' => 'Select whether to include VAT',
                            'data-original-title' => 'VAT',
                            'data-trigger' => 'hover',
                            'class' => 'span6 popovers ',
                        ),
                        'options' => array(
                            'label' => 'Include VAT',
                            'value_options' => array (
                                0=>'No',
                                1=>'Yes',
                            ),
                        ),
                    ));
                    break;
                case 'dAddress':
                    if ($value==1) {
                        $this->add(array(     
                            'type' => 'Select',       
                            'name' => 'dAddress',
                            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
                            'attributes' =>  array(
                                'data-content' => 'Delivery address for products',
                                'data-original-title' => 'Delivery Address',
                                'data-trigger' => 'hover',
                                'class' => 'span6  popovers',
                                'data-placeholder' => "Choose a Contact",

                                //
                            ),
                            'options' => array(
                                'label' => 'Delivery Address',
                                'object_manager' => $this->getObjectManager(),
                                'target_class'   => 'Contact\Entity\Address',
                                'order_by'=>'line1',
                                'label_generator' => function($targetEntity) {
                                    return $targetEntity->assemble();
                                },/**/
                                'is_method' => true,
                                'find_method' => array(
                                    'name' => 'findByClientId',
                                    'params' => array(
                                        'client_id' => $project->getClient()->getClientId(),
                                    )
                                ) 
                            ),
                        ));
                    }
                    break;
                case 'notes':
                    $this->add(array(     
                        'type' => 'textarea',       
                        'name' => 'notes',
                        'attributes' =>  array(
                            'data-content' => 'Enter additional notes',
                            'data-original-title' => 'Notes',
                            'data-trigger' => 'hover',
                            'class' => 'span6 popovers ',
                            'placeholder'=>'Enter notes separated by a new line'
                        ),
                        'options' => array(
                            'label' => 'Additional Notes',
                        ),
                    ));
                    break;
                case 'autosave':
                    $this->add(array(     
                        'type' => 'checkbox',       
                        'name' => 'autosave',
                        'attributes' =>  array(
                            'data-content' => 'Do you want to auto save this document to the Google Docs repository?',
                            'data-original-title' => 'Auto Save Document',
                            'data-trigger' => 'hover',
                            'class' => 'span6  popovers',
                            'value' => ($value==1),
                        ),
                        'options' => array(
                            'label' => 'Auto Save',
                        ),
                    ));
                    break;
                case 'model':
                    
                    break;
            }
        }
        

    }
    
    protected $objectManager;
    
    public function setObjectManager(\Doctrine\Common\Persistence\ObjectManager $objectManager)
    {
    	$this->objectManager = $objectManager;
    }
    
    public function getObjectManager()
    {
    	return $this->objectManager;
    }
}