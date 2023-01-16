<?php
namespace Pirastru\FormBuilderBundle\Event;

use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\Event;

class MailEvent extends Event
{
    public function __construct(private Email $message, private array $formData)
    {}

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function setFormData(array $formData): void
    {
        $this->formData = $formData;
    }

    public function getMessage(): Email
    {
        return $this->message;
    }

    public function setMessage(Email $message): void
    {
        $this->message = $message;
    }
}
