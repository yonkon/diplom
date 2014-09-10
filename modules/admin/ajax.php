<?php
require_once("../../includes/application_top.php");

if (!$_SESSION["user"]["auth"]) {
  die("запрещено");
}

if (isset($_POST['action']) && !empty($_POST['action'])) {
  switch ($_POST['action']) {
    case 'get_submodules':
      echo json_encode(get_submodules($_POST['module_id']));
      die;
      break;

    default:
      break;
  }
}