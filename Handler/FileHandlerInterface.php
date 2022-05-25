<?php

namespace Pirastru\FormBuilderBundle\Handler;

use Pirastru\FormBuilderBundle\Entity\FormBuilderSubmission;

interface FileHandlerInterface
{
    public function normalizeForPersistence(FormBuilderSubmission $submission): void;
    public function attachFilesToMail(\Swift_Message $message, array $data): void;
    public function normalizeUploadedFiles(array $data): array;
}