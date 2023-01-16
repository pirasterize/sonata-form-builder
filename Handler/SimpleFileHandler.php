<?php

namespace Pirastru\FormBuilderBundle\Handler;

use Pirastru\FormBuilderBundle\Entity\FormBuilderSubmission;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Email;

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

    public function attachFilesToMail(Email $email, array $data): void
    {
        foreach ($data['files'] as $header => $files) {
            foreach ($files as $file) {

                $email->attachFromPath(
                    $file->getPathname(),
                    sprintf('[%s] %s', $header, $file->getClientOriginalName()),
                    'text/csv'
                );
            }
        }
    }
}