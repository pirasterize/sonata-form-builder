<?php

namespace Pirastru\FormBuilderBundle\Handler;

use Pirastru\FormBuilderBundle\Entity\FormBuilderSubmission;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SimpleFileHandler implements FileHandlerInterface
{
    public function normalizeForPersistence(FormBuilderSubmission $submission): void
    {
        $values = $this->normalizeUploadedFiles($submission->getValue());
        $submission->setValue($values);
    }

    public function normalizeUploadedFiles(array $data): array
    {
        foreach ($data as $key => $element) {
            if ($element instanceof UploadedFile) {
                $data[$key] = $element->getClientOriginalName();
            }

            // If array, check recursive for uploaded files
            if (is_array($element)) {
                $data[$key] = $this->normalizeUploadedFiles($element);
            }
        }

        return $data;
    }

    public function attachFilesToMail(\Swift_Message $message, array $data): void
    {
        foreach ($data['files'] as $header => $files) {
            foreach ($files as $file) {
                $attachment = \Swift_Attachment::fromPath($file->getPathname());
                $attachment->setFilename(sprintf('[%s] %s', $header, $file->getClientOriginalName()));
                $message->attach($attachment);
            }
        }
    }
}