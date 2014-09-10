<?php

namespace Components\Exceptions;

class DatabaseErrorException extends Exception {
  public function __construct() {
    parent::__construct();
  }
}
