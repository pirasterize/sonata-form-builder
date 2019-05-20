<?php

namespace Pirastru\FormBuilderBundle\Twig;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FormBuilderExtension extends AbstractExtension
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * {@inheritdoc}
     */
    public function initRuntime(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getFilters()
    {
        return array(
            'cast_to_array' => new TwigFilter('cast_to_array', function ($stdClassObject) {
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
            'json_decode' => new TwigFunction('json_decode', array($this, 'jsonDecode')),
            'is_array' => new TwigFunction('is_array', array($this, 'isArray')),
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
     * @param $defaultTemplate
     * @return \Twig\TemplateWrapper
     * @throws LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function getTemplate(FieldDescriptionInterface $fieldDescription, $defaultTemplate)
    {
        $templateName = $fieldDescription->getTemplate() ?: $defaultTemplate;

        try {
            $template = $this->environment->load($templateName);
        } catch (LoaderError $e) {
            $template = $this->environment->load($defaultTemplate);
        }

        return $template;
    }

    public function getName()
    {
        return 'form_builder_extension';
    }
}
