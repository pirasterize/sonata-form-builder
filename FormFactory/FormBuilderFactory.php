<?php
/**
 * Created by Andrea Pirastru
 * Date: 03/12/14
 * Time: 12:27.
 */

namespace Pirastru\FormBuilderBundle\FormFactory;

use Gregwar\CaptchaBundle\Type\CaptchaType;
use Pirastru\FormBuilderBundle\Form\Type\DoubleButtonType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
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
        $form->add('email_' . $key, 'email', [
            'required' => $elem->fields->required->value,
            'label' => $elem->fields->label->value,
            'attr' => [
                'placeholder' => $elem->fields->placeholder->value,
            ],
            'constraints' => [
                new Email(),
            ],
            'sonata_help' => $elem->fields->helptext->value,
        ]);

        return [
            'name' => 'email_' . $key,
            'size' => $this->getSelectedValue($elem->fields->inputwidth->value),
        ];
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
        $form->add('date_' . $key, 'text', [
            'required' => $elem->fields->required->value,
            'label' => $elem->fields->label->value,
            'attr' => [
                'class' => 'date js-datepicker',
                'placeholder' => $elem->fields->placeholder->value,
            ],
            'constraints' => [
                new Regex([
                    'pattern' => "/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/",
                    'message' => 'Invalid format: dd-mm-yyyy'
                ]),
            ],
            'sonata_help' => $elem->fields->helptext->value,
        ]);

        return [
            'name' => 'date_' . $key,
            'size' => $this->getSelectedValue($elem->fields->inputwidth->value),
        ];
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
        $form->add('telephone_' . $key, class_exists(TelType::class) ? TelType::class : TextType::class, [
            'required' => $elem->fields->required->value,
            'label' => $elem->fields->label->value,
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
            'sonata_help' => $elem->fields->helptext->value,
        ]);

        return [
            'name' => 'telephone_' . $key,
            'size' => $this->getSelectedValue($elem->fields->inputwidth->value),
        ];
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
        $form->add('postalcode_' . $key, 'number', [
            'required' => $elem->fields->required->value,
            'label' => $elem->fields->label->value,
            'attr' => [
                'class' => 'postalcode ',
                'placeholder' => $elem->fields->placeholder->value,
            ],
            'sonata_help' => $elem->fields->helptext->value,
        ]);

        return [
            'name' => 'postalcode_' . $key,
            'size' => $this->getSelectedValue($elem->fields->inputwidth->value),
        ];
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
        $form->add('text_' . $key, 'text', [
            'required' => $elem->fields->required->value,
            'label' => $elem->fields->label->value,
            'attr' => [
                'placeholder' => $elem->fields->placeholder->value,
            ],
            'sonata_help' => $elem->fields->helptext->value,
        ]);

        return [
            'name' => 'text_' . $key,
            'size' => $this->getSelectedValue($elem->fields->inputwidth->value),
        ];
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
        $form->add('textarea_' . $key, 'textarea', [
            'required' => false,
            'label' => $elem->fields->label->value,
            'attr' => [
                'placeholder' => $elem->fields->textarea->value,
            ],
            'sonata_help' => $elem->fields->helptext->value,
        ]);

        return [
            'name' => 'textarea_' . $key,
            'size' => $this->getSelectedValue($elem->fields->inputwidth->value),
        ];
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
        $form->add('choice_' . $key, 'choice', [
            'label' => $elem->fields->label->value,
            'choices' => array_flip($elem->fields->options->value),
            'required' => false,
            'placeholder' => false,
        ]);

        return [
            'name' => 'choice_' . $key,
            'size' => $this->getSelectedValue($elem->fields->inputsize->value),
        ];
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
        $form->add('choice_' . $key, 'choice', [
            'label' => $elem->fields->label->value,
            'choices' => array_flip($elem->fields->options->value),
            'multiple' => true,
            'required' => $elem->fields->required->value,
            'constraints' => [
                new Count([
                    'min' => 1,
                ])
            ],
        ]);

        return [
            'name' => 'choice_' . $key,
            'size' => $this->getSelectedValue($elem->fields->inputsize->value),
        ];
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
        $constraints = [];
        if ($elem->fields->required->value) {
            $constraints[] = new NotBlank();
        }
        $form->add('radio_' . $key, 'choice', [
            'label' => $elem->fields->label->value,
            'choices' => array_flip($elem->fields->radios->value),
            'multiple' => false,
            'placeholder' => false,
            'required' => $elem->fields->required->value,
            'expanded' => true,
            'constraints' => $constraints,
        ]);

        return [
            'name' => 'radio_' . $key,
            'size' => 'col-sm-6',
        ];
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
        $form->add('checkbox_' . $key, 'choice', [
            'label' => $elem->fields->label->value,
            'choices' => array_flip($elem->fields->checkboxes->value),
            'multiple' => true,
            'expanded' => true,
            'required' => $elem->fields->required->value,
            'constraints' => [
                new Count([
                    'min' => count($elem->fields->checkboxes->value),
                    'max' => count($elem->fields->checkboxes->value),
                ])
            ],
            'choice_attr' => function () use ($elem) {
                // adds a class like attending_yes, attending_no, etc
                return ['required' => $elem->fields->required->value];
            },
        ]);

        return [
            'name' => 'checkbox_' . $key,
            'size' => 'col-sm-6',
        ];
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

        $form->add('privacy_' . $key, CheckboxType::class, [
            'label' => $label,
            'required' => true,
            'constraints' => [
                new EqualTo(['value' => 1])
            ],
        ]);

        return [
            'name' => 'privacy_' . $key,
            'size' => 'col-sm-6',
        ];
    }

    /**
     * Return the selected element on a list.
     *
     * @param $select
     * @return bool|string
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

        return [
            'name' => 'button_' . $key,
            'size' => 'col-sm-6',
        ];
    }

    /**
     * @param SymfonyFormBuilder $form
     * @param $key
     * @param $elem
     * @return array
     */
    public function setFieldDoublebutton($form, $key, $elem): array
    {
        $action1 = $this->getSelectedValue($elem->fields->button1action->value);
        $buttonType1 = $this->getButtonType($action1);

        $action2 = $this->getSelectedValue($elem->fields->button2action->value);
        $buttonType2 = $this->getButtonType($action2);

        $form->add('double_button_' . $key, DoubleButtonType::class, [
            'entry_options' => [
                [
                    'button_type' => $buttonType1,
                    'key' => '1_' . $key,
                    'label' => $elem->fields->button1label->value,
                ],
                [
                    'button_type' => $buttonType2,
                    'key' => '2_' . $key,
                    'label' => $elem->fields->button2label->value,
                ]
            ]
        ]);

        return [
            'name' => 'button_' . $key,
            'size' => 'col-sm-6',
        ];
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

        $form->add('button_' . $key, $buttonType, [
            'label' => $value,
        ]);
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
        $form->add('captcha_' . $key, CaptchaType::class, array(
            'width' => 200,
            'height' => 50,
            'length' => 6,
            'label_attr' => [
                'style' => 'display:none;',
            ],
            'sonata_help' => $elem->fields->helptext->value,
        ));

        return [
            'name' => 'captcha_' . $key,
            'size' => 'col-sm-6',
        ];
    }
}
