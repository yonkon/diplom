<?

require_once('../includes/application_top.php');
require_once(DIR_FS_FRAME . 'func.php');

if (!isset($_REQUEST["action"])) {
  die("-1");
}

//die("" . print_r($_REQUEST, true) );

switch($_REQUEST["action"]) {
  case 'check_email':
    if (auth_client($_REQUEST["email"])) {
      die($_SESSION["frame"]["client"]["id"]);
    } else {
      die(0);
    }
    break;

  default:
    die("-1");
    break;
}
die("-1");
