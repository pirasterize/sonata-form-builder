<?php

namespace Pirastru\FormBuilderBundle\Event;

use Pirastru\FormBuilderBundle\Entity\FormBuilderSubmission;
use Symfony\Component\EventDispatcher\Event;

class SubmissionEvent extends Event
{
    protected FormBuilderSubmission $submission;

    /**
     * @param FormBuilderSubmission $submission
     */
    public function __construct(FormBuilderSubmission $submission)
    {
        $this->submission = $submission;
    }

    public function getSubmission(): FormBuilderSubmission
    {
        return $this->submission;
    }
}