<?php

namespace Pirastru\FormBuilderBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class FormDataEvent extends Event
{
    public function __construct(protected array $data)
    {}

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
