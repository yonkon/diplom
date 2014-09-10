<?php

use Components\Entity\Command;

$GUI->cmdmenu->AddItem("Список", "?section=admin&subsection=3&action=list");
$GUI->cmdmenu->AddItem("Добавить", "?section=admin&subsection=3&action=add");

$action = 'list';
if (isset($_GET['action']) && !empty($_GET['action'])) {
  $action = $_GET['action'];
}
switch ($action) {
  case 'list':
    $GUI->mmenu->selected->selected->caption = "Список комманд";

    $tbl = $GUI->Table("submodule_commands" . $n);
    $tbl->Width = "100%";
    $tbl->DataMYSQL("submodule_commands");

    $tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
      10, 20, 50, 100, 0
    ));

    $tbl->RowEvent2 = "document.location.href=\"?section=admin&subsection=3&action=edit&command=%var%\"";

    $r = $tbl->NewColumn();
    $r->Caption = "Номер";
    $r->Align = "center";
    $r->DoSort = true;
    $r->Key = "id";

    $r = $tbl->NewColumn();
    $r->Caption = "Модуль";
    $r->Align = "center";
    $r->DoSort = true;
    $r->Key = "module_id";
    $r->Process = "get_module_name";

    $r = $tbl->NewColumn();
    $r->Caption = "Подмодуль";
    $r->Align = "center";
    $r->DoSort = true;
    $r->Key = "submodule_id";
    $r->Process = "get_submodule_name";

    $r = $tbl->NewColumn();
    $r->Caption = "Имя";
    $r->Align = "left";
    $r->DoSort = true;
    $r->Key = "name";

    $r = $tbl->NewColumn();
    $r->Caption = "Порядок";
    $r->DoSort = true;
    $r->Align = "left";
    $r->Key = "order";
    break;

  case 'add':
    $ypos = 0;
    $frm = $GUI->Form("Добавит новую команду", 300, 0);
    $frm->OnExecute = "add_command";

    $frm->Label("Имя", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->linkName = "name";
    $ypos += 30;

    $modules = get_modules(true);
    $frm->Label("Модуль", 10, $ypos += 20);
    $ss = $frm->Select(25, $ypos += 20, 250, $modules);
    $ss->AddValidator(new CGUI_VALIDATOR_NOZERO());
    $ss->linkName = "module_id";
    $ypos += 30;

$jq = <<<jQ
  var module_id = jQuery(this).val();
  var field_number = parseInt(jQuery(this).attr('id').substr(18, 1));
  jQuery.ajax({
    type:"POST",
    url:'/modules/admin/ajax.php',
    cache:false,
    dataType:'json',
    data:{action:'get_submodules', module_id:module_id},
    success:function(result) {
      var options = '';
      jQuery.each(result, function(index, value) {
        options += '<option value="' + index + '">' + value + '</option>';
      });

      jQuery('#cgui_form_0_field_'+(field_number+1)+'').html(options);
    }
  });
jQ;

    $ss->AddJsEvent("change", $jq);

    $modules_ids = array_keys($modules);

    $frm->Label("Подмодуль", 10, $ypos += 20);
    $t = $frm->Select(25, $ypos += 20, 250, get_submodules(reset($modules_ids)));
    $t->AddValidator(new CGUI_VALIDATOR_NOZERO());
    $t->linkName = "submodule_id";
    $ypos += 30;

    $frm->Label("Порядок отображения в меню", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 40);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->AddValidator(new CGUI_VALIDATOR_09);
    $t->linkName = "order";

    $frm->VLine(10, $ypos += 40, 280);
    $frm->Button("Сохранить", 40, $ypos += 20, 100, true);
    $b = $frm->Button("К списку", 160, $ypos, 100, false);
    $b->Event = "document.location.href='?section=admin&subsection=3&action=list'";
    $frm->height = $ypos + 60;
    break;

  case 'edit':
    $command_id = $_GET['command'];

    $GUI->cmdmenu->AddItem("Удалить", "?section=admin&subsection=3&action=del&command=" . $command_id);

    $ypos = 0;
    $frm = $GUI->Form("Редактировать команду №" . $command_id, 300, 0);
    $frm->OnExecute = "edit_command";

    $t = $frm->Hidden($command_id);
    $t->linkName = 'id';

    $command_info = Command::find($command_id);

    $frm->Label("Имя", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250, $command_info['name']);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->linkName = "name";
    $ypos += 30;

    $frm->Label("Родительский модуль", 10, $ypos += 20);
    $ss = $frm->Select(25, $ypos += 20, 250, get_modules(true), '', $command_info['module_id']);
    $ss->AddValidator(new CGUI_VALIDATOR_NOZERO());
    $ss->linkName = "module_id";
    $ypos += 30;

    $jq = <<<jQ
  var module_id = jQuery(this).val();
  var field_number = parseInt(jQuery(this).attr('id').substr(18, 1));
  jQuery.ajax({
    type:"POST",
    url:'/modules/admin/ajax.php',
    cache:false,
    dataType:'json',
    data:{action:'get_submodules', module_id:module_id},
    success:function(result) {
      var options = '';
      jQuery.each(result, function(index, value) {
        options += '<option value="' + index + '">' + value + '</option>';
      });

      jQuery('#cgui_form_0_field_'+(field_number+1)+'').html(options);
    }
  });
jQ;

    $ss->AddJsEvent("change", $jq);

    $frm->Label("Подмодуль", 10, $ypos += 20);
    $t = $frm->Select(25, $ypos += 20, 250, get_submodules($command_info['module_id']), '', $command_info['submodule_id']);
    $t->AddValidator(new CGUI_VALIDATOR_NOZERO());
    $t->linkName = "submodule_id";
    $ypos += 30;

    $frm->Label("Порядок отображения в меню", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 40, $command_info['order']);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->AddValidator(new CGUI_VALIDATOR_09);
    $t->linkName = "order";

    $frm->VLine(10, $ypos += 40, 280);
    $frm->Button("Сохранить", 40, $ypos += 20, 100, true);
    $b = $frm->Button("К списку", 160, $ypos, 100, false);
    $b->Event = "document.location.href='?section=admin&subsection=3&action=list'";
    $frm->height = $ypos + 60;
    break;

  case 'del':
    Command::delete($_REQUEST['command']);
    $GUI->OK("Команда удалена");
    page_reloadSubSec();
    break;

  default:
    break;
}