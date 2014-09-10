<?php

use Components\Entity\OrderHistory;

if (isset($_REQUEST['change']) && !empty($_REQUEST['change'])) {
  $order_change_history = OrderHistory::find($_REQUEST['change']);

  /******************** before edit start ********************/

  $frm = $GUI->Form("Содержание заказа до изменений", 650);

  $ypos = 10;

  $is_author = is_author($_SESSION["user"]["data"]['id']);

  if ($is_author) {
    $h = 800;
  } else {
    $h = 1100;
  }

  $frm->VLine(10, $h - 80, 630);
  $frm->height = $h;
  $b = $frm->Button("К списку", 275, $h - 60, 100);
  $b->Event = "document.location.href=\"?section=ord&subsection=2&order=" . $_REQUEST['order'] . "&p=5\"";

  if (!$is_author) {
    $klient = get_client_info($order_change_history['klient_id_old']);

    $frm->Label("Клиент: <b>" . $klient["fio"] . "</b>", 10, $ypos);
    $ypos += 20;
    $frm->Label("Почта: <b>" . $klient["email"] . "</b>", 10, $ypos);
    $ypos += 20;
    $frm->Label("Телефон: <b>" . $klient["telnum"] . "</b>", 10, $ypos);
    $ypos += 20;
    $s = $frm->Hidden($klient["id"]); //0
    $s->linkName = "client";

    $b = $frm->Button("Инфо", 560, $ypos - 32, 70);
    $b->Event = 'var id= jQuery("#' . $s->idname . '").val(); if(id!=0) window.open("?section=kln&subsection=2&edit="+id);';

    $frm->VLine(10, $ypos, 630);

    $ypos += 10;
  }

  $frm->Label("ВУЗ", 10, $ypos);

  $ypos += 20;

  if (!empty($order_change_history['vuz_user_old'])) {
    $s = $frm->Text(10, $ypos, 300, $order_change_history['vuz_user_old']);
  } else {
    $vuz = get_vuz_name($order_change_history['vuz_id_old']);
    $s = $frm->Text(10, $ypos, 300, $vuz['sname'] . '(' . $vuz['name'] . ')');
  }
  $s->linkName = "vuz";

  $ypos += 30;

  $frm->Label("Курс", 10, $ypos);
  $frm->Label("Вид работы", 150, $ypos);

  $ypos += 20;

  $s = $frm->Text(10, $ypos, 120, $data_courses[$order_change_history['kurs_old']]['name']); //3
  $s->linkName = "kurs";

  if (!empty($order_change_history['type_user_old'])) {
    $s = $frm->Text(150, $ypos, 200, $order_change_history['type_user_old']); // 4
  } else {
    $s = $frm->Text(150, $ypos, 200, get_worktype_name($order_change_history['type_id_old']));
  }
  $s->linkName = "worktype";

  $ypos += 30;

  $frm->Label("Направление (факультет)", 10, $ypos);
  $frm->Label("Специальность (дисциплина)", 190, $ypos);

  $ypos += 20;

  $s = $frm->Text(10, $ypos, 160, get_naprav_name($order_change_history['napr_id_old'])); //6
  $s->linkName = "naprav";

  if (!empty($order_change_history['disc_user_old'])) {
    $s = $frm->Text(190, $ypos, 200, $order_change_history['disc_user_old']); // 4
  } else {
    $s = $frm->Text(190, $ypos, 200, get_discipline_name($order_change_history['disc_id_old']));
  }
  //7
  $s->linkName = "disc";

  $ypos += 30;

  $frm->Label("Тема работы", 10, $ypos);

  $ypos += 20;

  $t = $frm->TextArea(10, $ypos, 625, 60, $order_change_history['subject_old']); //9
  $t->linkName = "subject";

  $ypos += 70;

  $frm->Label("Дата сдачи", 10, $ypos);
  $frm->Label("Практика", 150, $ypos);
  $frm->Label("Число страниц", 230, $ypos);
  $frm->Label("Число источников", 360, $ypos);
  $ypos += 20;

  if ($is_author) {
    $t = $frm->Text(10, $ypos, 120, format_date($order_change_history['time_auth_old'])); //10
    $t->linkName = "time_auth";
  } else {
    $t = $frm->Text(10, $ypos, 120, format_date($order_change_history['time_kln_old'])); //10
    $t->linkName = "time_kln";
  }

  $s = $frm->Text(150, $ypos, 60, $data_practica[$order_change_history['prakt_pc_old']]['name']); //11
  $s->linkName = "prakt_pc";

  $frm->Label("от", 230, $ypos);
  $frm->Label("до", 290, $ypos);
  $t = $frm->Text(250, $ypos, 30, $order_change_history['pages_min_old']); //12
  $t->linkName = "pages_min";

  $t = $frm->Text(310, $ypos, 30, $order_change_history['pages_max_old']); //13
  $t->linkName = "pages_max";

  $frm->Label("от", 360, $ypos);
  $frm->Label("до", 420, $ypos);
  $t = $frm->Text(380, $ypos, 30, $order_change_history["src_min_old"]); //14
  $t->linkName = "src_min";

  $t = $frm->Tracker(440, $ypos, 30, $order_change_history["src_max_old"]); //15
  $t->linkName = "src_max";

  $btn_oform = $frm->Button("Оформление", 500, $ypos - 2, 100);

  $ypos += 30;

  $frm->Label("Требования", 10, $ypos);

  $ypos += 20;

  $t = $frm->TextArea(10, $ypos, 625, 100, $order_change_history['about_kln_old']); //16
  $t->linkName = "about_kln";

  $ypos += 115;

  $frm->Label("Комментарий ОК", 10, $ypos);
  $frm->Label("Дата комментария ОК", 515, $ypos);

  $ypos += 20;

  $t = $frm->TextArea(10, $ypos, 490, 80, $order_change_history['ok_comment_old']); //17
  $t->linkName = "ok_comment";

  $t = $frm->Text(515, $ypos, 120, format_date($order_change_history['ok_comment_date_old'], false, true)); //10
  $t->linkName = "ok_comment_date";

  $ypos += 100;

  $frm->Label("Комментарий к оплате", 10, $ypos);

  $ypos += 20;

  $t = $frm->Text(10, $ypos, 200, array_key_exists($order_change_history['payment_comment_old'], $data_author_payment_status) ? $data_author_payment_status[$order_change_history['payment_comment_old']] : $order_change_history['payment_comment_old']); //17
  $t->linkName = "payment_comment";

  $ypos += 30;


//        $t = $frm->TextArea(10, $ypos, 490, 80, $order_change_history['payment_comment_old']); //17
//        $t->linkName = "payment_comment";
//
//        $ypos += 100;

  if (!$is_author) {
    $frm->Label("Комментарий менеджера", 10, $ypos);
    $frm->Label("Следующий контакт", 515, $ypos);

    $ypos += 20;

    $t = $frm->TextArea(10, $ypos, 490, 80, $order_change_history['about_mng_old']); //17
    $t->linkName = "about_mng";

    $t = $frm->Text(515, $ypos, 120, format_date($order_change_history['next_rel_date_old'])); //10
    $t->linkName = "next_rel_date";

    $ypos += 100;

    $frm->Label("Способ оплаты", 10, $ypos);
    $frm->Label("Заказ принят", 230, $ypos);
//          $frm->Label("Скидка", 445, $ypos);

    $ypos += 20;

    $s = $frm->Text(10, $ypos, 200, get_payment_name($order_change_history['payment_id_old'])); //18
    $s->linkName = "payment_id";

    $s = $frm->Text(230, $ypos, 200, $data_ordertakemethod[$order_change_history['from_id_old']]); //19
    $s->linkName = "from_id";
//          $t = $frm->Text(445, $ypos, 60, $defdata["skid"]); //20
//          $t->linkName = "skid";
//          $s = $frm->Text(510, $ypos, 50, $defdata["skidt"]); //21
//          $s->linkName = "skidt";

    $ypos += 30;

    $frm->Label("Цена клиента, " . $ofc_currency, 10, $ypos);
    $frm->Label("Оплачено клиентом, " . $ofc_currency, 180, $ypos);
    $frm->Label("Оплачено фирме, " . $ofc_currency, 350, $ypos);

    $ypos += 20;

    $t = $frm->Text(10, $ypos, 115, $order_change_history["cost_kln_old"]);
    $t->linkName = "cost_kln";

    $t = $frm->Text(180, $ypos, 115, $order_change_history["oplata_kln_old"]);
    $t->linkName = "oplata_kln";

    $t = $frm->Text(350, $ypos, 115, $order_change_history["company_paid_old"]);
    $t->linkName = "company_paid";

    $ypos += 30;
  }

  $frm->Label("Гонорар автора, " . $ofc_currency, 10, $ypos);
  $frm->Label("Оплачено автору, " . $ofc_currency, 180, $ypos);
  $frm->Label("Статус заказа", 350, $ypos);

  $ypos += 20;

  $t = $frm->Text(10, $ypos, 115, $order_change_history["cost_auth_old"]);
  $t->linkName = "cost_auth";

  $t = $frm->Text(180, $ypos, 115, $order_change_history["author_paid_old"]);
  $t->linkName = "author_paid";

  $s = $frm->Text(350, $ypos, 200, get_status_name($order_change_history['status_id_old'])); //21
  $s->linkName = "status_id";

  $oform = unserialize($order_change_history['oform_old']);

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
  $btn_oform->Event = "show_oform_editor('" . $frm->idname . "', " . $oform_fntnm->id . ", 9, 'cgui_form_oform_editor_before');";
  $h = 330;
  $frmo = $GUI->ModalForm("Оформление", 300, $h);
  $frmo->NuSubmit = true;
  $frmo->Rename("oform_editor_before");
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

  /******************** before edit end ********************/
  /******************** after edit start ********************/

  $frm = $GUI->Form("Содержание заказа после изменений", 650);

  $ypos = 10;

  $is_author = is_author($_SESSION["user"]["data"]['id']);

  if ($is_author) {
    $h = 800;
  } else {
    $h = 1100;
  }

  $frm->VLine(10, $h - 80, 630);
  $frm->height = $h;
  $b = $frm->Button("К списку", 275, $h - 60, 100);
  $b->Event = "document.location.href=\"?section=ord&subsection=2&order=" . $_REQUEST['order'] . "&p=5\"";

  if (!$is_author) {
    $klient = get_client_info($order_change_history['klient_id_new']);

    $frm->Label("Клиент: <b>" . $klient["fio"] . "</b>", 10, $ypos);
    $ypos += 20;
    $frm->Label("Почта: <b>" . $klient["email"] . "</b>", 10, $ypos);
    $ypos += 20;
    $frm->Label("Телефон: <b>" . $klient["telnum"] . "</b>", 10, $ypos);
    $ypos += 20;
    $s = $frm->Hidden($klient["id"]); //0
    $s->linkName = "client";

    $b = $frm->Button("Инфо", 560, $ypos - 32, 70);
    $b->Event = 'var id= jQuery("#' . $s->idname . '").val(); if(id!=0) window.open("?section=kln&subsection=2&edit="+id);';

    $frm->VLine(10, $ypos, 630);

    $ypos += 10;
  }

  $frm->Label("ВУЗ", 10, $ypos);

  $ypos += 20;

  if (!empty($order_change_history['vuz_user_new'])) {
    $s = $frm->Text(10, $ypos, 300, $order_change_history['vuz_user_new']);
  } else {
    $vuz = get_vuz_name($order_change_history['vuz_id_new']);
    $s = $frm->Text(10, $ypos, 300, $vuz['sname'] . '(' . $vuz['name'] . ')');
  }
  $s->linkName = "vuz";

  $ypos += 30;

  $frm->Label("Курс", 10, $ypos);
  $frm->Label("Вид работы", 150, $ypos);

  $ypos += 20;

  $s = $frm->Text(10, $ypos, 120, $data_courses[$order_change_history['kurs_new']]['name']); //3
  $s->linkName = "kurs";

  if (!empty($order_change_history['type_user_new'])) {
    $s = $frm->Text(150, $ypos, 200, $order_change_history['type_user_new']); // 4
  } else {
    $s = $frm->Text(150, $ypos, 200, get_worktype_name($order_change_history['type_id_new']));
  }
  $s->linkName = "worktype";

  $ypos += 30;

  $frm->Label("Направление (факультет)", 10, $ypos);
  $frm->Label("Специальность (дисциплина)", 190, $ypos);

  $ypos += 20;

  $s = $frm->Text(10, $ypos, 160, get_naprav_name($order_change_history['napr_id_new'])); //6
  $s->linkName = "naprav";

  if (!empty($order_change_history['disc_user_new'])) {
    $s = $frm->Text(190, $ypos, 200, $order_change_history['disc_user_new']); // 4
  } else {
    $s = $frm->Text(190, $ypos, 200, get_discipline_name($order_change_history['disc_id_new']));
  }
  //7
  $s->linkName = "disc";

  $ypos += 30;

  $frm->Label("Тема работы", 10, $ypos);

  $ypos += 20;

  $t = $frm->TextArea(10, $ypos, 625, 60, $order_change_history['subject_new']); //9
  $t->linkName = "subject";

  $ypos += 70;

  $frm->Label("Дата сдачи", 10, $ypos);
  $frm->Label("Практика", 150, $ypos);
  $frm->Label("Число страниц", 230, $ypos);
  $frm->Label("Число источников", 360, $ypos);
  $ypos += 20;

  if ($is_author) {
    $t = $frm->Text(10, $ypos, 120, format_date($order_change_history['time_auth_new'])); //10
    $t->linkName = "time_auth";
  } else {
    $t = $frm->Text(10, $ypos, 120, format_date($order_change_history['time_kln_new'])); //10
    $t->linkName = "time_kln";
  }

  $s = $frm->Text(150, $ypos, 60, $data_practica[$order_change_history['prakt_pc_new']]['name']); //11
  $s->linkName = "prakt_pc";

  $frm->Label("от", 230, $ypos);
  $frm->Label("до", 290, $ypos);
  $t = $frm->Text(250, $ypos, 30, $order_change_history['pages_min_new']); //12
  $t->linkName = "pages_min";

  $t = $frm->Text(310, $ypos, 30, $order_change_history['pages_max_new']); //13
  $t->linkName = "pages_max";

  $frm->Label("от", 360, $ypos);
  $frm->Label("до", 420, $ypos);
  $t = $frm->Text(380, $ypos, 30, $order_change_history["src_min_new"]); //14
  $t->linkName = "src_min";

  $t = $frm->Tracker(440, $ypos, 30, $order_change_history["src_max_new"]); //15
  $t->linkName = "src_max";

  $btn_oform = $frm->Button("Оформление", 500, $ypos - 2, 100);

  $ypos += 30;

  $frm->Label("Требования", 10, $ypos);

  $ypos += 20;

  $t = $frm->TextArea(10, $ypos, 625, 100, $order_change_history['about_kln_new']); //16
  $t->linkName = "about_kln";

  $ypos += 115;

  $frm->Label("Комментарий ОК", 10, $ypos);
  $frm->Label("Дата комментария ОК", 515, $ypos);

  $ypos += 20;

  $t = $frm->TextArea(10, $ypos, 490, 80, $order_change_history['ok_comment_new']); //17
  $t->linkName = "ok_comment";

  $t = $frm->Text(515, $ypos, 120, format_date($order_change_history['ok_comment_date_new'], false, true)); //10
  $t->linkName = "ok_comment_date";

  $ypos += 100;

  $frm->Label("Комментарий к оплате", 10, $ypos);

  $ypos += 20;

  $t = $frm->Text(10, $ypos, 200, array_key_exists($order_change_history['payment_comment_new'], $data_author_payment_status) ? $data_author_payment_status[$order_change_history['payment_comment_new']] : $order_change_history['payment_comment_new']); //17
  $t->linkName = "payment_comment";

  $ypos += 30;

//        $t = $frm->TextArea(10, $ypos, 490, 80, $order_change_history['payment_comment_new']); //17
//        $t->linkName = "payment_comment";
//
//        $ypos += 100;

  if (!$is_author) {
    $frm->Label("Комментарий менеджера", 10, $ypos);
    $frm->Label("Следующий контакт", 515, $ypos);

    $ypos += 20;

    $t = $frm->TextArea(10, $ypos, 490, 80, $order_change_history['about_mng_new']); //17
    $t->linkName = "about_mng";

    $t = $frm->Text(515, $ypos, 120, format_date($order_change_history['next_rel_date_new'])); //10
    $t->linkName = "next_rel_date";

    $ypos += 100;

    $frm->Label("Способ оплаты", 10, $ypos);
    $frm->Label("Заказ принят", 230, $ypos);
//          $frm->Label("Скидка", 445, $ypos);

    $ypos += 20;

    $s = $frm->Text(10, $ypos, 200, get_payment_name($order_change_history['payment_id_new'])); //18
    $s->linkName = "payment_id";

    $s = $frm->Text(230, $ypos, 200, $data_ordertakemethod[$order_change_history['from_id_new']]); //19
    $s->linkName = "from_id";
//          $t = $frm->Text(445, $ypos, 60, $defdata["skid"]); //20
//          $t->linkName = "skid";
//          $s = $frm->Text(510, $ypos, 50, $defdata["skidt"]); //21
//          $s->linkName = "skidt";

    $ypos += 30;

    $frm->Label("Цена клиента, " . $ofc_currency, 10, $ypos);
    $frm->Label("Оплачено клиентом, " . $ofc_currency, 180, $ypos);
    $frm->Label("Оплачено фирме, " . $ofc_currency, 350, $ypos);

    $ypos += 20;

    $t = $frm->Text(10, $ypos, 115, $order_change_history["cost_kln_new"]);
    $t->linkName = "cost_kln";

    $t = $frm->Text(180, $ypos, 115, $order_change_history["oplata_kln_new"]);
    $t->linkName = "oplata_kln";

    $t = $frm->Text(350, $ypos, 115, $order_change_history["company_paid_new"]);
    $t->linkName = "company_paid";

    $ypos += 30;
  }

  $frm->Label("Гонорар автора, " . $ofc_currency, 10, $ypos);
  $frm->Label("Оплачено автору, " . $ofc_currency, 180, $ypos);
  $frm->Label("Статус заказа", 350, $ypos);

  $ypos += 20;

  $t = $frm->Text(10, $ypos, 115, $order_change_history["cost_auth_new"]);
  $t->linkName = "cost_auth";

  $t = $frm->Text(180, $ypos, 115, $order_change_history["author_paid_new"]);
  $t->linkName = "author_paid";

  $s = $frm->Text(350, $ypos, 200, get_status_name($order_change_history['status_id_new'])); //21
  $s->linkName = "status_id";

  $oform = unserialize($order_change_history['oform_new']);

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
  /******************** after edit end ********************/
} else {
  $GUI->Vars["page_hdr"] = "История изменений заказа №" . $order_id;
  $tbl = $GUI->Table("order_history" . $n);
  $tbl->Width = "50%";
  $tbl->DataMYSQL("orders_changes_history");
  $tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
    10, 20, 50, 100, 0
  ));
  $tbl->FilterMYSQL("order_id = " . $_REQUEST['order']);

  $tbl->RowEvent2 = "document.location.href=\"?section=ord&subsection=2&order=" . $_REQUEST['order'] . "&change=%var%&p=5\"";

  $column = $tbl->NewColumn();
  $column->Caption = "Автор изменений";
  $column->DoSort = true;
  $column->Key = "change_user_id";
  $column->Process = "sotr_getFullName";

  $column = $tbl->NewColumn();
  $column->Caption = "Дата изменений";
  $column->DoSort = true;
  $column->Key = "change_date";
  $column->Process = "format_date";
}