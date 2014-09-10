<?php

use Components\Classes\db;

if (!empty($_REQUEST['term']) && !empty($_REQUEST['entity'])) {
  $result = array();
  switch($_REQUEST['entity']) {
    case 'disciplina':
      $result = db::get_single_values_array("SELECT `name` FROM " . TABLE_DISCIPLINE . " WHERE `name` LIKE '" . db::input($_REQUEST['term']) . "%' ORDER BY `name`");
      break;
  }

  echo json_encode(array_unique($result));
  die;
}