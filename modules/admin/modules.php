<?php

use Components\Entity\Module;

$GUI->cmdmenu->AddItem("Список", "?section=admin&subsection=1&action=list");
$GUI->cmdmenu->AddItem("Добавить", "?section=admin&subsection=1&action=add");

$action = 'list';
if (isset($_GET['action']) && !empty($_GET['action'])) {
  $action = $_GET['action'];
}
switch ($action) {
  case 'list':
    $GUI->mmenu->selected->selected->caption = "Список модулей";

    $tbl = $GUI->Table("modules" . $n);
    $tbl->Width = "100%";
    $tbl->DataMYSQL("modules");

    $tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
      10, 20, 50, 100, 0
    ));

    $tbl->RowEvent2 = "document.location.href=\"?section=admin&subsection=1&action=edit&module=%var%\"";

    $r = $tbl->NewColumn();
    $r->Caption = "Номер";
    $r->Align = "center";
    $r->DoSort = true;
    $r->Key = "id";

    $r = $tbl->NewColumn();
    $r->Caption = "Имя";
    $r->Align = "left";
    $r->DoSort = true;
    $r->Key = "name";

    $r = $tbl->NewColumn();
    $r->Caption = "Внутренее имя";
    $r->DoSort = true;
    $r->Align = "left";
    $r->Key = "internal_name";

    $r = $tbl->NewColumn();
    $r->Caption = "Порядок";
    $r->DoSort = true;
    $r->Align = "left";
    $r->Key = "order";

    break;

  case 'add':
    $ypos = 0;
    $frm = $GUI->Form("Добавит новый модуль", 300, 0);
    $frm->OnExecute = "add_module";

    $frm->Label("Имя (отображается в верхнем меню):", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->linkName = "name";
    $ypos += 30;

    $frm->Label("Внутренее имя (по названию папки):", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->linkName = "internal_name";
    $ypos += 30;

    $frm->Label("Порядок отображения в меню", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 40);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->AddValidator(new CGUI_VALIDATOR_09);
    $t->linkName = "order";

    $frm->VLine(10, $ypos += 40, 280);
    $frm->Button("Сохранить", 40, $ypos += 20, 100, true);
    $b = $frm->Button("К списку", 160, $ypos, 100, false);
    $b->Event = "document.location.href='?section=admin&subsection=1&action=list'";
    $frm->height = $ypos + 60;

    break;

  case 'edit':
    $module_id = $_GET['module'];

    $ypos = 0;
    $frm = $GUI->Form("Редактировать модуль №" . $module_id, 300, 0);
    $frm->OnExecute = "edit_module";

    $t = $frm->Hidden($module_id);
    $t->linkName = 'id';

    $module_info = Module::find($module_id);

    $frm->Label("Имя (отображается в верхнем меню):", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250, $module_info['name']);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->linkName = "name";
    $ypos += 30;

    $frm->Label("Внутренее имя (по названию папки):", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250, $module_info['internal_name']);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->linkName = "internal_name";
    $ypos += 30;

    $frm->Label("Порядок отображения в меню", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 40, $module_info['order']);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->AddValidator(new CGUI_VALIDATOR_09);
    $t->linkName = "order";

    $frm->VLine(10, $ypos += 40, 280);
    $frm->Button("Сохранить", 40, $ypos += 20, 100, true);
    $b = $frm->Button("К списку", 160, $ypos, 100, false);
    $b->Event = "document.location.href='?section=admin&subsection=1&action=list'";
    $frm->height = $ypos + 60;
    break;
  default:
    break;
}