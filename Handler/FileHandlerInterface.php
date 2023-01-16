<?php

namespace Pirastru\FormBuilderBundle\Handler;

use Pirastru\FormBuilderBundle\Entity\FormBuilderSubmission;
use Symfony\Component\Mime\Email;

interface FileHandlerInterface
{
    public function normalizeForPersistence(FormBuilderSubmission $submission): void;
    public function attachFilesToMail(Email $email, array $data): void;
    public function normalizeUploadedFiles(array $data): array;
}