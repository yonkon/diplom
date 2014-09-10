<?php

use Components\Entity\Column;

$GUI->cmdmenu->AddItem("Список", "?section=admin&subsection=4&action=list");
$GUI->cmdmenu->AddItem("Добавить", "?section=admin&subsection=4&action=add");

$action = 'list';
if (isset($_GET['action']) && !empty($_GET['action'])) {
  $action = $_GET['action'];
}
switch ($action) {
  case 'list':
    $GUI->mmenu->selected->selected->caption = "Список колонок";

    $tbl = $GUI->Table("submodule_columns" . $n);
    $tbl->Width = "100%";
    $tbl->DataMYSQL("submodule_columns");

    $tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
      10, 20, 50, 100, 0
    ));

    $tbl->RowEvent2 = "document.location.href=\"?section=admin&subsection=4&action=edit&column=%var%\"";

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
    $r->Caption = "Внутренее имя";
    $r->Align = "left";
    $r->DoSort = true;
    $r->Key = "internal_name";

    $r = $tbl->NewColumn();
    $r->Caption = "Порядок";
    $r->Align = "left";
    $r->Key = "order";

    $r = $tbl->NewColumn();
    $r->Caption = "Обработчик";
    $r->Align = "left";
    $r->Key = "on_execute";

    $r = $tbl->NewColumn();
    $r->Caption = "Выравнивание";
    $r->Align = "left";
    $r->Key = "align";

    $r = $tbl->NewColumn();
    $r->Caption = "Возможность сортировки";
    $r->Align = "left";
    $r->Key = "do_sort";
    $r->Process = "yes_or_no";

    $r = $tbl->NewColumn();
    $r->Caption = "Внутренее имя группы колонок";
    $r->Align = "left";
    $r->Key = "group_internal_name";
    break;

  case 'add':
    $ypos = 0;
    $frm = $GUI->Form("Добавит новую колонку", 300, 0);
    $frm->OnExecute = "add_column";

    $frm->Label("Имя", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250);
    $t->linkName = "name";
    $ypos += 30;

    $frm->Label("Внутренее имя", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250);
    $t->linkName = "internal_name";
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

    $frm->Label("Порядок отображения", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 40);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->AddValidator(new CGUI_VALIDATOR_09);
    $t->linkName = "order";

    $frm->Label("Функция обработчик", 10, $ypos += 30);
    $t = $frm->Text(25, $ypos += 20, 250);
    $t->linkName = "on_execute";

    $frm->Label("Выравнивание", 10, $ypos += 30);
    $t = $frm->Text(25, $ypos += 20, 250);
    $t->linkName = "align";

    $frm->Label("Сортировка", 10, $ypos += 30);
    $t = $frm->Checkbox(100, $ypos, false, 1);
    $t->linkName = "do_sort";

    $frm->Label("Внутренее имя группы колонок", 10, $ypos += 30);
    $t = $frm->Text(25, $ypos += 20, 250);
    $t->linkName = "group_internal_name";

    $frm->VLine(10, $ypos += 40, 280);
    $frm->Button("Сохранить", 40, $ypos += 20, 100, true);
    $b = $frm->Button("К списку", 160, $ypos, 100, false);
    $b->Event = "document.location.href='?section=admin&subsection=4&action=list'";
    $frm->height = $ypos + 60;
    break;

  case 'edit':
    $column_id = $_GET['column'];

    $GUI->cmdmenu->AddItem("Удалить", "?section=admin&subsection=4&action=del&column=" . $column_id);

    $ypos = 0;
    $frm = $GUI->Form("Редактировать колонку №" . $column_id, 300, 0);
    $frm->OnExecute = "edit_column";

    $t = $frm->Hidden($column_id);
    $t->linkName = 'id';

    $column_info = Column::find($column_id);

    $frm->Label("Имя", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250, $column_info['name']);
    $t->linkName = "name";
    $ypos += 30;

    $frm->Label("Внутренее имя", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 250, $column_info['internal_name']);
    $t->linkName = "internal_name";
    $ypos += 30;

    $frm->Label("Родительский модуль", 10, $ypos += 20);
    $ss = $frm->Select(25, $ypos += 20, 250, get_modules(true), '', $column_info['module_id']);
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
    $t = $frm->Select(25, $ypos += 20, 250, get_submodules($column_info['module_id']), '', $column_info['submodule_id']);
    $t->AddValidator(new CGUI_VALIDATOR_NOZERO());
    $t->linkName = "submodule_id";
    $ypos += 30;

    $frm->Label("Порядок отображения в меню", 10, $ypos += 20);
    $t = $frm->Text(25, $ypos += 20, 40, $column_info['order']);
    $t->AddValidator(new CGUI_VALIDATOR_NOEMPTY);
    $t->AddValidator(new CGUI_VALIDATOR_09);
    $t->linkName = "order";

    $frm->Label("Функция обработчик", 10, $ypos += 30);
    $t = $frm->Text(25, $ypos += 20, 250, $column_info['on_execute']);
    $t->linkName = "on_execute";

    $frm->Label("Выравнивание", 10, $ypos += 30);
    $t = $frm->Text(25, $ypos += 20, 250, $column_info['align']);
    $t->linkName = "align";

    $frm->Label("Сортировка", 10, $ypos += 30);
    $t = $frm->Checkbox(100, $ypos, $column_info['do_sort'], 1);
    $t->linkName = "do_sort";

    $frm->Label("Внутренее имя группы колонок", 10, $ypos += 30);
    $t = $frm->Text(25, $ypos += 20, 250, $column_info['group_internal_name']);
    $t->linkName = "group_internal_name";

    $frm->VLine(10, $ypos += 40, 280);
    $frm->Button("Сохранить", 40, $ypos += 20, 100, true);
    $b = $frm->Button("К списку", 160, $ypos, 100, false);
    $b->Event = "document.location.href='?section=admin&subsection=4&action=list'";
    $frm->height = $ypos + 60;
    break;

  case 'del':
    Column::delete($_REQUEST['column']);
    $GUI->OK("Колонка удалена");
    page_reloadSubSec();
    break;

  default:
    break;
}