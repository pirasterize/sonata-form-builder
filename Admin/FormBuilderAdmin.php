<?php

namespace Pirastru\FormBuilderBundle\Admin;

use Pirastru\FormBuilderBundle\Entity\FormBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormBuilderAdmin extends AbstractAdmin
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
            ->add('name');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('persistable', 'boolean', [
                'label' => 'Save to database?'
            ])
            ->add('mailable', 'boolean', [
                'label' => 'Email responses?'
            ])
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ]
            ]);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('json', HiddenType::class)
            ->add('name', TextType::class)
            ->add('persistable', CheckboxType::class, [
                'required' => false,
                'label' => 'Save to database?'
            ])
            ->add('mailable', CheckboxType::class, [
                'required' => false,
                'label' => 'Email responses?',
            ])
            ->add('subject', TextType::class, [
                'sonata_help' => "You can use &lt;Internal Key&gt; to add variables to your subject. Example: This email is from &lt;Name&gt;",
                'required' => false,
            ])
            ->add('reply_to', TextType::class, [
                'sonata_help' => "You can use &lt;Internal Key&gt; to add variables to your reply to field. Example: &lt;Email&gt;",
                'required' => false,
            ])
            ->add('recipient', CollectionType::class, array(
                    'sonata_help' => "You can use &lt;Internal Key&gt; to add variables to your recipient field. Example: &lt;Email&gt;",
                    'entry_type' => EmailType::class,
                    'label' => 'Recipient(s)',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'required' => false,
                    'entry_options' => array(
                        'label' => 'Email',
                        'required' => false,
                    ),
                )
            )
            ->add('recipientCC', CollectionType::class, array(
                    'sonata_help' => "You can use &lt;Internal Key&gt; to add variables to your recipient CC field. Example: &lt;Email&gt;",
                    'entry_type' => EmailType::class,
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'entry_options' => array(
                        'label' => 'Email',
                        'required' => false,
                    ),
                )
            )
            ->add('recipientBCC', CollectionType::class, array(
                    'sonata_help' => "You can use &lt;Internal Key&gt; to add variables to your recipient BCC field. Example: &lt;Email&gt;",
                    'entry_type' => EmailType::class,
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'entry_options' => array(
                        'label' => 'Email',
                        'required' => false,
                    ),
                )
            )
            ->add('submissionTitle', TextType::class, [
                'required' => false,
                'label' => 'Custom submit title',
                'sonata_help' => 'Customize thank you title after successful form submission',
            ])
            ->add('submissionText', TextareaType::class, [
                'required' => false,
                'label' => 'Custom submit text',
                'sonata_help' => 'Customize thank you text after successful form submission',
            ]);
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
            ->add('export', null, ['template' => 'PirastruFormBuilderBundle:CRUD:table_export_form.html.twig'])
            ->add('submissions', null, array('template' => 'PirastruFormBuilderBundle:CRUD:table_show_field.html.twig'));
    }

    /**
     * @param ErrorElement $errorElement
     * @param FormBuilder $object
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('name')
            ->addConstraint(new NotBlank())
            ->end();

        if ($object->isMailable()) {
            $errorElement
                ->with('subject')
                ->addConstraint(new NotBlank())
                ->end()
                ->with('recipient')
                ->addConstraint(new NotBlank())
                ->end();
        }

    }

    public function getNewInstance()
    {
        $instance = parent::getNewInstance();

        $instance->setPersistable(true);

        return $instance;
    }
}
