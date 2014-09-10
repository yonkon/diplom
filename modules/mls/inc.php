<?php

const MLS_SELECTED_NEW      = 1;
const MLS_SELECTED_INBOX    = 2;
const MLS_SELECTED_OUTBOX   = 3;
const MLS_SELECTED_BASKET   = 4;
const MLS_SELECTED_PROBLEMS = 5;

$n = $GUI->mmenu->selected->selected->section;
$GUI->tmpls[] = $active_module_root . "1.tmpl.php";
  include_once("functions.php");

if (!empty($_GET['show_history'])) {
  include("mail_history.php");
} else {
  switch ($n) {
    case MLS_SELECTED_NEW:
      include("inc_new.php");
      break;

    case MLS_SELECTED_INBOX:
    default:
      include("inc_in.php");
      break;

    case MLS_SELECTED_OUTBOX:
      include("inc_out.php");
      break;

    case MLS_SELECTED_BASKET:
      include("inc_basket.php");
      break;

    case MLS_SELECTED_PROBLEMS:
      include("inc_problems.php");
      break;
  }
}