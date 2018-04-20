<?php

namespace Pirastru\FormBuilderBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FormBuilderAdmin extends Admin
{
    protected $container;

    public function __construct($code, $class, $baseControllerName, ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct($code, $class, $baseControllerName);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('recipient')
            ->add('name')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('recipient')
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('json', 'hidden')
            ->add('name', 'text')
            ->add('subject', 'text', [
                'sonata_help' => "You can use &lt;Internal Key&gt; to add variables to your subject. Example: This email is from &lt;Name&gt;"
            ])
            ->add('reply_to', 'text',[
                'sonata_help' => "You can use &lt;Internal Key&gt; to add variables to your reply to field. Example: &lt;Email&gt;",
                'required' => false,
            ])
            ->add('recipient', 'collection', array(
                    'entry_type' => 'email',
                    'label' => 'Recipient(s)',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'entry_options' => array(
                        'label' => 'Email',
                        'required' => false,
                    ),
                )
            )
            ->add('recipientCC', 'collection', array(
                    'entry_type' => 'email',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'entry_options' => array(
                        'label' => 'Email',
                        'required' => false,
                    ),
                )
            )
            ->add('recipientBCC', 'collection', array(
                    'entry_type' => 'email',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'entry_options' => array(
                        'label' => 'Email',
                        'required' => false,
                    ),
                )
            )
        ;
    }

    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'PirastruFormBuilderBundle:CRUD:formbuilder.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('recipient')
            ->add('submit', null, array('template' => 'PirastruFormBuilderBundle:CRUD:table_show_field.html.twig'))
        ;
    }

    /**
     * @param ErrorElement $errorElement
     * @param mixed $object
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('name')
            ->assertNotBlank()
            ->end()
            ->with('subject')
            ->assertNotBlank()
            ->end()
        ;
    }
}
