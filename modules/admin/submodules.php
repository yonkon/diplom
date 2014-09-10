<?php

use Components\Entity\Submodule;

$GUI->cmdmenu->AddItem("Список", "?section=admin&subsection=2&action=list");
$GUI->cmdmenu->AddItem("Добавить", "?section=admin&subsection=2&action=add");

$action = 'list';
if (isset($_GET['action']) && !empty($_GET['action'])) {
  $action = $_GET['action'];
}
switch ($action) {
  case 'list':
    $GUI->mmenu->selected->selected->caption = "Список подмодулей";

    $tbl = $GUI->Table("submodules" . $n);
    $tbl->Width = "100%";
    $tbl->DataMYSQL("submodules");

    $tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
      10, 20, 50, 100, 0
    ));

    $tbl->RowEvent2 = "document.location.href=\"?section=admin&subsection=2&action=edit&submodule=%var%\"";

    $r = $tbl->NewColumn();
    $r->Caption = "Номер";
    $r->Align = "center";
    $r->DoSort = true;
    $r->Key = "id";

    $r = $tbl->NewColumn();
    $r->Caption = "Родительский модуль";
    $r->Align = "center";
    $r->DoSort = true;
    $r->Key = "module_id";
    $r->Process = "get_module_name";

    $r = $tbl->NewColumn();
    $r->Caption = "Имя";
    $r->Align = "center";
    $r->DoSort = true;
    $r->Key = "name";

    $r = $tbl->NewColumn();
    $r->Caption = "Порядок";
    $r->Align = "center";
    $r->Key = "order";

    $r = $tbl->NewColumn();
    $r->Caption = "Выбран по-умолчанию";
    $r->Align = "center";
    $r->Key = "default";
    $r->Process = "yes_or_no";
    break;

  case 'add':
    $ypos = 0;
    $frm = $GUI->Form("Добавит новый подмодуль", 300, 0);
    $frm->OnExecute = "add_submodule";

    $frm->Label("Имя", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->linkName = "name";
    $ypos += 30;

    $frm->Label("Родительский модуль", 10, $ypos += 20);

    $t = $frm->Select(25, $ypos += 20, 250, get_modules(true));
    $t->AddValidator(new CGUI_VALIDATOR_NOZERO());
    $t->linkName = "module_id";
    $ypos += 30;

    $frm->Label("Порядок отображения в меню", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 40);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->AddValidator(new CGUI_VALIDATOR_09);
    $t->linkName = "order";

    $frm->Label("Выбран по-умолчанию", 10, $ypos += 30);
    $t = $frm->Checkbox(175, $ypos, false, 1);
    $t->linkName = "default";

    $frm->VLine(10, $ypos += 40, 280);
    $frm->Button("Сохранить", 40, $ypos += 20, 100, true);
    $b = $frm->Button("К списку", 160, $ypos, 100, false);
    $b->Event = "document.location.href='?section=admin&subsection=2&action=list'";
    $frm->height = $ypos + 60;
    break;

  case 'edit':
    $module_id = $_GET['submodule'];

    $ypos = 0;
    $frm = $GUI->Form("Редактировать подмодуль №" . $module_id, 300, 0);
    $frm->OnExecute = "edit_submodule";

    $t = $frm->Hidden($module_id);
    $t->linkName = 'id';

    $module_info = Submodule::find($module_id);

    $frm->Label("Имя", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250, $module_info['name']);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->linkName = "name";
    $ypos += 30;

    $frm->Label("Родительский модуль", 10, $ypos += 20);
    $t = $frm->Select(25, $ypos += 20, 250, get_modules(true), '', $module_info['module_id']);
    $t->AddValidator(new CGUI_VALIDATOR_NOZERO());
    $t->linkName = "module_id";
    $ypos += 30;

    $frm->Label("Порядок отображения в меню", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 40, $module_info['order']);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->AddValidator(new CGUI_VALIDATOR_09);
    $t->linkName = "order";

    $frm->Label("Выбран по-умолчанию", 10, $ypos += 30);
    $t = $frm->Checkbox(175, $ypos, $module_info['default'], 1);
    $t->linkName = "default";

    $frm->VLine(10, $ypos += 40, 280);
    $frm->Button("Сохранить", 40, $ypos += 20, 100, true);
    $b = $frm->Button("К списку", 160, $ypos, 100, false);
    $b->Event = "document.location.href='?section=admin&subsection=2&action=list'";
    $frm->height = $ypos + 60;
    break;
  default:
    break;
}