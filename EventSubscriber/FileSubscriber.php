<?php

namespace Pirastru\FormBuilderBundle\EventSubscriber;

use Pirastru\FormBuilderBundle\Event\Events;
use Pirastru\FormBuilderBundle\Event\FormDataEvent;
use Pirastru\FormBuilderBundle\Event\MailEvent;
use Pirastru\FormBuilderBundle\Event\SubmissionEvent;
use Pirastru\FormBuilderBundle\Handler\FileHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FileSubscriber implements EventSubscriberInterface
{
    protected FileHandlerInterface $fileHandler;

    /**
     * @param FileHandlerInterface $fileHandler
     */
    public function __construct(FileHandlerInterface $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::SUBMISSION_PRE_SAVE => [
                ['normalizeForPersistence', 0],
            ],
            Events::PRE_SEND_MAIL => [
                ['attachFiles', 0],
            ],
            Events::FORM_DATA_PRE_FORMAT => [
                ['normalizeFormData', 0],
            ],
        ];
    }

    public function normalizeForPersistence(SubmissionEvent $event)
    {
        $this->fileHandler->normalizeForPersistence($event->getSubmission());
    }

    public function attachFiles(MailEvent $event)
    {
        $this->fileHandler->attachFilesToMail($event->getMessage(), $event->getFormData());
    }

    public function normalizeFormData(FormDataEvent $event)
    {
        $event->setData($this->fileHandler->normalizeUploadedFiles($event->getData()));
    }
}