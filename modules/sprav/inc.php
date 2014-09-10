<?php

  const SUBSECTION_PAYMENT_METHODS      = 1;
  const SUBSECTION_NAPRAVL              = 2;
  const SUBSECTION_WORK_TYPES           = 3;
  const SUBSECTION_VUZ                  = 4;
  const SUBSECTION_DISCIPLINE           = 5;
  const SUBSECTION_SUBWAY_STATIONS      = 6;
  const SUBSECTION_GROUPS               = 7;
  const SUBSECTION_FILIAL_CITIES        = 8;
  const SUBSECTION_RELOAD_SECTION        = 9;
  const SUBSECTION_REFERRER_SYSTEM      = 10;
  const SUBSECTION_EMAIL_NOTIFICATIONS  = 11;

$n = $GUI->mmenu->selected->selected->section;

$GUI->tmpls[] = $active_module_root . "1.tmpl.php";

include_once('functions.php');

switch ($n) {
  case SUBSECTION_PAYMENT_METHODS: //opl
    require_once('payment_methods.php');
    break;

  case SUBSECTION_NAPRAVL: // napr
    require_once('napravl.php');
    break;

  case SUBSECTION_WORK_TYPES: // types
    require_once('worktypes.php');
    break;

  case SUBSECTION_VUZ: //VUZ
    require_once('vuz.php');
    break;

  case SUBSECTION_DISCIPLINE: //disc
    require_once('discipline.php');
    break;

  case SUBSECTION_SUBWAY_STATIONS: //stations
    require_once('subway_stations.php');
    break;

  case SUBSECTION_GROUPS: //groups
    require_once('roles.php');
    break;

  case SUBSECTION_FILIAL_CITIES:
    include_once('filial_cities.php');
    break;

  case SUBSECTION_RELOAD_SECTION:
    page_reloadSec();
    break;

  case SUBSECTION_REFERRER_SYSTEM:
    include_once('referrer_system.php');
    break;

  case SUBSECTION_EMAIL_NOTIFICATIONS:
    include_once('email_notifications.php');
    break;
}