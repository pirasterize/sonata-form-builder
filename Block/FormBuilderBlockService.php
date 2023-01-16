<?php
/**
 * Created By Andrea Pirastru
 * Date: 08/01/2014
 * Time: 17:28.
 */

namespace Pirastru\FormBuilderBundle\Block;

use Doctrine\ORM\EntityManagerInterface;
use Pirastru\FormBuilderBundle\Admin\FormBuilderAdmin;
use Pirastru\FormBuilderBundle\Controller\FormBuilderController;
use Pirastru\FormBuilderBundle\Entity\FormBuilder;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Model\BlockInterface;
use Twig\Environment;

/**
 * @author     Andrea Pirastru
 */
class FormBuilderBlockService extends AbstractBlockService implements EditableBlockService
{
    public function __construct(
        Environment $twig,
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager,
        private readonly FormBuilderAdmin $formBuilderAdmin,
        private readonly FormBuilderController $formBuilderController,
    ) {
        parent::__construct($twig);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => '@PirastruFormBuilder/Block/block_form_builder.html.twig',
            'formBuilderId' => null,
        ]);
    }

    public function getFormBuilderAdmin(): FormBuilderAdmin
    {
        return $this->formBuilderAdmin;
    }

    protected function getFieldFormBuilder(FormMapper $formMapper): FormBuilderInterface
    {
        // simulate an association ...
        $fieldDescription = $this->getFormBuilderAdmin()->createFieldDescription('form_builder');
        $fieldDescription->setAssociationAdmin($this->getFormBuilderAdmin());
        $fieldDescription->setAdmin($formMapper->getAdmin());

        return $formMapper->create('formBuilderId', ModelListType::class, [
            'sonata_field_description' => $fieldDescription,
            'label' => 'Form Builder',
            'class' => $this->getFormBuilderAdmin()->getClass(),
            'model_manager' => $this->getFormBuilderAdmin()->getModelManager(),
            'btn_add' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        $formBuilderId = $blockContext->getBlock()->getSetting('formBuilderId');

        /** @var FormBuilder $formBuilder */
        $formBuilder = $this->entityManager
            ->getRepository(FormBuilder::class)
            ->findOneBy(['id' => $formBuilderId]);

        // In case the FormBuilder Object is not defined
        // return a empty Response
        if ($formBuilder === null) {
            return $this->renderResponse($blockContext->getTemplate(), [], $response);
        }

        $form_pack = $this->formBuilderController
            ->generateFormFromFormBuilder($formBuilder);

        /** @var Form $form */
        $form = $form_pack['form'];
        $success = false;
        $request = $this->requestStack->getCurrentRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /***************************************
             * operations when the form Builder is submitted
             ***************************************/
            $this->preUpdate($blockContext->getBlock());

            $this->formBuilderController
                ->submitOperations($formBuilder, $form_pack['title_col']);

            $success = true;
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'formBuilderId' => $formBuilder->getId(),
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'form' => $form->createView(),
            'formBuilder' => $formBuilder,
            'title_col' => $form_pack['title_col'],
            'size_col' => $form_pack['size_col'],
            'html_prefix_col' => $form_pack['html_prefix_col'],
            'success' => $success,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block): void
    {
        $formBuilderId = $block->getSetting('formBuilderId');

        if ($formBuilderId) {
            $formBuilderId = $this->entityManager
                ->getRepository(FormBuilder::class)
                ->findOneBy(['id' => $formBuilderId]);
        }

        $block->setSetting('formBuilderId', $formBuilderId);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('formBuilderId', is_object($block->getSetting('formBuilderId')) ? $block->getSetting('formBuilderId')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('formBuilderId', is_object($block->getSetting('formBuilderId')) ? $block->getSetting('formBuilderId')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Form';
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                [$this->getFieldFormBuilder($form), null, []],
            ],
        ]);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        // NOOP
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata($this->getName());
    }
}
