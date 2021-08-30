<?php

namespace Drupal\wmpage_cache\Event;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

class MainEntityAlterEvent extends Event
{
    /** @var EntityInterface|null */
    protected $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}
