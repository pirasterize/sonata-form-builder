<?php

namespace Pirastru\FormBuilderBundle\Twig;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;

class FormBuilderExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getFilters()
    {
        return array(
            'cast_to_array' => new \Twig_SimpleFilter('cast_to_array', function ($stdClassObject) {
                    $response = array();
                    foreach ($stdClassObject as $key => $value) {
                        $response[] = array($key, $value);
                    }

                    return $response;
                }),
        );
    }

    public function getFunctions()
    {
        return array(
            'json_decode' => new \Twig_Function_Method($this, 'jsonDecode'),
            'is_array' => new \Twig_Function_Method($this, 'isArray'),
        );
    }

    public function jsonDecode($str)
    {
        return json_decode($str);
    }

    public function isArray($var)
    {
        return is_array($var);
    }

    /**
     * @param FieldDescriptionInterface $fieldDescription
     * @param string                    $defaultTemplate
     *
     * @return \Twig_TemplateInterface
     */
    protected function getTemplate(FieldDescriptionInterface $fieldDescription, $defaultTemplate)
    {
        $templateName = $fieldDescription->getTemplate() ?: $defaultTemplate;

        try {
            $template = $this->environment->loadTemplate($templateName);
        } catch (\Twig_Error_Loader $e) {
            $template = $this->environment->loadTemplate($defaultTemplate);
        }

        return $template;
    }

    public function getName()
    {
        return 'form_builder_extension';
    }
}
