<?php

$usr = $_SESSION["user"]["data"];

if (isset($_REQUEST["step"]) && ($_REQUEST["step"] == 2)) {
  if (!isset($_SESSION["make_visit_tmp"])) {
    page_reloadToSec("1");
  }
  $frm = $GUI->Form("Создать встречу. Шаг 2.", 600);
  $frm->OnExecute = "addvisit_2_exec";
  $ypos = 10;

  $dt = explode("-", $_SESSION["make_visit_tmp"]["date"]);
  $dt = mktime(0, 0, 0, $dt[1], $dt[0], $dt[2]);

  $kln = kln_get($_SESSION["make_visit_tmp"]["klient"]);
  if ($_SESSION["make_visit_tmp"]["place"] == -1) {
    $_SESSION["make_visit_tmp"]["filial_id"] = $kln["filial_id"];
    $frm->Label("Встреча с курьером - " . date("d.m.Y", $dt), 10, $ypos);

    $users = array();
    if (in_array($usr["group_id"], array(0, 1, 2, 3))) {
      foreach ($data_users as $_user) {
        if ($_user["group_id"] == 5 && $_user["blocked"] != 1 && $_user["black_list"] != 1) {
          if (!isset($users[$_user["id"]])) {
            $users[$_user["id"]] = sotr_getFullName($_user["id"]);
          }
        }
      }
    }

    $ulist = array();
    foreach ($users as $k => $v) {
      $ulist[] = $k;
    }

    $frm->Label("Сотрудник", 10, $ypos += 30);
    $frm->Label("Занятость", 300, $ypos);

    $def_user_id = 0;

    if (isset($_SESSION["make_visit_tmp"]["order"]) && intval($_SESSION["make_visit_tmp"]["order"])) {
      // Ищем заказ и менеджера
      $o = ord_get(intval($_SESSION["make_visit_tmp"]["order"]));
      $def_user_id = $o["manager_id"];
    }

    if (!$def_user_id) {
      $kln = kln_get($_SESSION["make_visit_tmp"]["klient"]);
      if ($kln) {
        $def_user_id = $kln["added_by"];
      }
    }

    if (!isset($users[$def_user_id])) {
      $def_user_id = 0;
    }
    // Сотрудник
    $ss = $frm->Select(10, $ypos += 20, 280, array(0 => "-выберите-") + $users, "", $def_user_id);
    $ss->AddValidator(new CGUI_VALIDATOR_NOZERO);
    $ss->linkName = "user";

    $d = $frm->EmptyDiv(310, $ypos, 280, 80);
    $d->css = "";
    $d->Id = "vis_user_busy_box";

    $ss->AddJsEvent("change", "vis_get_user_visits(jQuery('#" . $ss->idname . "').val(), " . $dt . ", '" . implode(":", $ulist) . "')");

    need_data("subway_stations");
    $stations = array();
    foreach ($subway_stations as $station) {
      $stations[$station['id']] = $station['name'];
    }
    $ss2 = $frm->Select(10, $ypos += 30, 280, array(0 => "-выберите-") + $stations, "", 0);
    $ss2->AddValidator(new CGUI_VALIDATOR_NOZERO);
    $ss2->linkName = "station";

    $frm->Label("Начало", 10, $ypos += 30);
    $frm->Label("Окончание", 100, $ypos);

    $def_s = utils_cvt_time2i("10:00");

    $t1 = $frm->TimePic(10, $ypos += 20, 50, $def_s);
    $t1->min_step = 5;
    $t1->linkName = "start";
    $t2 = $frm->TimePic(100, $ypos, 50, $def_s + 5);
    $t2->min_step = 5;
    $t2->linkName = "finish";
    $t1->SetTimeEvent(" jQuery('#" . $t2->idname . "').val(val);");

    $frm->VLine(10, $ypos += 30, 580);

    $frm->Label("Описание клиента:", 10, $ypos += 10);

    $t = $frm->TextArea(10, $ypos += 20, 280, 80);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->linkName = "opisanie_klienta";

    $frm->Label("Описание пути:", 300, $ypos-20);

    $t = $frm->TextArea(310, $ypos, 280, 80);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->linkName = "opisanie_pyti";
    $ypos += 60;

    page_addScriptText(" jQuery(function(){ vis_get_user_visits(jQuery('#" . $ss->idname . "').val()," . $dt . ", '" . implode(":", $ulist) . "'); }); ");
  } else {
    $_SESSION["make_visit_tmp"]["filial_id"] = $kln["filial_id"];
    $frm->Label("Встреча в офисе филиала '" . $data_filials[$_SESSION["make_visit_tmp"]["filial_id"]]["name"] . "' " . date("d.m.Y", $dt), 10, $ypos);

    // Добавить себя
    $users[$usr["id"]] = sotr_getFullName($usr["id"]);

    if (in_array($usr["group_id"], array(1, 2, 3))) {
      // рук-ль добавит 1234, старший и менеджер добавят 234
      foreach ($data_users as $u) {
        // руководитель добавит любого руководителя
        if (($u["group_id"] == 1) && ($usr["group_id"] == 1)) {
          if (!isset($users[$u["id"]])) {
            $users[$u["id"]] = sotr_getFullName($u["id"]);
          }
        }

        // добавим любого менеджера этого филиала
        if (in_array($u["group_id"], array(2, 3, 4)) && ($u["filial_id"] == $_SESSION["make_visit_tmp"]["filial_id"])) {
          if (!isset($users[$u["id"]])) {
            $users[$u["id"]] = sotr_getFullName($u["id"]);
          }
        }
      }
    }

    $ulist = array();
    foreach ($users as $k => $v) {
      $ulist[] = $k;
    }

    $frm->Label("Сотрудник", 10, $ypos += 30);
    $frm->Label("Занятость", 300, $ypos);

    $def_user_id = 0;

    if (isset($_SESSION["make_visit_tmp"]["order"]) && intval($_SESSION["make_visit_tmp"]["order"])) {
      // Ищем заказ и менеджера
      $o = ord_get(intval($_SESSION["make_visit_tmp"]["order"]));
      $def_user_id = $o["manager_id"];
    }

    if (!$def_user_id) {
      $kln = kln_get($_SESSION["make_visit_tmp"]["klient"]);
      if ($kln) {
        $def_user_id = $kln["added_by"];
      }
    }

    if (!isset($users[$def_user_id])) {
      $def_user_id = 0;
    }

    // Сотрудник
    $ss = $frm->Select(10, $ypos += 20, 280, array(0 => "-выберите-") + $users, "", $def_user_id);
    $ss->AddValidator(new CGUI_VALIDATOR_NOZERO);
    $ss->linkName = "user";

    $d = $frm->EmptyDiv(310, $ypos, 280, 80);
    $d->css = "";
    $d->Id = "vis_user_busy_box";

    $ss->AddJsEvent("change", "vis_get_user_visits(jQuery('#" . $ss->idname . "').val(), " . $dt . ", '" . implode(":", $ulist) . "')");

    $frm->Label("Начало", 10, $ypos += 30);
    $frm->Label("Окончание", 100, $ypos);

    $def_s = utils_cvt_time2i("10:00");

    if ($_SESSION["make_visit_tmp"]["filial_id"]) {
      $fil = fils_get($_SESSION["make_visit_tmp"]["filial_id"]);
      $dweek = date("w", $dt) - 1;
      if ($dweek == -1) {
        $dweek = 6;
      }

      if (!fils_getworktime($fil, $dweek, $s, $e)) {
        $def_s = $fil["tm_special"][$dweek]["s"];
      } else {
        $def_s = $fil["tm_open"];
      }
    }

    $t1 = $frm->TimePic(10, $ypos += 20, 50, $def_s);
    $t1->min_step = 5;
    $t1->linkName = "start";
    $t2 = $frm->TimePic(100, $ypos, 50, $def_s + 5);
    $t2->min_step = 5;
    $t2->linkName = "finish";

    $t1->SetTimeEvent(" jQuery('#" . $t2->idname . "').val(val);");

    page_addScriptText(" jQuery(function(){ vis_get_user_visits(jQuery('#" . $ss->idname . "').val()," . $dt . ", '" . implode(":", $ulist) . "'); }); ");
  }

  $frm->VLine(10, $ypos += 40, 580);
  $frm->Button("Создать", 190, $ypos += 20, 100, true);
  $b = $frm->Button("Назад", 310, $ypos, 100, false);
  $b->Event = "document.location.href='?section=vis&subsection=1&back'";
  $frm->height = $ypos + 60;
} else {
  if (!isset($_REQUEST["back"])) {
    unset($_SESSION["make_visit_tmp"]);
  }

  $frm = $GUI->Form("Создать встречу. Шаг 1.", "600", "0");
  $frm->OnExecute = "addvisit_1_exec";
  $ypos = 10;
  $frm->Hidden(0);

  // Выбрать клиента или показать его
  $kln_id = 0;
  $k = false;
  if (isset($_REQUEST["kln"])) {
    $k = kln_get(intval($_REQUEST["kln"]));
    // Если мы не рук-ль, то проверим филиал
    if ($k && (($k["filial_id"] == $usr["filial_id"]) || ($usr["group_id"] == 1) || ($usr["group_id"] == 0))) {
      $kln_id = $k["id"];
    } else {
      $GUI->ERR("Неверно указан клиент");
    }
  }

  if (!$kln_id && isset($_SESSION["make_visit_tmp"]["klient"]) && intval($_SESSION["make_visit_tmp"]["klient"])) {
    $kln_id = intval($_SESSION["make_visit_tmp"]["klient"]);
    $k = kln_get($kln_id);
  }

  $selector_order = false;

  if ($k) {
    $h = $frm->Hidden($k["id"]);
    $h->linkName = "klient";
    $frm->Label("Клиент: <b>" . $k["fio"] . "</b>", 10, $ypos);
    $frm->Label("Почта: <b>" . $k["email"] . "</b>", 10, $ypos += 20);
    $frm->Label("Телефон: <b>" . $k["telnum"] . "</b>", 10, $ypos += 20);
    $b = $frm->Button("Инфо", 520, $ypos - 10, 70);
    $b->Event = 'window.open("?section=kln&subsection=2&edit=' . $k["id"] . '");';

    // Добавим поле выбора заказа
    $frm->Label("Заказ, если есть", 10, $ypos += 20);
    $o = ord_getByClient($k["id"]);
    $ords = array();
    if ($o) {
      foreach ($o as $v) {
        $ords[$v["id"]] = $v["id"] . ". " . $v["subject"];
      }
    }

    $ord_id = isset($_SESSION["make_visit_tmp"]["order"]) ? $_SESSION["make_visit_tmp"]["order"] : 0;
    if (isset($_REQUEST["ord"])) {
      $o = ord_get(intval($_REQUEST["ord"]));
      if ($o["klient_id"] == $k["id"]) {
        $ord_id = $o["id"];
      }
    }

    $s = $frm->Select(10, $ypos += 20, 580, array(0 => "не важно") + $ords, "", $ord_id);
    $s->linkName = "order";
    // При выборе запрашиваем сколько он должен и подставляем
    $selector_order = $s;
    $ypos += 20;
  } else {
    kln_search_modal();
    $frm->Label("Клиент", 10, $ypos);
    $s = $frm->Select(10, $ypos += 20, 500, array(0 => "-выберите-") + kln_getlist(), "", ""); //0
    $s->linkName = "klient";
    $s->AddValidator(new CGUI_VALIDATOR_NOZERO);
    $s->AddJsEvent("change", "document.location.href='?section=vis&subsection=1&kln=' + jQuery('#" . $s->idname . "').val(); ");

    $b = $frm->Button("Найти", 520, $ypos - 2, 70);
    $b->Event = 'jQuery("#' . $GUI->Vars["kln_search_modal_form"]->idname . '").modal();';

    //$b = $frm->Button("Инфо", 520, $ypos-2, 70);
    //$b->Event = 'var id= jQuery("#'.$s->idname.'").val(); if(id!=0) window.open("?section=kln&subsection=4&edit="+id);';

    page_AddScriptText("custom_klient_select_event = function(id){ jQuery('#" . $s->idname . "').val(id); };");
    $ypos += 20;
  }

  $frm->VLine(10, $ypos += 20, 580);

  $frm->Label("Цель встречи:", 10, $ypos += 20);

  // get mat
  $t = $frm->TextArea(25, $ypos += 20, 560, 80, isset($_SESSION["make_visit_tmp"]["about"]) ? $_SESSION["make_visit_tmp"]["about"] : "");
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
  $t->linkName = "about";
  $ypos += 80;

  $frm->Label("Деньги:", 10, $ypos += 20);
  $s = $frm->Select(20, $ypos += 20, 100, array(
    1 => "получить",
    -1 => "вернуть"
  ), "", isset($_SESSION["make_visit_tmp"]["money_dir"]) ? $_SESSION["make_visit_tmp"]["money_dir"] : 1);
  $s->linkName = "money_dir";

  $frm->Label("сумма: ", 130, $ypos + 3);
  $t = $frm->Text(180, $ypos, 100, isset($_SESSION["make_visit_tmp"]["summa"]) ? $_SESSION["make_visit_tmp"]["summa"] : "");
  $t->linkName = "summa";
  if ($selector_order) {
    $selector_order->AddJsEvent("change", "vis_get_user_order_dolg(this.value, '" . $t->idname . "', '" . $s->idname . "');");
  }

  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
  $t->AddValidator(new CGUI_VALIDATOR_09);

  // выбрать офис
  $arr = array(0 => "-выберите-", -1 => "с курьером", 1 => "в офисе");

  $frm->Label("Место встречи", 10, $ypos += 40);
  $frm->Label("Дата встречи", 300, $ypos);

  $s = $frm->Select(20, $ypos += 20, 250, $arr, "", isset($_SESSION["make_visit_tmp"]["place"]) ? $_SESSION["make_visit_tmp"]["place"] : 0);
  $s->AddValidator(new CGUI_VALIDATOR_NOZERO);
  $s->linkName = "place";

  $d = $frm->DatePic(310, $ypos, 100, isset($_SESSION["make_visit_tmp"]["date"]) ? strtotime($_SESSION["make_visit_tmp"]["date"]) : "");
  $d->linkName = "date";
  $d->AddValidator(new CGUI_VALIDATOR_NOEMPTY());

  $frm->VLine(10, $ypos += 40, 580);
  $frm->Button("Дальше", 190, $ypos += 20, 100, true);
  $b = $frm->Button("К списку", 310, $ypos, 100, false);
  $b->Event = "document.location.href='?section=vis&subsection=2'";
  $frm->height = $ypos + 60;
}