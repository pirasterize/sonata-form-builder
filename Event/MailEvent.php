<?php
namespace Pirastru\FormBuilderBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class MailEvent extends Event
{
    /**
     * @var \Swift_Message
     */
    private $message;

    /** @var array */
    private $formData;

    /**
     * MailEvent constructor.
     * @param \Swift_Message $message
     */
    public function __construct(\Swift_Message $message, array $formData)
    {
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * @param array $formData
     */
    public function setFormData($formData)
    {
        $this->formData = $formData;
    }

    /**
     * @return \Swift_Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param \Swift_Message $message
     */
    public function setMessage(\Swift_Message $message)
    {
        $this->message = $message;
    }
}
