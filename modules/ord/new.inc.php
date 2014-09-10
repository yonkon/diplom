<?php

use Components\Entity\Discipline;
use Components\Classes\Disciplines;

//if (!user_has_right("sotr_w")) page_ReloadSec();
page_scriptNeed('jquery-ui-1.10.3.custom.min.js', '/js/jquery-ui/js/');
page_styleNeed('jquery-ui-1.10.3.custom.min.css', '/js/jquery-ui/css/ui-lightness/');

page_scriptNeed('select2.min.js', '/js/select2/');
page_styleNeed('select2.css', '/js/select2/');

$defdata = array();
$defdata["klient"] = false;
$defdata["vuz"] = 0;
$defdata["vuz_usr"] = "";
$defdata["kurs"] = 0;
$defdata["work"] = 0;
$defdata["work_usr"] = "";
$defdata["napr"] = 0;
$defdata["disc"] = 0;
$defdata["disc_usr"] = "";
$defdata["date"] = "";
$defdata["prakt"] = 0;
$defdata["pgmin"] = "";
$defdata["pgmax"] = "";
$defdata["srcmin"] = "";
$defdata["srcmax"] = "";
$defdata["opl"] = 0;
$defdata["take"] = 0;
$defdata["skid"] = 0;
$defdata["skidt"] = 0;
$defdata["cost"] = 0;
$defdata["fontnm"] = 14;
$defdata["fontsz"] = 14;
$defdata["interval"] = 0;
$defdata["links"] = 0;
$defdata["pole_t"] = 20;
$defdata["pole_b"] = 20;
$defdata["pole_l"] = 30;
$defdata["pole_r"] = 15;
$defdata["pagenums"] = 0;
$defdata["next_rel_date"] = "";
$defdata["treb"] = "";
$defdata["rem"] = "";
$defdata["cost_auth"] = "";
$defdata["subj"] = "";

if (isset($_SESSION["repeat_order"])) {
  $defdata = $_SESSION["repeat_order"];
  unset($_SESSION["repeat_order"]);
}

$h = 800;

$frm = $GUI->Form("Добавить заказ", 650, $h);
$frm->VLine(10, $h - 80, 630);
$frm->Button("Добавить", 205, $h - 60, 100, true);
$frm->OnExecute = "addorder_exec";
$b = $frm->Button("К списку", 345, $h - 60, 100);
$b->Event = "document.location.href=\"?section=ord&subsection=2\"; return false;";

$ypos = 10;
$klient = false;

if (isset($_REQUEST["kln_id"])) {
  $id = intval($_REQUEST["kln_id"]);
  $klient = kln_get($id);
}

$s = false;
if (!$klient) {
  kln_search_modal();
  $frm->Label("Клиент", 10, $ypos);
  $ypos += 20;
  $s = $frm->Select(10, $ypos, 450, array(0 => "-выберите-") + kln_getlist(), "", $defdata["klient"]); //0
  $s->linkName = "klient";
  $s->AddValidator(new CGUI_VALIDATOR_NOZERO);
  $b = $frm->Button("Найти", 480, $ypos - 2, 70);
  $b->Event = 'jQuery("#' . $GUI->Vars["kln_search_modal_form"]->idname . '").modal();';
  page_AddScriptText("custom_klient_select_event = function(id){ jQuery('#" . $s->idname . "').val(id); };");
  $ypos += 30;
} else {
  $frm->Label("Клиент: <b>" . $klient["fio"] . "</b>", 10, $ypos);
  $ypos += 20;
  $frm->Label("Почта: <b>" . $klient["email"] . "</b>", 10, $ypos);
  $ypos += 20;
  $frm->Label("Телефон: <b>" . $klient["telnum"] . "</b>", 10, $ypos);
  $ypos += 20;
  $s = $frm->Hidden($klient["id"]); //0
  $s->linkName = "klient";
}

$b = $frm->Button("Инфо", 560, $ypos - 32, 70);
$b->Event = 'var id= jQuery("#' . $s->idname . '").val(); if(id!=0) window.open("?section=kln&subsection=2&edit="+id);';

$frm->VLine(10, $ypos, 630);
$ypos += 10;

need_data("data_vuz");
need_data("data_discip");
need_data("data_payments");
need_data("data_worktypes");
need_data("data_napravl");

$frm->Label("ВУЗ", 10, $ypos);
$frm->Label("ВУЗ (свой вариант)", 330, $ypos);
$ypos += 20;
$d = array();
$d[0] = "-свой вариант-";
foreach ($data_vuz as $vk => $vv) {
  $d[$vk] = $vv["sname"] . " (" . $vv["name"] . ")";
}
$s = $frm->Select(10, $ypos, 300, $d, "", $defdata["vuz"]); //1
$s->linkName = "vuz";
$t = $frm->Text(330, $ypos, 300, $defdata["vuz_usr"]); //2
$t->linkName = "vuz_usr";
$s->AddJsEvent("change", "order_filter_reset('" . $s->idname . "', '" . $t->idname . "');");
$t->AddJsEvent("keyup", "order_filter_list('" . $s->idname . "', '" . $t->idname . "');");
$ypos += 30;

$frm->Label("Курс", 10, $ypos);
$frm->Label("Вид работы", 150, $ypos);
$frm->Label("Вид работы (свой вариант)", 370, $ypos);

$ypos += 20;
$s = $frm->Select(10, $ypos, 120, $data_courses, "name", $defdata["kurs"]); //3
$s->linkName = "kurs";
//$s->AddValidator(new CGUI_VALIDATOR_NOZERO);

krsort($data_worktypes);
$s = $frm->Select(150, $ypos, 200, array(0 => array("name" => "-свой вариант-")) + $data_worktypes, "name", $defdata["work"]); // 4
$s->linkName = "work";
$t = $frm->Text(370, $ypos, 260, $defdata["work_usr"]); // 5
$t->linkName = "work_usr";
$ypos += 30;
$s->AddJsEvent("change", " jQuery('#" . $t->idname . "').val(''); ");
$t->AddJsEvent("keyup", " jQuery('#" . $s->idname . "').val(0); ");

$frm->Label("Направление (факультет)", 10, $ypos);
$frm->Label("Дисциплина", 190, $ypos);
$ypos += 20;

$s = $frm->Select(10, $ypos, 160, array(0 => array("name" => "-выберите-")) + $data_napravl, "name", $defdata["napr"]); //6
$s->linkName = "napr";

$t = $frm->Hidden($defdata["disc_usr"]); // 8
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
$t = $frm->TextArea(10, $ypos, 625, 60, $defdata['subj']); //9
$t->linkName = "subj";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(512));
$ypos += 70;

$frm->Label("Дата сдачи", 10, $ypos);
$frm->Label("Практика", 110, $ypos);
$frm->Label("Число страниц", 230, $ypos);
$frm->Label("Число источников", 360, $ypos);
$ypos += 20;

$t = $frm->DatePic(10, $ypos, 80, utils_cvt_date2i($defdata["date"])); //10
$t->linkName = "date";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_DDMMYYYY);

$s = $frm->Select(110, $ypos, 100, $data_practica, "name", $defdata["prakt"]); //11
$s->linkName = "prakt";
//$s->AddValidator(new CGUI_VALIDATOR_NOZERO);

$frm->Label("от", 230, $ypos);
$frm->Label("до", 290, $ypos);
$t = $frm->Tracker(250, $ypos, 30, $defdata["pgmin"]); //12
$t->linkName = "pgmin";
$t->MaxVal = 999;
//$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
//$t->AddValidator(new CGUI_VALIDATOR_NOZERO);
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(3));
$t = $frm->Tracker(310, $ypos, 30, $defdata["pgmax"]); //13
$t->linkName = "pgmax";
$t->MaxVal = 999;
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(3));

$frm->Label("от", 360, $ypos);
$frm->Label("до", 420, $ypos);
$t = $frm->Tracker(380, $ypos, 30, $defdata["srcmin"]); //14
$t->linkName = "srcmin";
$t->MaxVal = 999;
//$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
//$t->AddValidator(new CGUI_VALIDATOR_NOZERO);
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(3));
$t = $frm->Tracker(440, $ypos, 30, $defdata["srcmax"]); //15
$t->linkName = "srcmax";
$t->MaxVal = 999;
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(3));

$btn_oform = $frm->Button("Оформление", 500, $ypos - 2, 100);

$ypos += 30;

$frm->Label("Требования", 10, $ypos);
$ypos += 20;
$t = $frm->TextArea(10, $ypos, 625, 100, $defdata['treb']); //16
$t->linkName = "treb";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$ypos += 115;

$frm->Label("Комментарий менеджера", 10, $ypos);
$frm->Label("Следующий контакт", 515, $ypos);
$ypos += 20;

$t = $frm->TextArea(10, $ypos, 490, 80, $defdata['rem']); //17
$t->linkName = "rem";
$t->AddValidator(new CGUI_VALIDATOR_MAXLEN(1000));

$t = $frm->DatePic(515, $ypos, 120, utils_cvt_date2i($defdata["next_rel_date"])); //10
$t->linkName = "next_rel_date";
$t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
$t->AddValidator(new CGUI_VALIDATOR_DDMMYYYY);

$ypos += 100;

$frm->Label("Способ оплаты", 10, $ypos);
$frm->Label("Заказ принят", 230, $ypos);
$frm->Label("Скидка", 445, $ypos);
$ypos += 20;
$s = $frm->Select(10, $ypos, 200, array(0 => array("name" => "-выберите-")) + $data_payments, "name", $defdata["opl"]); //18
$s->linkName = "opl";
$s->AddValidator(new CGUI_VALIDATOR_NOZERO);
$s = $frm->Select(230, $ypos, 200, $data_ordertakemethod, "", $defdata["take"]); //19
$s->linkName = "take";
$t = $frm->Text(445, $ypos, 60, $defdata["skid"]); //20
$t->linkName = "skid";
$s = $frm->Select(510, $ypos, 50, array($ofc_currency, "%"), "", $defdata["skidt"]); //21
$s->linkName = "skidt";
$ypos += 30;


$frm->Label("Гонорар автора", 10, $ypos);
$frm->Label("Цена, " . $ofc_currency, 230, $ypos);
$frm->Label("Далее", 400, $ypos);

$ypos += 20;
$t = $frm->Text(10, $ypos, 115, $defdata["cost_auth"]);
$s->linkName = "cost_auth";

$t = $frm->Text(230, $ypos, 115, $defdata["cost"]);
$t->linkName = "cost";

$s = $frm->Select(400, $ypos, 200, array(0 => "к списку заказов", 1 => "правка заказа", 2 => "повторить заказ")); //22
$s->linkName = "next";

$oform_fntnm = $frm->Hidden($defdata["fontnm"]); //font name 23
$oform_fntnm->linkName = "fontnm";
$f = $frm->Hidden($defdata["fontsz"]); // font size 24
$f->linkName = "fontsz";
$f = $frm->Hidden($defdata["interval"]); // interval 25
$f->linkName = "interval";
$f = $frm->Hidden($defdata["links"]); // links 26
$f->linkName = "links";
$f = $frm->Hidden($defdata["pole_t"]); // pole top 27
$f->linkName = "pole_t";
$f = $frm->Hidden($defdata["pole_b"]); // pole bottom 28
$f->linkName = "pole_b";
$f = $frm->Hidden($defdata["pole_l"]); // pole left 29
$f->linkName = "pole_l";
$f = $frm->Hidden($defdata["pole_r"]); // pole right 30
$f->linkName = "pole_r";
$f = $frm->Hidden($defdata["pagenums"]); // pagenums 31
$f->linkName = "pagenums";

$btn_oform->Event = "show_oform_editor('" . $frm->idname . "', " . $oform_fntnm->id . ", 9);";

$h = 330;
$frmo = $GUI->ModalForm("Оформление", 300, $h);
$frmo->NuSubmit = true;
$frmo->Rename("oform_editor");

$frmo->Label("Шрифт", 10, 10);
$frmo->Select(60, 9, 230, $data_oform_fonts);

$frmo->Label("Размер шрифта, pt", 10, 40);
$t = $frmo->Text(130, 39, 80, 14);

$frmo->Label("Интервал", 10, 70);
$frmo->Select(75, 69, 215, $data_oform_interv);

$frmo->Label("Ссылки", 10, 100);
$frmo->Select(75, 99, 215, $data_oform_links);

$frmo->Label("Поля, мм", 10, 130);
$frmo->Label("верхнее", 15, 150);
$frmo->Label("нижнее", 150, 150);
$frmo->Label("левое", 15, 180);
$frmo->Label("правое", 150, 180);

$frmo->Text(75, 150, 60);
$frmo->Text(210, 150, 60);
$frmo->Text(75, 180, 60);
$frmo->Text(210, 180, 60);

$frmo->Label("Нумерация", 10, 215);
$frmo->Select(85, 215, 200, $data_oform_numpos);

$frmo->VLine(10, $h - 80, 280);

$b = $frmo->Button("Принять", 60, $h - 60, 80);
$b->Event = "closeok_oform_editor();";

$b = $frmo->Button("Отмена", 160, $h - 60, 80);
$b->Event = "jQuery.modal.close();";

// сноски внизу страницы (Шрифт 10pt, одинарный интервал).
// Поля: верхнее 20мм, нижнее 20мм, левое 30мм, правое 15мм.
// Нумерация страниц снизу посередине.