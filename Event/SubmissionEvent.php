<?php

namespace Pirastru\FormBuilderBundle\Event;

use Pirastru\FormBuilderBundle\Entity\FormBuilderSubmission;
use Symfony\Contracts\EventDispatcher\Event;

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
