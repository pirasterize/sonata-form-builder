<?php

namespace Pirastru\FormBuilderBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pirastru\FormBuilderBundle\Entity\FormBuilder as Form;

/**
 * FormBuilderSubmissionRepository.
 */
class FormBuilderSubmissionRepository extends EntityRepository
{
    public function getNewSubmissions(Form $form)
    {
        return $this->findBy([
            'form' => $form,
            'exportedAt' => null,
        ]);
    }
}