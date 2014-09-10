<?php

use Components\Entity\Client;

//    $GUI->Vars["page_hdr"] = "Содержание заказа. Тут выводим данные о том, что от нас хочет заказчик.";
$frm = $GUI->Form("Содержание заказа №" . $order_id, 650);

$ypos = 10;

$is_author = is_author($_SESSION["user"]["data"]['id']);

if ($is_author) {
  $h = 800;
} else {
  $h = 1130;
}

$frm->VLine(10, $h - 80, 630);
$frm->height = $h;
$b = $frm->Button("К списку", 275, $h - 60, 100);
$b->Event = "document.location.href=\"?section=ord&subsection=2\"; return false;";

if (!$is_author) {
  $client = Client::find($order_info['klient_id']);

  $frm->Label("Клиент: <b>" . $client["fio"] . "</b>", 10, $ypos);
  $ypos += 20;
  $frm->Label("Почта: <b>" . $client["email"] . "</b>", 10, $ypos);
  $ypos += 20;
  $frm->Label("Телефон: <b>" . $client["telnum"] . "</b>", 10, $ypos);
  $ypos += 20;
  $s = $frm->Hidden($client["id"]); //0
  $s->linkName = "client";

  $b = $frm->Button("Инфо", 560, $ypos - 32, 70);
  $b->Event = 'var id= jQuery("#' . $s->idname . '").val(); if(id!=0) window.open("?section=kln&subsection=2&edit="+id);';

  $frm->VLine(10, $ypos, 630);

  $ypos += 10;
}

$frm->Label("ВУЗ", 10, $ypos);

$ypos += 20;

if (!empty($order_info['vuz_user'])) {
  $s = $frm->Text(10, $ypos, 300, $order_info['vuz_user']);
} else {
  $vuz = get_vuz_name($order_info['vuz_id']);
  $s = $frm->Text(10, $ypos, 300, $vuz['sname'] . '(' . $vuz['name'] . ')');
}
$s->linkName = "vuz";

$ypos += 30;

$frm->Label("Курс", 10, $ypos);
$frm->Label("Вид работы", 150, $ypos);

$ypos += 20;

$s = $frm->Text(10, $ypos, 120, $data_courses[$order_info['kurs']]['name']); //3
$s->linkName = "kurs";

if (!empty($order_info['type_user'])) {
  $s = $frm->Text(150, $ypos, 200, $order_info['type_user']); // 4
} else {
  $s = $frm->Text(150, $ypos, 200, get_worktype_name($order_info['type_id']));
}
$s->linkName = "worktype";

$ypos += 30;

$frm->Label("Направление (факультет)", 10, $ypos);
$frm->Label("Дисциплина", 190, $ypos);

$ypos += 20;

$s = $frm->Text(10, $ypos, 160, get_naprav_name($order_info['napr_id'])); //6
$s->linkName = "naprav";

if (!empty($order_info['disc_user'])) {
  $s = $frm->Text(190, $ypos, 200, $order_info['disc_user']); // 4
} else {
  $s = $frm->Text(190, $ypos, 200, get_discipline_name($order_info['disc_id']));
}
//7
$s->linkName = "disc";

$ypos += 30;

$frm->Label("Тема работы", 10, $ypos);

$ypos += 20;

$t = $frm->TextArea(10, $ypos, 625, 60, $order_info['subject']); //9
$t->linkName = "subject";

$ypos += 70;

$frm->Label("Дата сдачи", 10, $ypos);
$frm->Label("Практика", 150, $ypos);
$frm->Label("Число страниц", 230, $ypos);
$frm->Label("Число источников", 360, $ypos);
$ypos += 20;

if ($is_author) {
  $t = $frm->Text(10, $ypos, 120, format_date($order_info['time_auth'])); //10
  $t->linkName = "time_auth";
} else {
  $t = $frm->Text(10, $ypos, 120, format_date($order_info['time_kln'])); //10
  $t->linkName = "time_kln";
}

$s = $frm->Text(150, $ypos, 60, $data_practica[$order_info['prakt_pc']]['name']); //11
$s->linkName = "prakt_pc";

$frm->Label("от", 230, $ypos);
$frm->Label("до", 290, $ypos);
$t = $frm->Text(250, $ypos, 30, $order_info['pages_min']); //12
$t->linkName = "pages_min";

$t = $frm->Text(310, $ypos, 30, $order_info['pages_max']); //13
$t->linkName = "pages_max";

$frm->Label("от", 360, $ypos);
$frm->Label("до", 420, $ypos);
$t = $frm->Text(380, $ypos, 30, $order_info["src_min"]); //14
$t->linkName = "src_min";

$t = $frm->Tracker(440, $ypos, 30, $order_info["src_max"]); //15
$t->linkName = "src_max";

$btn_oform = $frm->Button("Оформление", 500, $ypos - 2, 100);

$ypos += 30;

$frm->Label("Требования", 10, $ypos);

$ypos += 20;

$t = $frm->TextArea(10, $ypos, 625, 100, $order_info['about_kln']); //16
$t->linkName = "about_kln";

$ypos += 115;

$frm->Label("Комментарий ОК", 10, $ypos);
$frm->Label("Дата комментария ОК", 515, $ypos);

$ypos += 20;

$t = $frm->TextArea(10, $ypos, 490, 80, $order_info['ok_comment']); //17
$t->linkName = "ok_comment";

$t = $frm->Text(515, $ypos, 120, format_date($order_info['ok_comment_date'], false, true)); //10
$t->linkName = "ok_comment_date";

$ypos += 100;

if (!$is_author) {
  $frm->Label("Комментарий менеджера", 10, $ypos);
  $frm->Label("Следующий контакт", 515, $ypos);

  $ypos += 20;

  $t = $frm->TextArea(10, $ypos, 490, 80, $order_info['about_mng']); //17
  $t->linkName = "about_mng";

  $t = $frm->Text(515, $ypos, 120, format_date($order_info['next_rel_date'])); //10
  $t->linkName = "next_rel_date";

  $ypos += 100;

  $frm->Label("Заказ принят", 10, $ypos);
  $frm->Label("Гонорар автора, " . $ofc_currency, 230, $ypos);

  $ypos += 20;

  $t = $frm->Text(10, $ypos, 200, $data_ordertakemethod[$order_info['from_id']]); //19
  $t->linkName = "from_id";
  $t = $frm->Text(230, $ypos, 115, $order_info["cost_auth"]);
  $t->linkName = "cost_auth";

  $ypos += 30;

  $frm->Label("Цена для клиента, " . $ofc_currency, 10, $ypos);
  $frm->Label("Оплачено автору, " . $ofc_currency, 230, $ypos);

  $ypos += 20;

  $t = $frm->Text(10, $ypos, 115, $order_info["cost_kln"]);
  $t->linkName = "cost_kln";
  $t = $frm->Text(230, $ypos, 115, $order_info["author_paid"]);
  $t->linkName = "author_paid";

  $ypos += 30;

  $frm->Label("Оплачено клиентом, " . $ofc_currency, 10, $ypos);

  $ypos += 20;

  $t = $frm->Text(10, $ypos, 115, $order_info["oplata_kln"]);
  $t->linkName = "oplata_kln";

  $ypos += 30;

  $frm->Label("Способ оплаты", 10, $ypos);

  $ypos += 20;

  $t = $frm->Text(10, $ypos, 200, get_payment_name($order_info['payment_id'])); //18
  $t->linkName = "payment_id";

  $ypos += 30;

  $frm->Label('Долг перед фирмой', 10, $ypos);

  $ypos += 20;

  $t = $frm->Text(10, $ypos, 200, calculate_debt_to_company($order_info["cost_kln"], $order_info["cost_auth"], $order_info['filial_id']));
  $t->Disabled = true;
} else {
  $frm->Label("Гонорар автора, " . $ofc_currency, 10, $ypos);
  $frm->Label("Оплачено автору, " . $ofc_currency, 230, $ypos);

  $ypos += 20;

  $t = $frm->Text(10, $ypos, 115, $order_info["cost_auth"]);
  $t->linkName = "cost_auth";
  $t = $frm->Text(230, $ypos, 115, $order_info["author_paid"]);
  $t->linkName = "author_paid";
}

$ypos += 30;

$frm->Label("Статус заказа", 10, $ypos);

$ypos += 20;

$t = $frm->Text(10, $ypos, 200, get_status_name($order_info['status_id'])); //21
$t->linkName = "status_id";

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
$frmo->Text(60, 9, 230, $data_oform_fonts[$fontname_index]);

$frmo->Label("Размер шрифта, pt", 10, 40);
$t = $frmo->Text(130, 39, 80, $oform[1]);

$frmo->Label("Интервал", 10, 70);
$frmo->Text(75, 69, 215, $data_oform_interv[$oform[2]]);

$frmo->Label("Ссылки", 10, 100);
$frmo->Text(75, 99, 215, $data_oform_links[$oform[3]]);

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
$frmo->Text(85, 215, 200, $data_oform_numpos[$oform[8]]);

$frmo->VLine(10, $h - 80, 280);

$b = $frmo->Button("Отмена", 120, $h - 60, 80);
$b->Event = "jQuery.modal.close();";