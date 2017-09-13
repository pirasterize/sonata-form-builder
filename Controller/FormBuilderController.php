<?php

namespace Pirastru\FormBuilderBundle\Controller;

use Pirastru\FormBuilderBundle\Event\MailEvent;
use Pirastru\FormBuilderBundle\FormFactory\FormBuilderFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Exporter\Writer\XlsWriter;
use Exporter\Writer\CsvWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * FormBuilder controller.
 */
class FormBuilderController extends Controller
{
    private $blacklist = [
        '_token',
        'button_',
        'privacy_',
        'captcha_'
    ];

    /**
     * @Route("/export_submit/{id}/{format}", name="form_builder_export_submit")
     *
     * @param $id
     * @param $format
     *
     * @return StreamedResponse
     *
     * @throws \RuntimeException
     */
    public function exportSubmitAction($id, $format)
    {
        $formBuilder = $this->getDoctrine()->getRepository('PirastruFormBuilderBundle:FormBuilder')
            ->find($id);

        $json_object = $formBuilder->getSubmit();

        switch ($format) {
            case 'xls':
                $writer = new XlsWriter('php://output', false);
                $contentType = 'application/vnd.ms-excel';
                break;
            case 'csv':
                $writer = new CsvWriter('php://output', ';', '"', '', false, true);
                $contentType = 'text/csv';
                break;
            default:
                throw new \RuntimeException('Invalid format');
        }

        $filename = sprintf('export_%s_%s.%s',
            $formBuilder->getName(),
            date('Y_m_d_H_i_s', strtotime('now')),
            $format
        );

        $callback = function () use ($json_object, $writer, $formBuilder) {
            $this->buildContent($json_object, $writer, $formBuilder);
        };

        return new StreamedResponse($callback, 200, array(
            'Content-Type' => $contentType,
            'Content-Disposition' => sprintf('attachment; filename=%s', $filename),
        ));
    }

    /*
     * function executed from the sonata-block in case
     * of submission of a front-end form Builder
     */
    public function submitOperations($formBuilder, $columns)
    {
        $em = $this->getDoctrine()->getManager();
        $form_submit = $this->container->get('request')->request->all();

        /*******************************
         * Submits JSON Object from DB with all previous form builder submits
         * then append the new submit json to collect
         *******************************/
        $submits = $formBuilder->getSubmit();
        $formBuilder->setColumns($columns);

        if ($this->getParameter('pirastru_form_builder.save_data')) {
            /* append the new submit on tail of the previous Submits JSON */
            $submits[] = $form_submit['form'];
            $formBuilder->setSubmit($submits);
        }

        $em->persist($formBuilder);
        $em->flush();

        /****************************
         * Send Emails to recipients
         ***************************/
        $this->sendEmailToRecipient($formBuilder, $form_submit);
    }

    /*
     * Send Email to all Recipients defined for this form Builder
     */
    private function sendEmailToRecipient($formBuilder, $form_submit)
    {
        /* ******************************
         * Check if Recipient is not Empty and
         * default email_from too
         * ****************************** */
        $recipient = $formBuilder->getRecipient();

        if (!empty($recipient) && !is_null($this->container->getParameter('formbuilder_email_from'))) {
            $message = \Swift_Message::newInstance()
                ->setFrom($this->container->getParameter('formbuilder_email_from'))
                ->setTo($recipient);

            $emailCc = $formBuilder->getRecipientCC();
            if (!empty($emailCc)) {
                $message->setCc($emailCc);
            }

            $emailBcc = $formBuilder->getRecipientBCC();
            if (!empty($emailBcc)) {
                $message->setBcc($emailBcc);
            }

            $data = $this->buildSingleContent($formBuilder, $form_submit);

            $patterns = array_map(function($key) { return '#<' . $key . '>#';}, array_values($data['headers']));
            $subject = preg_replace($patterns, array_values($data['data']), $formBuilder->getSubject());

            $message->setSubject($subject);

            if ($formBuilder->getReplyTo() !== NULL){
                $patterns = array_map(function($key) { return '#<' . $key . '>#';}, array_values($data['headers']));
                $replyTo = preg_replace($patterns, array_values($data['data']), $formBuilder->getReplyTo());

                $errors = $this->get('validator')->validateValue(
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
                'name' => $formBuilder->getName()
            ]);

            $message->setBody($html, 'text/html');

            $dispatcher = $this->get('event_dispatcher');
            $event = new MailEvent($message, $data);
            $dispatcher->dispatch('pirastru.formbuilder.event.mail', $event);

            $this->get('mailer')->send($event->getMessage());
        }
    }

    /*
     * This function
     * Translate a json_form object to a symfony form
     */
    public function generateFormFromFormBuilder($formbuild)
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

    /*
     * TODO: Refactor this with the data from buildSingleContent
     *  Needed for export CSV/XSL
     *  permet de creer le contenu du fichier dans le format choisie (XLS,CSV)
     */
    private function buildContent($json_object, $writer, $formBuilder)
    {
        $columns = $formBuilder->getColumns();
        $obj_form = json_decode($formBuilder->getJson());// needed for get field labels

        $writer->open();

        $is_title = true;

        $title = array();
        foreach ($json_object as $line) {
            $response = array();

            /* First Line with title columns  */
            if ($is_title) {
                foreach ($columns as $key => $value) {
                    $el_k = explode('_', $key);
                    if ($el_k[0] == 'button') {
                        continue;
                    }
                    $title[] = $value;
                }

                $is_title = false;
                $writer->write($title);
            }

            /* Others Lines */
            foreach ($columns as $key => $value) {
                $el_k = explode('_', $key);
                if ($el_k[0] == 'button') {
                    continue;
                }
                if ($el_k[0] == 'radio') {
                    if ($line[$key] != '') {
                        $response[] = $obj_form[$el_k[1]]->fields->radios->value[$line[$key]];
                    }
                } elseif ($el_k[0] == 'choice') {
                    if (is_array($line[$key])) {
                        $r = array();
                        foreach ($line[$key] as $v) {
                            $r[] = $obj_form[$el_k[1]]->fields->options->value[$v];
                        }
                        $response[] = implode('|', $r);
                    } elseif ($line[$key] != '') {
                        $response[] = $obj_form[$el_k[1]]->fields->options->value[$line[$key]];
                    }
                } elseif ($el_k[0] == 'checkbox') {
                    if (is_array($line[$key])) {
                        $r = array();
                        foreach ($line[$key] as $v) {
                            $r[] = $obj_form[$el_k[1]]->fields->checkboxes->value[$v];
                        }
                        $response[] = implode('|', $r);
                    } elseif ($line[$key] != '') {
                        $response[] = $obj_form[$el_k[1]]->fields->checkboxes->value[$line[$key]];
                    }
                } elseif (isset($line[$key])) {
                    $response[] = $line[$key];
                }
            }

            /* write one line */
            $writer->write($response);
        }
        $writer->close();
    }

    private function buildSingleContent($formBuilder, $form_submit)
    {
        $formArray = json_decode($formBuilder->getJson());
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

            $header = $formBuilder->getColumns()[$key];
            if (isset($formArray[$position]->fields->key) && $formArray[$position]->fields->key->value != '') {
                $header = $formArray[$position]->fields->key->value;
            }

            $csvData['headers'][] = $header;
            $csvData['data'][] = $value;
        }

        return $csvData;
    }

    private function validKey($key)
    {
        foreach ($this->blacklist as $blacklistItem) {
            if (strpos($key, $blacklistItem) !== FALSE) {
                return false;
            }
        }

        return true;
    }

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
