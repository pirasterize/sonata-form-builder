<?php

namespace Pirastru\FormBuilderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Pirastru\FormBuilderBundle\Entity\FormBuilderSubmission as Submission;
use Doctrine\ORM\Mapping as ORM;

/**
 * Form Builder Entity.
 *
 * @ORM\Table(name="form__builder")
 * @ORM\Entity(repositoryClass="Pirastru\FormBuilderBundle\Entity\FormBuilderRepository")
 */
class FormBuilder
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
     * @var json
     *
     * @ORM\Column(name="json", type="json")
     */
    private $json;

    /**
     * @var array
     *
     * @ORM\Column(name="columns", type="array", nullable=true)
     */
    private $columns;

    /**
     * @var array
     *
     * @ORM\Column(name="recipient", type="array")
     */
    private $recipient;

    /**
     * @var array
     *
     * @ORM\Column(name="recipientcc", type="array")
     */
    private $recipientCC;

    /**
     * @var array
     *
     * @ORM\Column(name="recipientbcc", type="array")
     */
    private $recipientBCC;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @var string|null
     *
     * @ORM\Column(name="reply_to", type="string", length=255, nullable=true)
     */
    private $replyTo;

    /**
     * @var Submission[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pirastru\FormBuilderBundle\Entity\FormBuilderSubmission", mappedBy="form")
     */
    private $submissions;

    /**
     * @var bool
     *
     * @ORM\Column(name="persistable", type="boolean", nullable=false, options={"default": false})
     */
    private $persistable = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="exportable", type="boolean", nullable=false, options={"default": true})
     */
    private $exportable = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="mailable", type="boolean", nullable=false)
     */
    private $mailable = true;

    /**
     * @var null|string
     *
     * @ORM\Column(name="submission_title", type="string", nullable=true)
     */
    private $submissionTitle;

    /**
     * @var null|string
     *
     * @ORM\Column(name="submission_text", type="string", nullable=true)
     */
    private $submissionText;

    public function __construct()
    {
        $this->recipient = array();
        $this->recipientCC = array();
        $this->recipientBCC = array();
        $this->submissions = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ?: 'Create a Form Builder';
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param array $recipientCC
     */
    public function setRecipientCC($recipientCC)
    {
        $this->recipientCC = $recipientCC;
    }

    /**
     * @return array
     */
    public function getRecipientCC()
    {
        return $this->recipientCC;
    }

    /**
     * @param array $recipientBCC
     */
    public function setRecipientBCC($recipientBCC)
    {
        $this->recipientBCC = $recipientBCC;
    }

    /**
     * @return array
     */
    public function getRecipientBCC()
    {
        return $this->recipientBCC;
    }

    /**
     * @param array $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return array
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string|null $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param \Pirastru\FormBuilderBundle\Entity\json $json
     */
    public function setJson($json)
    {
        $this->json = $json;
    }

    /**
     * @return \Pirastru\FormBuilderBundle\Entity\json
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @param array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return string|null
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @param string|null $replyTo
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
    }

    /**
     * @return ArrayCollection|FormBuilderSubmission[]
     */
    public function getSubmissions()
    {
        return $this->submissions;
    }

    /**
     * @param $submissions
     * @return self
     */
    public function setSubmissions($submissions): self
    {
        $this->submissions = $submissions;

        return $this;
    }

    /**
     * @param FormBuilderSubmission $submission
     * @return self
     */
    public function addSubmission(Submission $submission): self
    {
        if (!$this->submissions->contains($submission)) {
            $this->submissions->add($submission);
        }

        return $this;
    }

    /**
     * @param FormBuilderSubmission $submission
     * @return self
     */
    public function removeSubmission(Submission $submission): self
    {
        if ($this->submissions->contains($submission)) {
            $this->submissions->removeElement($submission);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isPersistable(): bool
    {
        return $this->persistable;
    }

    /**
     * @param bool $persistable
     * @return self
     */
    public function setPersistable(bool $persistable): self
    {
        $this->persistable = $persistable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExportable(): bool
    {
        return $this->exportable;
    }

    /**
     * @param bool $exportable
     * @return self
     */
    public function setExportable(bool $exportable): self
    {
        $this->exportable = $exportable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMailable(): bool
    {
        return $this->mailable;
    }

    /**
     * @param bool $mailable
     * @return self
     */
    public function setMailable(bool $mailable): self
    {
        $this->mailable = $mailable;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubmissionTitle(): ?string
    {
        return $this->submissionTitle;
    }

    /**
     * @param string|null $submissionTitle
     * @return FormBuilder
     */
    public function setSubmissionTitle(?string $submissionTitle): FormBuilder
    {
        $this->submissionTitle = $submissionTitle;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubmissionText(): ?string
    {
        return $this->submissionText;
    }

    /**
     * @param string|null $submissionText
     * @return FormBuilder
     */
    public function setSubmissionText(?string $submissionText): FormBuilder
    {
        $this->submissionText = $submissionText;
        return $this;
    }
}
