<?php

const ORD_SUBSECTION_NEW = 1;
const ORD_SUBSECTION_LIST = 2;
const ORD_SUBSECTION_ARCHIVE = 3;

$n = $GUI->mmenu->selected->selected->section;
$GUI->tmpls[] = $active_module_root . $n . ".tmpl.php";

page_ScriptNeed("scripts.js", "modules/ord");
require_once('functions.php');

switch ($n) {
  case ORD_SUBSECTION_NEW:
    //add
    include("new.inc.php");
    break;
  case ORD_SUBSECTION_LIST:
    // list
    include("list.inc.php");
    break;
  case ORD_SUBSECTION_ARCHIVE:
    include("archive.inc.php");
    break;

  case 'instant_edit':
    require_once('instant_edit_handler.php');
    break;

  case 'autocomplete':
    require_once('autocomplete.php');
    break;

  case 'authors_disciplines':
    require_once('authors_disciplines.php');
    break;
}