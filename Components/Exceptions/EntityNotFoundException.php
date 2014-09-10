<?php

namespace Components\Exceptions;

class EntityNotFoundException extends Exception {
  private $id;
  private $entity;

  public function __construct($id, $entity) {
    $msg = sprintf('Entity "%s" with id "%s" not found.', $entity, $id);

    parent::__construct($msg);

    $this->id = $id;
    $this->entity = $entity;
  }

  public function getId() {
    return $this->id;
  }

  public function getEntity() {
    return $this->entity;
  }
}
