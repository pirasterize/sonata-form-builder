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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormBuilderAdmin extends AbstractAdmin
{
    protected $container;

    public function __construct($code, $class, $baseControllerName, ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct($code, $class, $baseControllerName);
    }

    protected function configure(): void
    {
        $this->getTemplateRegistry()->setTemplate('edit', '@PirastruFormBuilder/CRUD/formbuilder.html.twig');
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('recipient')
            ->add('name');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('name')
            ->add('persistable', 'boolean', [
                'label' => 'Save to database?'
            ])
            ->add('mailable', 'boolean', [
                'label' => 'Email responses?'
            ])
            ->add('_actions', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ]
            ]);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('json', HiddenType::class)
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(groups: ['Default']),
                ],
            ])
            ->add('persistable', CheckboxType::class, [
                'required' => false,
                'label' => 'Save to database?'
            ])
            ->add('mailable', CheckboxType::class, [
                'required' => false,
                'label' => 'Email responses?',
            ])
            ->add('subject', TextType::class, [
                'help' => "You can use &lt;Internal Key&gt; to add variables to your subject. Example: This email is from &lt;Name&gt;",
                'help_html' => true,
                'required' => false,
                'constraints' => [
                    new NotBlank(groups: ['Mailable']),
                ],
            ])
            ->add('reply_to', TextType::class, [
                'help' => "You can use &lt;Internal Key&gt; to add variables to your reply to field. Example: &lt;Email&gt;",
                'help_html' => true,
                'required' => false,
            ])
            ->add('recipient', CollectionType::class, array(
                    'help' => "You can use &lt;Internal Key&gt; to add variables to your recipient field. Example: &lt;Email&gt;",
                    'help_html' => true,
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
                    'constraints' => [
                        new NotBlank(groups: ['Mailable']),
                    ],
                )
            )
            ->add('recipientCC', CollectionType::class, array(
                    'help' => "You can use &lt;Internal Key&gt; to add variables to your recipient CC field. Example: &lt;Email&gt;",
                    'help_html' => true,
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
                    'help' => "You can use &lt;Internal Key&gt; to add variables to your recipient BCC field. Example: &lt;Email&gt;",
                    'help_html' => true,
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
                'help' => 'Customize thank you title after successful form submission',
            ])
            ->add('submissionText', TextareaType::class, [
                'required' => false,
                'label' => 'Custom submit text',
                'help' => 'Customize thank you text after successful form submission',
            ]);
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('name')
            ->add('recipient')
            ->add('submissions', null, array('template' => '@PirastruFormBuilder/CRUD/table_show_field.html.twig'));
    }

    public function alterNewInstance(object $object): void
    {
        $object->setPersistable(true);
    }

    protected function configureFormOptions(array &$formOptions): void
    {
        $formOptions['validation_groups'] = function (FormInterface $form) {
            $groups = ['Default'];

            $formBuilder = $form->getData();

            if ($formBuilder instanceof FormBuilder && $formBuilder->isMailable()) {
                $groups[] = 'Mailable';
            }

            return $groups;
        };
    }
}
