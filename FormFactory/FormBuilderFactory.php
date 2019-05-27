<?php
/**
 * Created by Andrea Pirastru
 * Date: 03/12/14
 * Time: 12:27.
 */

namespace Pirastru\FormBuilderBundle\FormFactory;

use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Regex;
use Pirastru\FormBuilderBundle\Entity\FormBuilder as Form;
use Symfony\Component\Form\FormBuilder as SymfonyFormBuilder;

class FormBuilderFactory
{
    /**
     * Email Field.
     * 
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldEmailinput($form, $key, $elem): array
    {
        $form->add('email_'.$key, 'email', array(
            'required' => $elem->fields->required->value,
            'label' => $elem->fields->label->value,
            'help_block' => $elem->fields->helptext->value,
            'attr' => array(
                'placeholder' => $elem->fields->placeholder->value,
            ),
            'constraints' => array(
                new Email(),
            ),
        ));

        return array('name' => 'email_'.$key, 'size' => $this->getSelectedValue($elem->fields->inputwidth->value));
    }

    /**
     * Date Field.
     * 
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldDateinput(SymfonyFormBuilder $form, $key, $elem): array
    {
        $form->add('date_'.$key, 'text', array(
            'required' => $elem->fields->required->value,
            'label' => $elem->fields->label->value,
            'help_label' => $elem->fields->helptext->value,
            'attr' => array(
                'class' => 'date js-datepicker',
                'placeholder' => $elem->fields->placeholder->value,
            ),
            'constraints' => array(
                new Regex([
                    'pattern' =>  "/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/",
                    'message' => 'Invalid format: dd-mm-yyyy'
                ]),
            ),
        ));

        return array('name' => 'date_'.$key, 'size' => $this->getSelectedValue($elem->fields->inputwidth->value));
    }

    /**
     * Telephone Field.
     * 
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldTelephoneinput($form, $key, $elem): array
    {
        $form->add('telephone_'.$key, class_exists(TelType::class) ? TelType::class : TextType::class, [
            'required' => $elem->fields->required->value,
            'label' => $elem->fields->label->value,
            'help_block' => $elem->fields->helptext->value,
            'attr' => [
                'class' => 'telephone ',
                'placeholder' => $elem->fields->placeholder->value,
            ],
            'constraints' => [
                new Regex([
                    'pattern' => '/[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/',
                    'message' => 'Invalid telephone number.',
                ])
            ],
        ]);

        return array('name' => 'telephone_'.$key, 'size' => $this->getSelectedValue($elem->fields->inputwidth->value));
    }

    /**
     * Postalcode Field.
     * 
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldPostalcodeinput($form, $key, $elem): array
    {
        $form->add('postalcode_'.$key, 'number', array(
            'required' => $elem->fields->required->value,
            'label' => $elem->fields->label->value,
            'help_block' => $elem->fields->helptext->value,
            'attr' => array(
                'class' => 'postalcode ',
                'placeholder' => $elem->fields->placeholder->value,
            ),
        ));

        return array('name' => 'postalcode_'.$key, 'size' => $this->getSelectedValue($elem->fields->inputwidth->value));
    }

    /**
     * Text Field.
     * 
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldTextinput($form, $key, $elem): array
    {
        $form->add('text_'.$key, 'text', array(
            'required' => $elem->fields->required->value,
            'label' => $elem->fields->label->value,
            'help_block' => $elem->fields->helptext->value,
            'attr' => array(
                'placeholder' => $elem->fields->placeholder->value,
            ),
        ));

        return array('name' => 'text_'.$key, 'size' => $this->getSelectedValue($elem->fields->inputwidth->value));
    }

    /**
     * Textarea Field.
     * 
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldTextarea($form, $key, $elem): array
    {
        $form->add('textarea_'.$key, 'textarea', array(
            'required' => false,
            'label' => $elem->fields->label->value,
            'help_block' => $elem->fields->helptext->value,
            'attr' => array(
                'placeholder' => $elem->fields->textarea->value,
            ),
        ));

        return array('name' => 'textarea_'.$key, 'size' => $this->getSelectedValue($elem->fields->inputwidth->value));
    }

    /**
     * Select basic Field.
     * 
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldSelectbasic($form, $key, $elem): array
    {
        $form->add('choice_'.$key, 'choice', array(
            'label' => $elem->fields->label->value,
            'choices' => array_flip($elem->fields->options->value),
            'required' => false,
            'placeholder' => false,
        ));

        return array('name' => 'choice_'.$key, 'size' => $this->getSelectedValue($elem->fields->inputsize->value));
    }

    /**
     * Select multiple Field.
     * 
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldSelectmultiple($form, $key, $elem): array
    {
        $form->add('choice_'.$key, 'choice', array(
            'label' => $elem->fields->label->value,
            'choices' => array_flip($elem->fields->options->value),
            'multiple' => true,
            'required' => false,
        ));

        return array('name' => 'choice_'.$key, 'size' => $this->getSelectedValue($elem->fields->inputsize->value));
    }

    /**
     * Multiple Radio Field.
     * 
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldMultipleradios($form, $key, $elem): array
    {
        $form->add('radio_'.$key, 'choice', array(
            'label' => $elem->fields->label->value,
            'choices' => array_flip($elem->fields->radios->value),
            'multiple' => false,
            'placeholder' => false,
            'required' => false,
            'expanded' => true,
        ));

        return array('name' => 'radio_'.$key, 'size' => 'col-sm-6');
    }

    /**
     * Multiple Checkbox Field.
     * 
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldMultiplecheckboxes($form, $key, $elem): array
    {
        $form->add('checkbox_'.$key, 'choice', array(
            'label' => $elem->fields->label->value,
            'choices' => array_flip($elem->fields->checkboxes->value),
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ));

        return array('name' => 'checkbox_'.$key, 'size' => 'col-sm-6');
    }

    /**
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldPrivacycheckbox($form, $key, $elem): array
    {
        $label = sprintf("%s <a href='%s'>%s</a>",
            $elem->fields->text->value,
            $elem->fields->url->value,
            $elem->fields->cta->value
        );

        $form->add('privacy_'.$key, CheckboxType::class, [
            'label' => $label,
            'required' => true,
            'label_attr' => [
                'style' => 'display:none;',
            ],
            'constraints' => array(
                new EqualTo(['value' => 1])
            ),
        ]);

        return array('name' => 'privacy_'.$key, 'size' => 'col-sm-6');
    }

    /**
     * Return the selected element on a list.
     * 
     * @param $select
     * @return bool
     */
    private function getSelectedValue($select): bool
    {
        foreach ($select as $elem) {
            if ($elem->selected) {
                return $elem->value;
            }
        }

        return false;
    }

    /**
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldSinglebutton($form, $key, $elem): array
    {
        $action = $this->getSelectedValue($elem->fields->buttonaction->value);
        $this->createButton($form, $action, $key, $elem->fields->buttonlabel->value);

        return array('name' => 'button_'.$key, 'size' => 'col-sm-6');
    }

    /**
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldDoublebutton($form, $key, $elem): array
    {
        $action = $this->getSelectedValue($elem->fields->button1action->value);
        $this->createButton($form, $action, '1_'.$key, $elem->fields->button1label->value);

        $action = $this->getSelectedValue($elem->fields->button2action->value);
        $this->createButton($form, $action, '2_'.$key, $elem->fields->button2label->value);

        return array(
            'name' => 'button_'.$key, 'size' => 'col-sm-6',
        );
    }

    /**
     * @param SymfonyFormBuilder $form
     * @param $action
     * @param $key
     * @param $value
     */
    private function createButton(SymfonyFormBuilder $form, $action, $key, $value): void
    {
        $buttonType = $this->getButtonType($action);

        $form->add('button_'.$key, $buttonType, array(
            'label' => $value,
        ));
    }

    /**
     * @param $action
     * @return string
     */
    private function getButtonType($action): string
    {
        switch ($action) {
            case 'submit':
                return SubmitType::class;
            case 'reset':
                return ResetType::class;
            default:
                return ButtonType::class;
        }
    }

    /**
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldCaptcha($form, $key, $elem): array
    {
        $form->add('captcha_'.$key, CaptchaType::class, array(
            'width' => 200,
            'height' => 50,
            'length' => 6,
            'label_attr' => [
                'style' => 'display:none;',
            ],
            'help_block' => $elem->fields->helptext->value,
        ));

        return array('name' => 'captcha_'.$key, 'size' => 'col-sm-6');
    }
}
