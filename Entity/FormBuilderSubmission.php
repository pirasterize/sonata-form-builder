<?php

namespace Pirastru\FormBuilderBundle\Entity;

use Pirastru\FormBuilderBundle\Entity\FormBuilder as Form;
use Doctrine\ORM\Mapping as ORM;

/**
 * Form Builder Entity.
 *
 * @ORM\Table(name="form__builder__submission")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class FormBuilderSubmission
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(name="value", type="array", nullable=false)
     */
    private $value;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime|null $exportedAt
     *
     * @ORM\Column(name="exported_at", type="datetime", nullable=true)
     */
    private $exportedAt;

    /**
     * @var Form
     *
     * @ORM\ManyToOne(targetEntity="Pirastru\FormBuilderBundle\Entity\FormBuilder", inversedBy="submissions")
     */
    private $form;

    /**
     * FormBuilderSubmission constructor.
     * @param array $value
     * @param FormBuilder $form
     */
    public function __construct(array $value, FormBuilder $form)
    {
        $this->value = $value;
        $this->form = $form;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return $this->value;
    }

    /**
     * @param array $value
     * @return self
     */
    public function setValue(array $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAt()
    {
        if ($this->createdAt !== null) {
            return;
        }
        $this->createdAt = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getExportedAt(): ?\DateTime
    {
        return $this->exportedAt;
    }

    /**
     * @return self
     */
    public function export(): self
    {
        $this->exportedAt = new \DateTime();
        return $this;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @param Form $form
     * @return self
     */
    public function setForm(Form $form): self
    {
        $this->form = $form;
        return $this;
    }
}
