<?php

namespace Pirastru\FormBuilderBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Pirastru\FormBuilderBundle\Entity\FormBuilder as Form;
use Pirastru\FormBuilderBundle\Entity\FormBuilderSubmission as Submission;
use Pirastru\FormBuilderBundle\Event\Events;
use Pirastru\FormBuilderBundle\Event\FormDataEvent;
use Pirastru\FormBuilderBundle\Event\MailEvent;
use Pirastru\FormBuilderBundle\Event\SubmissionEvent;
use Pirastru\FormBuilderBundle\FormFactory\FormBuilderFactory;
use Sonata\Exporter\Exception\InvalidDataFormatException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/export_submit/{form}", name="form_builder_export_submit", methods={"POST"})
     *
     * @param Request $request
     * @param Form $form
     *
     * @return StreamedResponse
     *
     * @throws \RuntimeException
     */
    public function exportSubmitAction(Request $request, Form $form): StreamedResponse
    {
        $range = $request->get('range');

        $writer = new CsvWriter('php://output', ';', '"', '\\', false, true);
        $contentType = 'text/csv';


        switch ($range) {
            case 'all':
                $submissions = $form->getSubmissions();
                break;
            case 'new':
                $em = $this->get('doctrine.orm.entity_manager');
                $submissionRepo = $em->getRepository(Submission::class);
                $submissions = $submissionRepo->getNewSubmissions($form);
                break;
            default:
                throw new \RuntimeException('Invalid export range');
        }


        $date = date('Y_m_d_H_i_s');

        $formNameSanitized = preg_replace('/[\x00-\x1F\x7F]/u', '', str_replace(' ', '', strip_tags($form->getName())));

        $filename = "export_{$formNameSanitized}_{$date}.csv";


        $callback = function () use ($submissions, $writer, $form) {
            $this->buildContent($submissions, $writer, $form);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => sprintf('attachment; filename=%s', $filename),
        ]);
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

        $files = $this->container->get('request_stack')->getCurrentRequest()->files->all();
        $form_submit = array_replace_recursive($form_submit, $files);

        $form->setColumns($columns);

        if ($form->isPersistable()) {
            $submission = new Submission($form_submit['form'], $form);
            $submissionEvent = new SubmissionEvent($submission);

            $this->get('event_dispatcher')->dispatch(Events::SUBMISSION_PRE_SAVE, $submissionEvent);

            $form->addSubmission($submissionEvent->getSubmission());
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
                ->setFrom($this->container->getParameter('formbuilder_email_from'));

            $data = $this->buildSingleContent($form, $form_submit);

            $patterns = array_map(function ($key) {
                return '#<' . quotemeta($key) . '>#';
            }, array_values($data['headers']));

            $message->setTo(preg_replace($patterns, array_values($data['data']), $recipient));

            $emailCc = $form->getRecipientCC();
            if (!empty($emailCc)) {
                $message->setCc(preg_replace($patterns, array_values($data['data']), $emailCc));
            }

            $emailBcc = $form->getRecipientBCC();
            if (!empty($emailBcc)) {
                $message->setBcc(preg_replace($patterns, array_values($data['data']), $emailBcc));
            }

            foreach ($data['data'] as $item) {
                quotemeta($item);
            }

            $subject = preg_replace($patterns, array_values($data['data']), $form->getSubject());

            $message->setSubject($subject);

            if ($form->getReplyTo() !== NULL) {
                $patterns = array_map(function ($key) {
                    return '#<' . quotemeta($key) . '>#';
                }, array_values($data['headers']));

                foreach ($data['data'] as $item) {
                    quotemeta($item);
                }

                $replyTo = preg_replace($patterns, array_values($data['data']), $form->getReplyTo());

                $errors = $this->get('validator')->validate(
                    $replyTo,
                    [
                        new NotBlank(),
                        new EmailConstraint(),
                    ]
                );

                if (count($errors) === 0) {
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
            $dispatcher->dispatch(Events::PRE_SEND_MAIL, $event);

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
        $formBuilder = $this->createFormBuilder([], [
            'action' => '#',
            'method' => 'POST',
            'attr' => [
                'id' => 'form_builder' . $formbuild->getId(),
            ],
        ]);

        $size_col = [];/* column size */
        $title_col = [];
        $extra_html_prefix_col = [];
        $formBuilderFactory = new FormBuilderFactory();

        /*
         * start processing each json object elements
         * each element is a form field like 'Text Input'
         */
        $obj_form = json_decode($formbuild->getJson(), false);
        $next_orphan_html_elem = null;
        foreach ($obj_form as $key => $elem) {
            if ($elem->typefield === 'formname') {
                continue;
            }

            /*
             * Call of a special function named setFieldXXXXXXXXXX
             * previous defined in FormBuilderFactory Class
             */
            $field_fun = 'setField' . ucfirst($elem->typefield);
            if (method_exists($formBuilderFactory, $field_fun)) {
                $field_detail = $formBuilderFactory->$field_fun($formBuilder, $key, $elem);

                if (isset($elem->fields->label)) {
                    $title_col[$field_detail['name']] = $elem->fields->label->value;
                } else {
                    $title_col[$field_detail['name']] = $elem->title;
                }
                $size_col[$field_detail['name']] = $field_detail['size'];

                if ($next_orphan_html_elem) {
                    $extra_html_prefix_col[$field_detail['name']] = $extra_html_prefix_col[$field_detail['name']] ?? [];
                    $extra_html_prefix_col[$field_detail['name']][] = $next_orphan_html_elem;
                    $next_orphan_html_elem = null;
                }
            }

            if ($elem->typefield === 'title' || $elem->typefield === 'linktext') {
                $next_orphan_html_elem = $elem;
            }
        }

        /* Return a Symfony Form Object
         * with columns titles;
         *
         * =>>> [form, $title_col, $size_col] */
        return [
            'form' => $formBuilder->getForm(), 'title_col' => $title_col, 'size_col' => $size_col, 'html_prefix_col' => $extra_html_prefix_col
        ];
    }

    /**
     * @param Submission[]|ArrayCollection $submissions
     * @param CsvWriter|XmlExcelWriter $writer
     * @param Form $form
     * @return void
     */
    private function buildContent($submissions, $writer, Form $form): void
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $writer->open();
        $formArray = json_decode($form->getJson(), true);
        $headers = [];

        $index = 0;

        if (count($submissions) === 0) {
            $writer->write(['no new submissions since last export']);
        }

        foreach ($submissions as $submission) {

            $data = [];
            foreach ($submission->getValue() as $key => $submittedValue) {
                if (!$this->validKey($key)) {
                    continue;
                }

                list($type, $position) = explode('_', $key);

                switch ($type) {
                    case 'radio':
                        $value = $formArray[$position]['fields']['radios']['value'][$submittedValue];
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

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                if ($index === 0) {
                    $header = $form->getColumns()[$key];
                    if (isset($formArray[$position]['fields']['key']) && $formArray[$position]['fields']['key']['value'] !== '') {
                        $header = $formArray[$position]['fields']['key']['value'];
                    }
                    $headers[] = $header;
                }

                $data[] = $value;
            }

            if ($index === 0) {
                try {
                    $writer->write($headers);
                } catch (InvalidDataFormatException $exception) {
                    $writer->write(["ERROR handling header data from submission id: {$submission->getId()}"]);
                }
            }

            $index++;

            try {
                $writer->write($data);
            } catch (InvalidDataFormatException $exception) {
                $writer->write(["ERROR handling data from submission id: {$submission->getId()}"]);
            }

            $submission->export();
        }

        $em->flush();

        $writer->close();
    }

    /**
     * @param Form $form
     * @param array $form_submit
     * @return array
     */
    private function buildSingleContent(Form $form, array $form_submit): array
    {
        $formArray = json_decode($form->getJson(), true);
        $data = [
            'headers' => [],
            'data' => [],
            'files' => [],
        ];

        $originalData = $form_submit['form'];
        $event = new FormDataEvent($originalData);
        $dispatcher = $this->get('event_dispatcher')->dispatch(Events::FORM_DATA_PRE_FORMAT, $event);

        foreach ($event->getData() as $key => $submittedValue) {
            if (!$this->validKey($key)) {
                continue;
            }

            [$type, $position] = explode('_', $key);

            $files = null;

            switch ($type) {
                case 'radio':
                    $value = $formArray[$position]['fields']['radios']['value'][$submittedValue];
                    break;
                case 'choice':
                    $value = $this->formatMulti($submittedValue, $formArray[$position]);
                    break;
                case 'checkbox':
                    $value = $this->formatMulti($submittedValue, $formArray[$position], 'checkboxes');
                    break;
                case 'file':
                    $value = $submittedValue;
                    $originalSubmittedValue = $originalData[$key] ?? null;
                    $files = is_array($originalSubmittedValue) ? $originalSubmittedValue : [$originalSubmittedValue];
                    break;
                default:
                    $value = $submittedValue;
            }

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $header = $form->getColumns()[$key];
            if (isset($formArray[$position]['fields']['key']) && $formArray[$position]['fields']['key']['value'] !== '') {
                $header = $formArray[$position]['fields']['key']['value'];
            }

            $data['headers'][] = $header;
            $data['data'][] = $value;

            if (is_array($files)) {
                $files = array_filter($files);
            }

            if ($files) {
                $data['files'][$header] = $files;
            }
        }

        return $data;
    }

    private function extractAttachments(Form $form, array $form_submit): array
    {
        $formArray = json_decode($form->getJson(), true);
        $data = [];

        foreach ($form_submit['form'] as $key => $submittedValue) {
            if (!$this->validKey($key)) {
                continue;
            }

            if (!is_array($submittedValue)) {
                $submittedValue = [$submittedValue];
            }

            [$type, $position] = explode('_', $key);

            if ($type !== 'file') {
                continue;
            }

            $header = $form->getColumns()[$key];
            if (isset($formArray[$position]['fields']['key']) && $formArray[$position]['fields']['key']['value'] !== '') {
                $header = $formArray[$position]['fields']['key']['value'];
            }

            $data[$header] = $submittedValue;
        }

        return $data;
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
                $value[] = $formData['fields'][$field]['value'][$submit];
            }
        } elseif ($submittedValue !== '') {
            $value[] = $formData['fields'][$field]['value'][$submittedValue];
        }
        $value = implode(', ', $value);

        return $value;
    }

    private function formatFiles(array $files)
    {
        $value = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $value[] = $file->getClientOriginalName();
        }

        return implode(', ', $value);
    }
}
