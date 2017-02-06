<?php

namespace Pirastru\FormBuilderBundle\Entity;

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
     * @var array
     *
     * @ORM\Column(name="submit", type="array", nullable=true)
     */
    private $submit;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->recipient = array();
        $this->recipientCC = array();
        $this->recipientBCC = array();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ? $this->getName() : 'Create a Form Builder';
    }

    /**
     * @param array $submit
     */
    public function setSubmit($submit)
    {
        $this->submit = $submit;
    }

    /**
     * @return array
     */
    public function getSubmit()
    {
        return $this->submit;
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
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
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
}
