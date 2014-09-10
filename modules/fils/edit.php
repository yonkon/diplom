<?php

use Components\Entity\Filial;
use Components\Entity\Role;
use Components\Classes\Roles;
use Components\Exceptions\AccessDeniedException;
use Components\Classes\db;

if (!Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
  throw new AccessDeniedException;
}

$id = intval($_REQUEST["edit"]);
$filial = Filial::find($id);
if ($filial) {
  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Удалить")) {
    $GUI->cmdmenu->AddItem("Удалить", "?section=fils&subsection=2&del=" . $id);
  }

  $frm = $GUI->Form("Редактировать филиал", 600, 420);
  $frm->Hidden($id);
  $ypos = 10;

  $frm->Label("Название", 10, $ypos);
  $frm->Label("Руководитель", 310, $ypos);

  $t = $frm->Text(10, $ypos += 20, 278, $filial["name"]);
  $t->linkName = 'name';
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(40));

  $usrs = array();
  $usrs[0] = "-выберите-";
  $ruk_group = Role::findOneBy(array(
    'name' => 'Руководитель',
  ));
  $elder_manager_group = Role::findOneBy(array(
    'name' => 'Старший менеджер',
  ));
  foreach ($data_users as $u) {
    if ($u["black_list"]) {
      continue;
    }
    if ($u["group_id"] == $ruk_group['id'] || $u["group_id"] == $elder_manager_group['id']) {
      $usrs[$u["id"]] = sotr_getFullName($u["id"]);
    }
  }

  $f = $frm->Select(310, $ypos, 278, $usrs, "", $filial["user_id"]);
  $f->linkName = 'manager';
  $f->AddValidator(new CGUI_VALIDATOR_NOZERO());

  $h = $frm->Hidden(db::get_single_values_string("SELECT city_id FROM " . TBL_PREF . "filial_to_city WHERE filial_id = " . db::input($filial['id']), '_'));
  $h->linkName = 'city';
  city_modal($h->idname, $id);
  $b = $frm->Button("Города", 10, $ypos += 30, 70);
  $b->Event = 'open_cities("' . $h->idname . '", "' . $GUI->Vars["city_modal_form"]->idname . '");';

  $frm->Label("Email филиала", 10, $ypos += 30);
  $t = $frm->Text(10, $ypos += 20, 573, $filial["email"]);
  $t->linkName = 'email';

  $frm->Label("Адрес сайта", 10, $ypos += 30);
  $t = $frm->Text(10, $ypos += 20, 573, $filial["web"]);
  $t->linkName = 'url';

  $frm->Label("Путь к форме заказа", 10, $ypos += 30);
  $t = $frm->Text(10, $ypos += 20, 573, $filial["order_form_path"]);
  $t->linkName = 'order_form_path';

  $frm->Label("Описание", 10, $ypos += 30);
  $t = $frm->TextArea(10, $ypos += 20, 573, 50, $filial["about"]);
  $t->linkName = 'description';

  $frm->Label("Заметки", 10, $ypos += 70);
  $t = $frm->TextArea(10, $ypos += 20, 573, 50, $filial["rem"]);
  $t->linkName = 'notes';

  $frm->Label("Время работы", 10, $ypos += 70);
  $frm->Label("Использовать как филиал по-умолчанию", 300, $ypos);

  $frm->Label("c", 20, $ypos += 20);
  $t = $frm->TimePic(30, $ypos, 80, $filial["tm_open"]);
  $t->min_step = 10;
  $t->linkName = 'time_from';

  $frm->Label("по", 120, $ypos);
  $t = $frm->TimePic(140, $ypos, 80, $filial["tm_close"]);
  $t->min_step = 10;
  $t->linkName = 'time_to';

  $c = $frm->Checkbox(300, $ypos, $filial["default"], 1);
  $c->linkName = 'default';
  $c->defval = 1;

  $day_names = array(
    0 => "понедельник",
    1 => "вторник",
    2 => "среда",
    3 => "четверг",
    4 => "пятница",
    5 => "суббота",
    6 => "воскресенье"
  );

  if (is_array($filial["tm_special"]) && count($filial["tm_special"]) < 7) {
    page_scriptNeed("scripts.js", "modules/fils");

    $frm->VLine(10, $ypos += 40, 580);
    $frm->Label("Добавить день", 10, $ypos += 10);
    $days = $day_names; //array(0=>"понедельник", 1=>"вторник", 2=>"среда", 3=>"четверг", 4=>"пятница", 5=>"суббота", 6=>"воскресенье");
    if (is_array($filial["tm_special"])) {
      foreach ($filial["tm_special"] as $k => $f) {
        if (isset($days[$k])) {
          unset($days[$k]);
        }
      }
    }

    $s = $frm->Select(10, $ypos += 20, 120, $days);
    $frm->Label("работаем с", 140, $ypos);
    $t1 = $frm->TimePic(210, $ypos, 80, $filial["tm_open"]);
    $frm->Label("по", 300, $ypos);
    $t2 = $frm->TimePic(320, $ypos, 80, $filial["tm_close"]);
    $cb = $frm->Checkbox(420, $ypos, false, 1);
    $frm->Label("выходной", 440, $ypos);
    $b = $frm->Button("Добавить", 510, $ypos - 4);
    $b->Event = "fils_add_day_info('" . $frm->idname . "', '" . $s->idname . "', '" . $t1->idname . "','" . $t2->idname . "','" . $cb->idname . "', " . $id . ");";
  }

  if (is_array($filial["tm_special"]) && count($filial["tm_special"])) {
    $ypos += 10;
    ksort($filial["tm_special"]);

    foreach ($filial["tm_special"] as $k => $f) {
      if ($f["w"]) {
        $s = "выходной";
      } else {
        $s = "с " . utils_cvt_i2times($f["s"]) . " по " . utils_cvt_i2times($f["e"]);
      }

      $frm->Label($day_names[$k] . ":", 20, $ypos += 30);
      $frm->Label($s, 110, $ypos);
      $frm->Label("<a href='#' onclick='fils_reset_day(\"" . $frm->idname . "\", " . $k . ", " . $id . "); return false;'>сбросить</a>", 230, $ypos);
      //$b = $frm->Button("Сбросить день", 220, $ypos-4);
      //$b->Event = "fils_reset_day('".$frm->idname."', ".$k.", ".$id.");";

    }
  }

  $frm->Label("Доход, %", 10, $ypos += 50);
  $frm->Label("Расход, %", 100, $ypos);

  $t = $frm->Tracker(10, $ypos += 20, 80, ($filial["profit"] * 100));
  $t->linkName = 'profit';
  $t->MaxVal = 100;
  $t->MinVal = 0;
  $t->AddValidator(new CGUI_VALIDATOR_09());

  $t = $frm->Tracker(100, $ypos, 80, ($filial["consumption"] * 100));
  $t->linkName = 'consumption';
  $t->MaxVal = 100;
  $t->MinVal = 0;
  $t->AddValidator(new CGUI_VALIDATOR_09());

  $frm->VLine(10, $ypos += 40, 580);
  $frm->Button("Сохранить", 210, $ypos += 20, 80, true);
  $frm->OnExecute = "editfilial_exec";
  $b = $frm->Button("К списку", 310, $ypos, 80);
  $b->Event = "document.location.href=\"?section=fils&subsection=2\"; return false;";
  $frm->height = $ypos + 70;
} else {
  $GUI->informer->ERR("Запись не найдена");
  page_ReloadSubSec();
}