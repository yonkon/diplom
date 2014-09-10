<?php

  use Components\Classes\db;

  need_data("email_notifications_types");

  if (isset($_REQUEST["edit"])) {
    $id = intval($_REQUEST["edit"]);

    if (isset($email_notifications_types[$id])) {
      $e_n_t = $email_notifications_types;
      $frm = $GUI->Form("Редактировать", 400, 220);

      $frm->Hidden($id);
      $frm->Button("Сохранить", 100, 170, 80, true);
      $frm->OnExecute = "edit_description_exec";
      $b = $frm->Button("К списку", 220, 170, 80);
      $b->Event = "document.location.href=\"?" . $GUI->Url(array('section', 'subsection', 'edit')) . "\"; return false;";
      $frm->Label("Название", 10, 10);
      $t = $frm->TextArea(10, 30, 378, 80, $e_n_t[$id]["description"]);
      $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY());
      $t->AddValidator(new CGUI_VALIDATOR_MAXLEN(512));
      $action_type = $frm->Select(10, 130, 250, array("0"=>"игнорировать", "1"=>"внутреннее сообщение", "2"=>"внутреннее сообщение + email"), "", $e_n_t [$id]["action_type"]);
      //$persist = $frm->Checkbox(10, 130, $e_n_t[$id]["persist"], 'persist', "Сохранять нотификации в БД");
      //$send = $frm->Checkbox(10, 170, $e_n_t[$id]["send"], 'send', "Оправлять сохранённые нотификации на почту");

    } else {
      $GUI->informer->ERR("Запись не найдена");
      page_ReloadSubSec();
    }
  } else {
    $tbl = $GUI->Table("email_notifications_types" . $n);
    $tbl->Width = "70%";

    if (user_can($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
      $tbl->RowEvent2 = "document.location.href=\"?section=sprav&subsection=" . $n . "&edit=%var%\"";
    }

    $r = $tbl->NewColumn();
    $r->Caption = "Номер";
    $r->DoSort = true;
    $r->Key = "id";

    $r = $tbl->NewColumn();
    $r->Caption = "Описание";
    $r->DoSort = true;
    $r->Key = "description";
    $r->Align = "left";
/*
    $r = $tbl->NewColumn();
    $r->Caption = "Сохранять нотификации в БД";
    $r->DoSort = true;
    $r->Key = "persist";
    $r->Align = "left";

    $r = $tbl->NewColumn();
    $r->Caption = "Оправлять сохранённые нотификации на почту";
    $r->DoSort = true;
    $r->Key = "send";
    $r->Align = "left";
*/
// temporary
    $r = $tbl->NewColumn();
    $r->Caption = "Действия";
    $r->DoSort = false;
    $r->Key = "action_type";
    $r->Align = "left";
    $r->Process = "tp_email_notify_actions";

    foreach ($email_notifications_types as $d) {
      if($d['id'] != 3) {
        $tbl->AddRow($d, "id");
      }
    }

    $tbl->InlineSort(true);

  }


  function edit_description_exec($Frm, $Err) {
    global $email_notifications_types;
    if (!$Err) {
      $id = intval($Frm->GetValue(0));
      $descr = str_replace("'", '"', htmlspecialchars($Frm->GetValue(1)));
      //$persist = (null !== $Frm->GetValue(2)) ? 1 : 0;
      //$send = (null !== $Frm->GetValue(3)) ? 1 : 0;

      $action_type = $Frm->GetValue(2);
      in_array($action_type, array(0,1,2)) or $action_type = 0;

      if (isset($email_notifications_types[$id])) 
      {
        db::update(TABLE_EMAIL_NOTIFICATION_TYPES, array(
          'description' => $descr,
          //'persist' => $persist,
          //'send' => $send,
          'action_type' => $action_type,
        ), "id=" . $id);
        $Frm->_gui->informer->OK("Сохранено");
        page_reloadAll();
      }
    }
  }
