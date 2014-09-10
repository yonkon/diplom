<?php

namespace Components\Exceptions;

class AccessDeniedException extends Exception {
  public function __construct() {
    parent::__construct();
  }
}
