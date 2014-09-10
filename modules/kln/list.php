<?php

use Components\Classes\Roles;
use Components\Classes\db;

need_data('data_filials');

$Filter = $GUI->FltrCol("clients_filter", "data_users:conf_clientfltr");
$Filter->SrcTable = TABLE_CLIENTS;
$Filter->DstTable = "clients_tmp_" . $_SESSION["user"]["data"]["id"];

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Клиент";
$f->keyid = "id";
$f->SetSelectData(kln_getrawlist(), "fio");
$flt_kln_id = $f->id;

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Филиал";
$f->keyid = "filial_id";
$f->SetSelectData($data_filials, "name");

$f = $Filter->AddFilter("CGUI_FilterDate");
$f->name = "Дата регистрации";
$f->keyid = "regdate";

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Блокировка";
$f->keyid = "blocked";
$f->SetSelectData(array('нет', 'да'));

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Кто привел";
$f->keyid = "ref_id";
$f->SetSelectData(kln_getrawlist(), "fio");

$f = $Filter->AddFilter("CGUI_FilterSelect");
$f->name = "Кем добавлен";
$f->keyid = "added_by";
$f->SetSelectData($data_users, "fio");

$Filter->MakeUserSets(10);
$Filter->Requests();
$Filter->Filtering();

$pan1 = $GUI->UPanel();
$pan1->Caption = "Фильтры";
$pan1->defOpen = $Filter->OpenPanel;
$pan1->AddHTML($Filter->GetHTML());

$tbl = $GUI->Table("kln" . $n);
$tbl->Width = "100%";
$tbl->DataMYSQL($Filter->DstTable);

$where = '';
if ($_SESSION["user"]["data"]["group_id"] > 1) {
  $where .= "filial_id = " . $_SESSION["user"]["data"]["filial_id"];
}
$tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
  10,
  20,
  50,
  100,
  0
));

$sp = $GUI->UPanel();
$sp->Caption = "Поиск";
if (!empty($_REQUEST["kln_search"])) {
  $sp->defOpen = true;

  $searchWhere = '';
  if (!empty($_REQUEST['search_id'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= "id = '" . db::input($_REQUEST['search_id']) . "'";
  }

  if (!empty($_REQUEST['search_name'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= " fio LIKE '%" . db::input($_REQUEST['search_name']) . "%'";
  }

  if (!empty($_REQUEST['search_mail'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= " email LIKE '%" . db::input($_REQUEST['search_mail']) . "%'";
  }

  if (!empty($_REQUEST['search_phone'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= " telnum LIKE '%" . db::input($_REQUEST['search_phone']) . "%'";
  }

  if (!empty($_REQUEST['search_city'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= " city LIKE '%" . db::input($_REQUEST['search_city']) . "%'";
  }

  if (!empty($_REQUEST['search_referrer'])) {
    if (!empty($searchWhere)) {
      $searchWhere .= ' OR';
    }
    $searchWhere .= " referrer_code LIKE '%" . db::input($_REQUEST['search_referrer']) . "%'";
  }

  if (!empty($where) && !empty($searchWhere)) {
    $where .= ' AND (' . $searchWhere . ')';
  } elseif (empty($where) and !empty($searchWhere)) {
    $where = $searchWhere;
  }
}
$tbl->FilterMYSQL($where);


$sp->AddHTML("<div style='margin-left: 4px; margin-bottom: 5px; text-align:left'>");
$sp->AddHTML("<form method='post'>");
$sp->AddHTML("<input type='hidden' name='kln_search' value='1'>");
$sp->AddHTML("<label class='search_field'>по номеру<br/>");
$sp->AddHTML("<input type='text' name='search_id' style='width:100px;' value='" . (!empty($_REQUEST['search_id']) ? $_REQUEST['search_id'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<label class='search_field'>по имени<br/>");
$sp->AddHTML("<input type='text' name='search_name' style='width:100px;' value='" . (!empty($_REQUEST['search_name']) ? $_REQUEST['search_name'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<label class='search_field'>по почте<br/>");
$sp->AddHTML("<input type='text' name='search_mail' style='width:100px;' value='" . (!empty($_REQUEST['search_mail']) ? $_REQUEST['search_mail'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<label class='search_field'>по телефону<br/>");
$sp->AddHTML("<input type='text' name='search_phone' style='width:100px;' value='" . (!empty($_REQUEST['search_phone']) ? $_REQUEST['search_phone'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<label class='search_field'>по городу<br/>");
$sp->AddHTML("<input type='text' name='search_city' style='width:100px;' value='" . (!empty($_REQUEST['search_city']) ? $_REQUEST['search_city'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<label class='search_field'>по партнерскому коду<br/>");
$sp->AddHTML("<input type='text' name='search_referrer' style='width:100px;' value='" . (!empty($_REQUEST['search_referrer']) ? $_REQUEST['search_referrer'] : '') . "'>");
$sp->AddHTML("</label>");
$sp->AddHTML("<input type='submit' value='Искать' style='margin-left: 10px;margin-top: 17px;'>");
$sp->AddHTML("<input type='submit' value='Сброс' style='margin-left: 10px' onclick='document.location.href=\"?section=kln&subsection=2&kln_search=1\"; return false;'>");
$sp->AddHTML("</form>");
$sp->AddHTML("</div>");

if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Редактировать")) {
  $tbl->RowEvent2 = "document.location.href=\"?section=kln&subsection=2&edit=%var%\"";
}

$columns_resource = Roles::getColumns($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"]);

if (!is_resource($columns_resource)) {
  $GUI->ERR($columns_resource);
  page_reload();
}

$new_columns = array();
$column_group_name = array();
while ($row = db::fetch_array($columns_resource)) {
  if ($row['group_internal_name'] != "") {
    $column_group_name[] = $row['group_internal_name'];
    $new_columns[$row['group_internal_name']]['custom'][] = $row;
  } else {
    $new_columns[] = $row;
  }
}

foreach ($new_columns as $column) {
  if (isset($column['internal_name']) && in_array($column['internal_name'], $column_group_name)) {
    continue;
  }
  if (isset($column['custom']) && count($column['custom'])) {
    $r = $tbl->NewColumn();
    foreach ($column['custom'] as $custom_column) {
      $r1 = new CGUI_TableColumn();
      $r1->Caption = $custom_column['name'];
      $r1->DoSort = $custom_column['do_sort'];
      $r1->Key = $custom_column['internal_name'];
      $r1->Align = $custom_column['align'];
      $r1->Process = $custom_column['on_execute'];
      $r->Custom[] = $r1;
    }
  } else {
    $r = $tbl->NewColumn();
    $r->Caption = $column['name'];
    $r->DoSort = $column['do_sort'];
    $r->Key = $column['internal_name'];
    $r->Align = $column['align'];
    $r->Process = $column['on_execute'];
  }
}