<?php

namespace Drupal\wmpage_cache\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Entity\EntityInterface;

class MainEntityAlterEvent extends Event
{
    /** @var EntityInterface|null */
    protected $entity;

    public function __construct(?EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): ?EntityInterface
    {
        return $this->entity;
    }

    public function setEntity(?EntityInterface $entity): void
    {
        $this->entity = $entity;
    }
}
