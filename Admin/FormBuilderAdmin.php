<?php

namespace Pirastru\FormBuilderBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
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
            ->add('recipient', 'collection', array(
                    'type' => 'email',
                    'label' => 'Recipient(s)',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'options' => array(
                        'label' => 'Email',
                        'required' => false,
                    ),
                )
            )
            ->add('recipientCC', 'collection', array(
                    'type' => 'email',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'options' => array(
                        'label' => 'Email',
                        'required' => false,
                    ),
                )
            )
            ->add('recipientBCC', 'collection', array(
                    'type' => 'email',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'options' => array(
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
}
