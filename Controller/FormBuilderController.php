<?php

namespace Pirastru\FormBuilderBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Exporter\Writer\XmlExcelWriter;
use Exporter\Writer\XmlWriter;
use Pirastru\FormBuilderBundle\Entity\FormBuilder as Form;
use Pirastru\FormBuilderBundle\Entity\FormBuilderSubmission as Submission;
use Pirastru\FormBuilderBundle\Event\MailEvent;
use Pirastru\FormBuilderBundle\FormFactory\FormBuilderFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Exporter\Writer\CsvWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * FormBuilder controller.
 */
class FormBuilderController extends AbstractController
{
    private $blacklist = [
        '_token',
        'button_',
        'privacy_',
        'captcha_'
    ];

    /**
     * @Route("/export_submit/{form}/{format}", name="form_builder_export_submit")
     *
     * @param Form $form
     * @param $format
     *
     * @return StreamedResponse
     *
     * @throws \RuntimeException
     */
    public function exportSubmitAction(Form $form, $format): StreamedResponse
    {
        //TODO export
        $submissions = $form->getSubmissions();

        switch ($format) {
            case 'xlsx':
                $writer = new XmlExcelWriter('php://output', false);
                $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'csv':
                $writer = new CsvWriter('php://output', ';', '"', '', false, true);
                $contentType = 'text/csv';
                break;
            default:
                throw new \RuntimeException('Invalid format');
        }

        $filename = sprintf('export_%s_%s.%s',
            $form->getName(),
            date('Y_m_d_H_i_s', strtotime('now')),
            $format
        );

        /*$content = [];
        foreach ($submissions as $submission) {
            $content[] = $this->buildSingleContent($form, $submission->getValue());
        }*/
        $callback = function () use ($submissions, $writer, $form) {
            $this->buildContent($submissions, $writer, $form);

        };

        return new StreamedResponse($callback, 200, array(
            'Content-Type' => $contentType,
            'Content-Disposition' => sprintf('attachment; filename=%s', $filename),
        ));
    }

    /**
     * function executed from the sonata-block in case
     * of submission of a front-end form Builder
     *
     * @param Form $form
     * @param $columns
     */
    public function submitOperations(Form $form, $columns): void
    {
        $em = $this->getDoctrine()->getManager();
        $form_submit = $this->container->get('request_stack')->getCurrentRequest()->request->all();

        $form->setColumns($columns);

        if ($form->isPersistable()) {
            $submission = new Submission($form_submit['form'], $form);
            $form->addSubmission($submission);
            $em->persist($submission);
        }

        $em->persist($form);
        $em->flush();

        /****************************
         * Send Emails to recipients
         ***************************/
        if ($form->isMailable()) {
            $this->sendEmailToRecipient($form, $form_submit);
        }
    }

    /**
     * Send Email to all Recipients defined for this form Builder
     *
     * @param Form $form
     * @param $form_submit
     */
    private function sendEmailToRecipient(Form $form, $form_submit): void
    {
        /* ******************************
         * Check if Recipient is not Empty and
         * default email_from too
         * ****************************** */
        $recipient = $form->getRecipient();

        if (!empty($recipient) && $this->container->getParameter('formbuilder_email_from') !== null) {
            $message = (new \Swift_Message())
                ->setFrom($this->container->getParameter('formbuilder_email_from'))
                ->setTo($recipient);

            $emailCc = $form->getRecipientCC();
            if (!empty($emailCc)) {
                $message->setCc($emailCc);
            }

            $emailBcc = $form->getRecipientBCC();
            if (!empty($emailBcc)) {
                $message->setBcc($emailBcc);
            }

            $data = $this->buildSingleContent($form, $form_submit);

            $patterns = array_map(function($key) { return '#<' . $key . '>#';}, array_values($data['headers']));
            $subject = preg_replace($patterns, array_values($data['data']), $form->getSubject());

            $message->setSubject($subject);

            if ($form->getReplyTo() !== NULL){
                $patterns = array_map(function($key) { return '#<' . $key . '>#';}, array_values($data['headers']));
                $replyTo = preg_replace($patterns, array_values($data['data']), $form->getReplyTo());

                $errors = $this->get('validator')->validate(
                    $replyTo,
                    array(
                        new NotBlank(),
                        new EmailConstraint(),
                    )
                );

                if (count($errors) === 0){
                    $message->setReplyTo($replyTo);
                }
            }

            $html = $this->renderView('PirastruFormBuilderBundle:Mail:resume.html.twig', [
                'data' => $data,
                'name' => $form->getName()
            ]);

            $message->setBody($html, 'text/html');

            $dispatcher = $this->get('event_dispatcher');
            $event = new MailEvent($message, $data);
            $dispatcher->dispatch('pirastru.formbuilder.event.mail', $event);

            $this->get('mailer')->send($event->getMessage());
        }
    }

    /**
     * This function
     * Translate a json_form object to a symfony form
     *
     * @param $formbuild
     * @return array
     */
    public function generateFormFromFormBuilder($formbuild): array
    {
        $formBuilder = $this->createFormBuilder(array(), array(
            'action' => '#',
            'method' => 'POST',
            'attr' => array(
                'id' => 'form_builder'.$formbuild->getId(),
            ),
        ));

        $size_col = array();/* column size */
        $title_col = array();
        $formBuilderFactory = new FormBuilderFactory();

        /*
         * start processing each json object elements
         * each element is a form field like 'Text Input'
         */
        $obj_form = json_decode($formbuild->getJson());
        foreach ($obj_form as $key => $elem) {
            if ($elem->typefield == 'formname') {
                continue;
            }

            /*
             * Call of a special function named setFieldXXXXXXXXXX
             * previous defined in FormBuilderFactory Class
             */
            $field_fun = 'setField'.ucfirst($elem->typefield);
            if (method_exists($formBuilderFactory, $field_fun)) {
                $field_detail = $formBuilderFactory->$field_fun($formBuilder, $key, $elem);

                if (isset($elem->fields->label)) {
                    $title_col[$field_detail['name']] = $elem->fields->label->value;
                } else {
                    $title_col[$field_detail['name']] = $elem->title;
                }
                $size_col[$field_detail['name']] = $field_detail['size'];
            }
        }

        /* Return a Symfony Form Object
         * with columns titles;
         * the field $size_col size
         * =>>> array(form, $title_col, $size_col) */
        return array('form' => $formBuilder->getForm(), 'title_col' => $title_col, 'size_col' => $size_col);
    }

    /**
     * @param Submission[]|ArrayCollection $submissions
     * @param CsvWriter|XmlExcelWriter $writer
     * @param Form $form
     * @return void
     */
    private function buildContent($submissions, $writer, Form $form): void
    {
        $writer->open();
        $formArray = json_decode($form->getJson());
        $headers = [];
        $data = [];

        $index = 0;
        foreach ($submissions as $submission) {

            $data = [];
            foreach ($submission->getValue() as $key => $submittedValue) {
                if (!$this->validKey($key)) {
                    continue;
                }

                list($type, $position) = explode('_', $key);

                switch ($type) {
                    case 'radio':
                        $value = $formArray[$position]->fields->radios->value[$submittedValue];
                        break;
                    case 'choice':
                        $value = $this->formatMulti($submittedValue, $formArray[$position]);
                        break;
                    case 'checkbox':
                        $value = $this->formatMulti($submittedValue, $formArray[$position], 'checkboxes');
                        break;
                    default:
                        $value = $submittedValue;
                }

                if ($index === 0) {
                    $header = $form->getColumns()[$key];
                    if (isset($formArray[$position]->fields->key) && $formArray[$position]->fields->key->value != '') {
                        $header = $formArray[$position]->fields->key->value;
                    }
                    $headers[] = $header;
                }

                $data[] = $value;
            }

            if ($index === 0) {
                $writer->write($headers);
            }

            $index++;

            $writer->write($data);
        }

        $writer->close();
    }

    /**
     * @param Form $form
     * @param array $form_submit
     * @return array
     */
    private function buildSingleContent(Form $form, array $form_submit): array
    {
        $formArray = json_decode($form->getJson());
        $csvData = [
            'headers' => [],
            'data' => []
        ];

        foreach ($form_submit['form'] as $key => $submittedValue) {
            if (!$this->validKey($key)) {
                continue;
            }

            list($type, $position) = explode('_', $key);
            switch ($type) {
                case 'radio':
                    $value = $formArray[$position]->fields->radios->value[$submittedValue];
                    break;
                case 'choice':
                    $value = $this->formatMulti($submittedValue, $formArray[$position]);
                    break;
                case 'checkbox':
                    $value = $this->formatMulti($submittedValue, $formArray[$position], 'checkboxes');
                    break;
                default:
                    $value = $submittedValue;
            }

            $header = $form->getColumns()[$key];
            if (isset($formArray[$position]->fields->key) && $formArray[$position]->fields->key->value != '') {
                $header = $formArray[$position]->fields->key->value;
            }

            $csvData['headers'][] = $header;
            $csvData['data'][] = $value;
        }

        return $csvData;
    }

    /**
     * @param string $key
     * @return bool
     */
    private function validKey(string $key): bool
    {
        foreach ($this->blacklist as $blacklistItem) {
            if (strpos($key, $blacklistItem) !== FALSE) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $submittedValue
     * @param $formData
     * @param string $field
     * @return array|string
     */
    private function formatMulti($submittedValue, $formData, $field = 'options')
    {
        $value = [];
        if (is_array($submittedValue)) {
            foreach ($submittedValue as $submit) {
                $value[] = $formData->fields->$field->value[$submit];
            }
        } elseif ($submittedValue != '') {
            $value[] = $formData->fields->$field->value[$submittedValue];
        }
        $value = implode(', ', $value);

        return $value;
    }
}
