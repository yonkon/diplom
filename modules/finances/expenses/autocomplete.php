<?php

use Components\Classes\db;

if (!empty($_REQUEST['term'])) {
  echo json_encode(array_unique(db::get_single_values_array("SELECT `name` FROM " . TABLE_EXPENSES . " WHERE `name` LIKE '%" . db::input($_REQUEST['term']) . "%'")));
  die;
}