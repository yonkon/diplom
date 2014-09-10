<?php

namespace Components\Exceptions;

class InvalidArgumentException extends Exception {
  private $entity;

  public function __construct($entity) {
    $msg = sprintf('Entity "%s" except another arguments.', $entity);

    parent::__construct($msg);

    $this->entity = $entity;
  }

  public function getEntity() {
    return $this->entity;
  }
}
