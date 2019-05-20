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

class FormBuilderFactory
{
    /**
     * Email Field.
     */
    public function setFieldEmailinput($formBuilder, $key, $elem)
    {
        $formBuilder->add('email_'.$key, 'email', array(
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
     */
    public function setFieldDateinput($formBuilder, $key, $elem)
    {
        $formBuilder->add('date_'.$key, 'text', array(
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
     */
    public function setFieldTelephoneinput($formBuilder, $key, $elem)
    {
        $formBuilder->add('telephone_'.$key, class_exists(TelType::class) ? TelType::class : TextType::class, [
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
     */
    public function setFieldPostalcodeinput($formBuilder, $key, $elem)
    {
        $formBuilder->add('postalcode_'.$key, 'number', array(
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
     */
    public function setFieldTextinput($formBuilder, $key, $elem)
    {
        $formBuilder->add('text_'.$key, 'text', array(
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
     */
    public function setFieldTextarea($formBuilder, $key, $elem)
    {
        $formBuilder->add('textarea_'.$key, 'textarea', array(
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
     */
    public function setFieldSelectbasic($formBuilder, $key, $elem)
    {
        $formBuilder->add('choice_'.$key, 'choice', array(
            'label' => $elem->fields->label->value,
            'choices' => array_flip($elem->fields->options->value),
            'required' => false,
            'placeholder' => false,
        ));

        return array('name' => 'choice_'.$key, 'size' => $this->getSelectedValue($elem->fields->inputsize->value));
    }

    /**
     * Select multiple Field.
     */
    public function setFieldSelectmultiple($formBuilder, $key, $elem)
    {
        $formBuilder->add('choice_'.$key, 'choice', array(
            'label' => $elem->fields->label->value,
            'choices' => array_flip($elem->fields->options->value),
            'multiple' => true,
            'required' => false,
        ));

        return array('name' => 'choice_'.$key, 'size' => $this->getSelectedValue($elem->fields->inputsize->value));
    }

    /**
     * Multiple Radio Field.
     */
    public function setFieldMultipleradios($formBuilder, $key, $elem)
    {
        $formBuilder->add('radio_'.$key, 'choice', array(
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
     */
    public function setFieldMultiplecheckboxes($formBuilder, $key, $elem)
    {
        $formBuilder->add('checkbox_'.$key, 'choice', array(
            'label' => $elem->fields->label->value,
            'choices' => array_flip($elem->fields->checkboxes->value),
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ));

        return array('name' => 'checkbox_'.$key, 'size' => 'col-sm-6');
    }

    public function setFieldPrivacycheckbox($formBuilder, $key, $elem)
    {
        $label = sprintf("%s <a href='%s'>%s</a>",
            $elem->fields->text->value,
            $elem->fields->url->value,
            $elem->fields->cta->value
        );

        $formBuilder->add('privacy_'.$key, CheckboxType::class, [
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
     */
    private function getSelectedValue($select)
    {
        foreach ($select as $elem) {
            if ($elem->selected) {
                return $elem->value;
            }
        }

        return false;
    }

    public function setFieldSinglebutton($formBuilder, $key, $elem)
    {
        $action = $this->getSelectedValue($elem->fields->buttonaction->value);
        $this->createButton($formBuilder, $action, $key, $elem->fields->buttonlabel->value);

        return array('name' => 'button_'.$key, 'size' => 'col-sm-6');
    }

    public function setFieldDoublebutton($formBuilder, $key, $elem)
    {
        $action = $this->getSelectedValue($elem->fields->button1action->value);
        $this->createButton($formBuilder, $action, '1_'.$key, $elem->fields->button1label->value);

        $action = $this->getSelectedValue($elem->fields->button2action->value);
        $this->createButton($formBuilder, $action, '2_'.$key, $elem->fields->button2label->value);

        return array(
            'name' => 'button_'.$key, 'size' => 'col-sm-6',
        );
    }

    private function createButton($formBuilder, $action, $key, $value)
    {
        $buttonType = $this->getButtonType($action);

        $formBuilder->add('button_'.$key, $buttonType, array(
            'label' => $value,
        ));
    }

    private function getButtonType($action)
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

    public function setFieldCaptcha($formBuilder, $key, $elem)
    {
        $formBuilder->add('captcha_'.$key, CaptchaType::class, array(
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
