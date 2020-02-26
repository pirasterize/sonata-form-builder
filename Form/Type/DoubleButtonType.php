<?php

namespace Pirastru\FormBuilderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DoubleButtonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['entry_options'] as $option) {
            $buttonType = $option['button_type'];
            $builder
                ->add('button_'.$option['key'], $buttonType, [
                    'label' => $option['label'],
                ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'double_button';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_options' => [],
        ]);
    }
}