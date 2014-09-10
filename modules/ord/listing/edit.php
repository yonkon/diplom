<?php

use Components\Classes\Disciplines;
use Components\Entity\Discipline;

page_scriptNeed('select2.min.js', '/js/select2/');
page_styleNeed('select2.css', '/js/select2/');

//    $GUI->Vars["page_hdr"] = "Правим содержание, управляем датами и ценой для клиента.";
page_scriptNeed('jquery-ui-1.10.3.custom.min.js', '/js/jquery-ui/js/');
page_styleNeed('jquery-ui-1.10.3.custom.min.css', '/js/jquery-ui/css/ui-lightness/');

$h = 1090;

$frm = $GUI->Form("Редактирование заказа №" . $order_id, 650);

$showOtdelKcomment = is_otdel_K($_SESSION["user"]["data"]['id']) || is_director($_SESSION["user"]["data"]['id']) || is_manager($_SESSION["user"]["data"]['id']);

if ($showOtdelKcomment) {
  $h = 1130;
} else {
  $h = 930;
}

$frm->height = $h;
$frm->VLine(10, $h - 80, 630);
$frm->Button("Сохранить", 205, $h - 60, 100, true);
$frm->OnExecute = "edit_order";
$b = $frm->Button("К списку", 345, $h - 60, 100);
$b->Event = "document.location.href=\"?section=ord&subsection=2\"; return false;";

$t = $frm->Hidden($order_id);
$t->linkName = 'id';

$ypos = 10;

kln_search_modal();
$frm->Label("Клиент", 10, $ypos);
$ypos += 20;
$s = $frm->Select(10, $ypos, 450, array(0 => "-выберите-") + kln_getlist(), "", $order_info['klient_id']); //0
$s->linkName = "klient";
$s->AddValidator(new CGUI_VALIDATOR_NOZERO);
$b = $frm->Button("Найти", 480, $ypos - 2, 70);
$b->Event = 'jQuery("#' . $GUI->Vars["kln_search_modal_form"]->idname . '").modal();';
page_AddScriptText("custom_klient_select_event = function(id){ jQuery('#" . $s->idname . "').val(id); };");

$ypos += 30;

$b = $frm->Button("Инфо", 560, $ypos - 32, 70);
$b->Event = 'var id= jQuery("#' . $s->idname . '").val(); if(id!=0) window.open("?section=kln&subsection=2&edit="+id);';

$frm->VLine(10, $ypos, 630);
$ypos += 10;

need_data("data_vuz");
need_data("data_discip");
need_data("data_payments");

$frm->Label("ВУЗ", 10, $ypos);
$frm->Label("ВУЗ (свой вариант)", 330, $ypos);
$ypos += 20;
$d = array();
$d[0] = "-свой вариант-";
foreach ($data_vuz as $vk => $vv) {
  $d[$vk] = $vv["sname"] . " (" . $vv["name"] . ")";
}
$s = $frm->Select(10, $ypos, 300, $d, "", $order_info['vuz_id']); //1
$s->linkName = "vuz";
$t = $frm->Text(330, $ypos, 300, $order_info["vuz_user"]); //2
$t->linkName = "vuz_usr";
$s->AddJsEvent("change", "order_filter_reset('" . $s->idname . "', '" . $t->idname . "');");
$t->AddJsEvent("keyup", "order_filter_list('" . $s->idname . "', '" . $t->idname . "');");

$ypos += 30;

$frm->Label("Курс", 10, $ypos);
$frm->Label("Вид работы", 150, $ypos);
$frm->Label("Вид работы (свой вариант)", 370, $ypos);

$ypos += 20;
$s = $frm->Select(10, $ypos, 120, $data_courses, "name", $order_info['kurs']); //3
$s->linkName = "kurs";
//$s->AddValidator(new CGUI_VALIDATOR_NOZERO);

krsort($data_worktypes);
$s = $frm->Select(150, $ypos, 200, array(0 => array("name" => "-свой вариант-")) + $data_worktypes, "name", $order_info["type_id"]); // 4
$s->linkName = "work";
$t = $frm->Text(370, $ypos, 260, $order_info["type_user"]); // 5
$t->linkName = "work_usr";
$ypos += 30;
$s->AddJsEvent("change", " jQuery('#" . $t->idname . "').val(''); ");
$t->AddJsEvent("keyup", " jQuery('#" . $s->idname . "').val(0); ");

$frm->Label("Направление (факультет)", 10, $ypos);
$frm->Label("Дисциплина", 190, $ypos);
$ypos += 20;

$s = $frm->Select(10, $ypos, 160, array(0 => array("name" => "-выберите-")) + $data_napravl, "name", $order_info["napr_id"]); //6
$s->linkName = "napr";

$t = $frm->Hidden(isset($data_discip[$order_info["disc_id"]]) ? $order_info["disc_id"] : $order_info["disc_user"]); // 8
$t->linkName = "disc_usr";
$t->class = 'discipline_select2';

$disciplines = Discipline::findAll();
foreach($disciplines as &$discipline) {
  $discipline['authors_qt'] = Disciplines::getAuthorsQt($discipline['id']);
}
unset($discipline);
page_AddScriptText('var disciplines = ' . json_encode($disciplines) . ';');

$ypos += 30;

$frm->Label("Тема работы", 10, $ypos);
$ypos += 20;
$t = $frm->TextArea(10, $ypos, 625, 60, $order_info["subject"]); //9
$t->linkName = "subj";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(512));
$ypos += 70;

$frm->Label("Дата сдачи", 10, $ypos);
$frm->Label("Практика", 110, $ypos);
$frm->Label("Число страниц", 230, $ypos);
$frm->Label("Число источников", 360, $ypos);
$ypos += 20;
$t = $frm->DatePic(10, $ypos, 80, $order_info['time_kln']); //10
$t->linkName = "date";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_DDMMYYYY);

$s = $frm->Select(110, $ypos, 100, $data_practica, "name", $order_info['prakt_pc']); //11
$s->linkName = "prakt";
//$s->AddValidator(new CGUI_VALIDATOR_NOZERO);

$frm->Label("от", 230, $ypos);
$frm->Label("до", 290, $ypos);
$t = $frm->Tracker(250, $ypos, 30, $order_info['pages_min']); //12
$t->linkName = "pgmin";
$t->MaxVal = 999;
//$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
//$t->AddValidator(new CGUI_VALIDATOR_NOZERO);
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(3));
$t = $frm->Tracker(310, $ypos, 30, $order_info['pages_max']); //13
$t->linkName = "pgmax";
$t->MaxVal = 999;
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(3));

$frm->Label("от", 360, $ypos);
$frm->Label("до", 420, $ypos);
$t = $frm->Tracker(380, $ypos, 30, $order_info["src_min"]); //14
$t->linkName = "srcmin";
$t->MaxVal = 999;
//$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
//$t->AddValidator(new CGUI_VALIDATOR_NOZERO);
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(3));
$t = $frm->Tracker(440, $ypos, 30, $order_info["src_max"]); //15
$t->linkName = "srcmax";
$t->MaxVal = 999;
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(3));

$btn_oform = $frm->Button("Оформление", 500, $ypos - 2, 100);

$ypos += 30;

$frm->Label("Требования", 10, $ypos);
$ypos += 20;
$t = $frm->TextArea(10, $ypos, 625, 100, $order_info['about_kln']); //16
$t->linkName = "treb";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$ypos += 115;

$frm->Label("Комментарий менеджера", 10, $ypos);
$frm->Label("Следующий контакт", 515, $ypos);
$ypos += 20;

$t = $frm->TextArea(10, $ypos, 490, 80, $order_info['about_mng']); //17
$t->linkName = "rem";
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(1000));

$t = $frm->DatePic(515, $ypos, 120, $order_info['next_rel_date']); //10
$t->linkName = "next_rel_date";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_DDMMYYYY);

$ypos += 100;

if ($showOtdelKcomment) {
  $frm->Label("Комментарий ОК", 10, $ypos);

  $ypos += 20;

  $t = $frm->TextArea(10, $ypos, 490, 80, $order_info['ok_comment']);
  $t->linkName = "ok_comment";
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(1000));

  $ypos += 100;
}

$frm->Label("Заказ принят", 10, $ypos);
$frm->Label("Гонорар автора, " . $ofc_currency, 230, $ypos);

$ypos += 20;

$s = $frm->Select(10, $ypos, 200, $data_ordertakemethod, "", $order_info['from_id']); //19
$s->linkName = "take";

$t = $frm->Text(230, $ypos, 115, $order_info["cost_auth"]);
$t->linkName = "cost_auth";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_09);

$ypos += 30;

$frm->Label("Цена для клиента, " . $ofc_currency, 10, $ypos);
$frm->Label("Оплачено автору, " . $ofc_currency, 230, $ypos);

$ypos += 20;

$t = $frm->Text(10, $ypos, 115, $order_info["cost_kln"]);
$t->linkName = "cost";
$t = $frm->Text(230, $ypos, 115, $order_info["author_paid"]);
$t->linkName = "author_paid";
$t->AddValidator(new CGUI_VALIDATOR_09);

$ypos += 30;

$frm->Label("Оплачено клиентом, " . $ofc_currency, 10, $ypos);

$ypos += 20;

$t = $frm->Text(10, $ypos, 115, $order_info["oplata_kln"]);
$t->linkName = "oplata_kln";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_09);

$ypos += 30;

$frm->Label("Способ оплаты", 10, $ypos);

$ypos += 20;

$s = $frm->Select(10, $ypos, 200, array(0 => array("name" => "-выберите-")) + $data_payments, "name", $order_info['payment_id']); //18
$s->linkName = "opl";
$s->AddValidator(new CGUI_VALIDATOR_NOZERO);

$ypos += 30;

$frm->Label('Долг перед фирмой', 10, $ypos);

$ypos += 20;

$t = $frm->Text(10, $ypos, 200, calculate_debt_to_company($order_info["cost_kln"], $order_info["cost_auth"], $order_info['filial_id']));
$t->Disabled = true;

$ypos += 30;

$statuses = array('Не определен');
foreach (get_orders_statuses() as $status) {
  $statuses[$status['id']] = $status['status_name'];
}
$frm->Label("Статус заказа", 10, $ypos);
$frm->Label("Далее", 445, $ypos);

$ypos += 20;

$s = $frm->Select(10, $ypos, 200, $statuses, "", $order_info['status_id']); //21
$s->linkName = "status_id";

$s = $frm->Select(445, $ypos, 200, array(
  0 => "к списку заказов",
  1 => "правка заказа",
  2 => "повторить заказ"
)); //22
$s->linkName = "next";

$oform = unserialize($order_info['oform']);

if (array_key_exists($oform[0], $data_oform_fonts)) {
  $fontname_index = $oform[0];
} else {
  $fontname_index = 0;
}

$oform_fntnm = $frm->Hidden($data_oform_fonts[$fontname_index]); //font name 23
$oform_fntnm->linkName = "fontnm";
$f = $frm->Hidden($oform[1]); // font size 24
$f->linkName = "fontsz";
$f = $frm->Hidden($data_oform_interv[$oform[2]]); // interval 25
$f->linkName = "interval";
$f = $frm->Hidden($data_oform_links[$oform[3]]); // links 26
$f->linkName = "links";
$f = $frm->Hidden($oform[4]); // pole top 27
$f->linkName = "pole_t";
$f = $frm->Hidden($oform[5]); // pole bottom 28
$f->linkName = "pole_b";
$f = $frm->Hidden($oform[6]); // pole left 29
$f->linkName = "pole_l";
$f = $frm->Hidden($oform[7]); // pole right 30
$f->linkName = "pole_r";
$f = $frm->Hidden($data_oform_numpos[$oform[8]]); // pagenums 31
$f->linkName = "pagenums";
$btn_oform->Event = "show_oform_editor('" . $frm->idname . "', " . $oform_fntnm->id . ", 9);";

$h = 330;
$frmo = $GUI->ModalForm("Оформление", 300, $h);
$frmo->NuSubmit = true;
$frmo->Rename("oform_editor");

$frmo->Label("Шрифт", 10, 10);
$frmo->Select(60, 9, 230, $data_oform_fonts, '', $data_oform_fonts[$fontname_index]);

$frmo->Label("Размер шрифта, pt", 10, 40);
$t = $frmo->Text(130, 39, 80, 14, $oform[1]);

$frmo->Label("Интервал", 10, 70);
$frmo->Select(75, 69, 215, $data_oform_interv, '', $data_oform_fonts[$oform[2]]);

$frmo->Label("Ссылки", 10, 100);
$frmo->Select(75, 99, 215, $data_oform_links, '', $data_oform_links[$oform[3]]);

$frmo->Label("Поля, мм", 10, 130);
$frmo->Label("верхнее", 15, 150);
$frmo->Label("нижнее", 150, 150);
$frmo->Label("левое", 15, 180);
$frmo->Label("правое", 150, 180);

$frmo->Text(75, 150, 60, $oform[4]);
$frmo->Text(210, 150, 60, $oform[5]);
$frmo->Text(75, 180, 60, $oform[6]);
$frmo->Text(210, 180, 60, $oform[7]);

$frmo->Label("Нумерация", 10, 215);
$frmo->Select(85, 215, 200, $data_oform_numpos, '', $data_oform_numpos[$oform[8]]);

$frmo->VLine(10, $h - 80, 280);

$b = $frmo->Button("Принять", 60, $h - 60, 80);
$b->Event = "closeok_oform_editor();";

$b = $frmo->Button("Отмена", 160, $h - 60, 80);
$b->Event = "jQuery.modal.close();";