<?php
//client_src
$client_sources[0] = array();
$client_sources[1] = array("name"=>"интернет");
$client_sources[2] = array("name"=>"от знакомых");

$data_courses[0]=array('name' => 'не указан');
$data_courses[1]=array("name"=>"1");
$data_courses[2]=array("name"=>"2");
$data_courses[3]=array("name"=>"3");
$data_courses[4]=array("name"=>"4");
$data_courses[5]=array("name"=>"5");
$data_courses[6]=array("name"=>"6");
$data_courses[7]=array("name"=>"Аспирантура");

$data_practica[0] = array('name' => '');
$data_practica[1] = array("name"=>"0%");
$data_practica[2] = array("name"=>"50%");
$data_practica[3] = array("name"=>"100%");

$data_oform_fonts[] = "Times New Roman";
$data_oform_fonts[] = "Arial";
$data_oform_fonts[] = "Tahoma";
$data_oform_fonts[] = "Verdana";
$data_oform_fonts[] = "Courier New";
$data_oform_fonts[] = "другой шрифт";

$data_oform_interv[] = "Полуторный";
$data_oform_interv[] = "Одинарный";
$data_oform_interv[] = "Двойной";

$data_oform_links[] = "нет";
$data_oform_links[] = "внутристрочные";
$data_oform_links[] = "подстрочные";

$data_oform_numpos[] = "снизу посередине";
$data_oform_numpos[] = "снизу справа";
$data_oform_numpos[] = "снизу слева";
$data_oform_numpos[] = "сверху посередине";
$data_oform_numpos[] = "сверху справа";
$data_oform_numpos[] = "сверху слева";
$data_oform_numpos[] = "нет";

$data_ordertakemethod[0] = "-не указано-";
$data_ordertakemethod[1] = "лично";
$data_ordertakemethod[2] = "по телефону";
$data_ordertakemethod[3] = "через интернет";
$data_ordertakemethod[4] = "через форму заказа";

//groups
need_data('data_groups', 'roles');

$data_mails[5] = array("mails"=>array(1,2,3,4));
$data_mails[6] = array("mails"=>array(1,2,3,4));

$GLOBALS["data_mails"] = $data_mails;
$GLOBALS["data_courses"] = $data_courses;
$GLOBALS["data_practica"] = $data_practica;
$GLOBALS["data_oform_fonts"] = $data_oform_fonts;
$GLOBALS["data_oform_interv"] = $data_oform_interv;
$GLOBALS["data_oform_links"] = $data_oform_links;
$GLOBALS["data_oform_numpos"] = $data_oform_numpos;
$GLOBALS["data_ordertakemethod"] = $data_ordertakemethod;
$GLOBALS["data_author_payment_status"] = array('не оплачено', 'оплачено');

$GLOBALS["ofc_currency"] = "руб.";

define('PASSWORD_MIN_CHARS', 6);
define('PASSWORD_MAX_CHARS', 20);
define('PERMISSION_DENIED', 'У вас недостаточно прав');