<?php
namespace Pirastru\FormBuilderBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class MailEvent extends Event
{
    /**
     * @var \Swift_Message
     */
    private $message;

    /**
     * MailEvent constructor.
     * @param \Swift_Message $message
     */
    public function __construct(\Swift_Message $message)
    {
        $this->message = $message;
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
