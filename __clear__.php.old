<?
if (isset($_GET["i_realy_know_what_i_doing"])) {
  include_once "mysql.class.php";
  include_once "config.php";

  $admin_default_email = "admin@office.local";
  if (TEST_MODE) {
    $admin_default_password = TEST_PASSWORD;
  } else {
    $admin_default_password = "111111";
  }
  $super_admin_default_email = "sadmin@sessi-onlain.ru";
  $super_admin_default_password = "222222";

  $clr_tables = array(
    "activity_logs",
    "authors_offers",
    "clients",
    "clients_changes_history",
    "data_filials",
    "data_users",
    "data_visits",
    "filial_to_city",
    "login_hst",
    "messages",
    "orders",
    "orders_changes_history",
    "order_files"
  );

  print SITE_ROOT . "<br>";
  foreach ($clr_tables as $nm) {
    $db->query("SELECT * FROM ofc_" . $nm);
    print "count '" . $nm . "' - " . mysql_num_rows($db->_res) . "<br>";
    $db->query("TRUNCATE TABLE ofc_" . $nm);
  }

  $db->query("INSERT INTO ofc_data_filials (`user_id`,`name`,`default`) VALUES (1,'Default',1)");

  $db->query("INSERT INTO ofc_data_users (`filial_id`, `fio`, `hpwd`, `email`, `group_id`, `password`) VALUES (1,'Administrator','" . md5($admin_default_password . $admin_default_email) . "','" . $admin_default_email . "',1,'" . $admin_default_password . "')");
  $db->query("INSERT INTO `ofc_data_users` (`filial_id`, `fio`, `hpwd`, `email`, `telnum`, `cont`, `group_id`, `rights`, `last_act`, `last_login`, `comments`, `blocked`, `conf_ordfld`, `conf_visfltr`, `conf_sotrfltr`, `conf_ordfltr`, `payment_requisites`, `conf_sotrfld`, `password`, `conf_visitfld`, `black_list`, `conf_blacklistfltr`, `conf_clientfltr`) VALUES
(0, 'Системный администратор', '" . md5($super_admin_default_password . $super_admin_default_email) . "', '" . $super_admin_default_email . "', '', '', 0, '', 0, '', '', 0, '', '', '', '', '', '', '', '', 0, '', '');");
} else {
  print "Clear";
}

?>