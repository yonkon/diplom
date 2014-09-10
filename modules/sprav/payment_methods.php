<?php

use Components\Entity\PaymentMethod;

if (isset($_REQUEST["edit"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }
  $id = intval($_REQUEST["edit"]);
  $payment_method = PaymentMethod::find($id);

  if ($payment_method) {
    $frm = $GUI->Form("Редактировать", 300, 200);

    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $frm->Button("Сохранить", 60, 140, 80, true);
    $frm->OnExecute = "editpayment_exec";
    $b = $frm->Button("К списку", 160, 140, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'edit')) . "\"; return false;";

    $frm->Label("Название", 10, 10);
    $t = $frm->Text(10, 30, 278, $payment_method["name"]);
    $t->linkName = 'name';
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(60));

    $frm->Label("Показывать в форме заказа", 10, 60);
    $t = $frm->Checkbox(190, 60, $payment_method["onsite"], 1);
    $t->linkName = 'onsite';
    $GUI->cmdmenu->AddItem("Удалить", "?section=sprav&subsection=1&del=" . $id);
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} elseif (isset($_REQUEST["add"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }
  $frm = $GUI->Form("Добавить", 300, 200);
  $frm->Button("Добавить", 60, 140, 80, true);
  $frm->OnExecute = "addpayment_exec";
  $b = $frm->Button("К списку", 160, 140, 80);
  $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'add')) . "\"; return false;";

  $frm->Label("Название", 10, 10);
  $t = $frm->Text(10, 30, 278);
  $t->linkName = 'name';
  $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
  $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(60));

  $frm->Label("Показывать в форме заказа", 10, 60);
  $t = $frm->Checkbox(190, 60, false, 1);
  $t->linkName = 'onsite';
} elseif (isset($_REQUEST["del"])) {
//      if (!user_has_right("sprav_w")) {
//        page_ReloadSubSec();
//      }
  $id = intval($_REQUEST["del"]);
  $payment_method = PaymentMethod::find($id);
  if ($payment_method) {
    $frm = $GUI->Form("Удалить", 300, 100);

    $t = $frm->Hidden($id);
    $t->linkName = 'id';

    $frm->Button("Удалить", 60, 40, 80, true);
    $frm->OnExecute = "delpayment_exec";
    $b = $frm->Button("К списку", 160, 40, 80);
    $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'del')) . "\"; return false;";
    $frm->Label("Удалить '" . $payment_method["name"] . "'?", 10, 10);
  } else {
    $GUI->informer->ERR("Запись не найдена");
    page_ReloadSubSec();
  }
} else {
  // oplata

  $tbl = $GUI->Table("sprav" . $n);
  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
    $tbl->RowEvent2 = "document.location.href=\"?section=sprav&subsection=1&edit=%var%\"";
  }

  /*
       $r = $tbl->NewColumn();
       $r->Caption = "Номер";
       $r->DoSort = true;
       $r->Key = "id";
       */
  $r = $tbl->NewColumn();
  $r->Caption = "Наименование";
  $r->DoSort = true;
  $r->Key = "name";

  $r = $tbl->NewColumn();
  $r->Caption = "В форме заказа";
  $r->DoSort = true;
  $r->Key = "onsite";
  $r->Process = "tp_payments_onsite";

  $r = $tbl->NewColumn();
  $r->Caption = "";
  $r->Process = "tp_payments_cmds";


  foreach (PaymentMethod::findAll() as $method) {
    $tbl->AddRow($method, "id");
  }

  $tbl->InlineSort(true);

  if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Добавить")) {
    $GUI->cmdmenu->AddItem("Добавить", "?section=sprav&subsection=1&add");
  }
}