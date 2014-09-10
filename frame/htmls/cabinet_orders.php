<?php

use Components\Entity\Order;
use Components\Exceptions\Exception;
use Components\Classes\db;
use Components\Entity\OrderFile;
use Components\Entity\Message;

if (!is_client_logged() || $_SESSION["frame"]["client"]["blocked"]) {
  echo 'Доступ запрещен.';
} else {
  if (isset($_REQUEST["order"])) {
    try {
      $order = Order::find($_REQUEST["order"]);
    } catch(Exception $e) {
      redirect('/frame?type=cabinet');
    }

    if (isset($_POST['add_file'])) {
      if (is_uploaded_file($_FILES["zf_work_file"]["tmp_name"])) {
        // Формируем новое имя файла
        $path = TMPFILES_PATH . session_id();
        if (!file_exists($path))
          @mkdir($path);
        if (file_exists($path)) {
          $new_name = $path . "/" . $_FILES["zf_work_file"]["name"];
          if (file_exists($new_name))
            unlink($new_name);
          move_uploaded_file($_FILES["zf_work_file"]["tmp_name"], $new_name);
        }
      }

      $files = check_user_files();
      if (count($files)) {
        $path = "../order_files/" . $order['id'];
        if (!file_exists($path))
          mkdir($path);

        foreach ($files as $f) {
          $fid = OrderFile::create(array(
            'order_id' => $order['id'],
            'creator_id' => 0,
            'created' => time(),
            'name' => $f["name"],
            'size' => $f["size"],
          ));

          if ($fid > 0) {
            $ext = substr($f["name"], strrpos($f["name"], ".") + 1);
            $f_s = fopen($f["path"], "r");
            $f_d = fopen($path . "/" . $fid . "." . $ext, "w");
            fwrite($f_d, fread($f_s, $f["size"]));
            fclose($f_s);
            fclose($f_d);
          }
          unlink($f["path"]);
        }
      }
    }

    $type = "<i>неизвестно</i>";
    $napr = "<i>неизвестно</i>";
    $disc = "<i>неизвестно</i>";
    $status = "<i>неизвестно</i>";

    if ($order["status_id"]) {
      $status = get_status_name($order["status_id"]);
    }

    if ($order['type_id']) {
      $type = get_worktype_name($order['type_id']);
    } else {
      $type = $order['type_user'];
    }

    if ($order["napr_id"]) {
      $napr = get_naprav_name($order["napr_id"]);
    }

    if ($order['disc_id']) {
      $disc = get_discipline_name($order['disc_id']);
    } else {
      $disc = $order['disc_user'];
    }

    $cost = $order["cost_kln"];
    if ($cost == 0)
      $cost = "оценка";

    print "<div style='margin-bottom: 5px'><a href='?type=cabinet'><< к списку заказов</a></div>" . "<div style='font-size: 12pt; font-weight: bold; margin-bottom: 10px;'>" . $order["subject"] . "</div>" . "<div class='cab_ord_row'><div class='cab_ord_row_capt'>Номер заказа:</div><div class='cab_ord_row_val'>" . $order["id"] . "</div><div class='clear'></div></div>" . "<div class='cab_ord_row'><div class='cab_ord_row_capt'>Вид работы:</div><div class='cab_ord_row_val'>" . $type . "</div><div class='clear'></div></div>" . "<div class='cab_ord_row'><div class='cab_ord_row_capt'>Направление:</div><div class='cab_ord_row_val'>" . $napr . "</div><div class='clear'></div></div>" . "<div class='cab_ord_row'><div class='cab_ord_row_capt'>Дисциплина:</div><div class='cab_ord_row_val'>" . $disc . "</div><div class='clear'></div></div>" . "<div class='cab_ord_row'><div class='cab_ord_row_capt'>Принят:</div><div class='cab_ord_row_val'>" . date("d.m.Y", $order["created"]) . "</div><div class='clear'></div></div>" . "<div class='cab_ord_row'><div class='cab_ord_row_capt'>Дата сдачи:</div><div class='cab_ord_row_val'>" . date("d.m.Y", $order["time_kln"]) . "</div><div class='clear'></div></div>" . "<div class='cab_ord_row'><div class='cab_ord_row_capt'>Статус:</div><div class='cab_ord_row_val'>" . $status . "</div><div class='clear'></div></div>" . "<div class='cab_ord_row'><div class='cab_ord_row_capt'>Стоимость, руб.:</div><div class='cab_ord_row_val'>" . $cost . "</div><div class='clear'></div></div>" . "<div class='cab_ord_row'><div class='cab_ord_row_capt'>Оплачено, руб.:</div><div class='cab_ord_row_val'>" . $order["oplata_kln"] . "</div><div class='clear'></div></div>" . "<div class='cab_ord_row'><div class='cab_ord_row_capt'>Требования:</div><div class='cab_ord_row_val'>" . $order["about_kln"] . "</div><div class='clear'></div></div>";

    $html = array();
    $files = get_order_files($order["id"], 0);
    if (count($files)) {
      $html[] = '<p style="margin-top: 20px;">Файлы</p>';
      $html[] = '<table cellpadding="4" cellspacing="1" width="100%">';
      $html[] = '<tr class="header">';
      $html[] = '<td>Название</td>';
      $html[] = '<td>Размер</td>';
      $html[] = '<td>Дата добавления</td>';
      $html[] = '<td>Скачать</td>';
      $html[] = '</tr>';
      foreach ($files as $file) {
        $html[] = '<tr>';
        $html[] = '<td>' . $file['name'] . '</td>';
        $html[] = '<td>' . get_file_size($file['size']) . '</td>';
        $html[] = '<td>' . _get_fmt_date_time($file['created']) . '</td>';
        $html[] = '<td>' . generate_file_link_for_frame($file) . '</td>';
        $html[] = '</tr>';
      }
      $html[] = '</table>';
    }

    print join("\n", $html);

    print '
    <form id="zakaz_form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="add_file">
		<div style="height:50px; margin-bottom: 20px;margin-top: 20px; width: 580px;">
			<div>Если имеются методические указания и материалы прикрепите файл (макс. размер 1го файла = ' . ini_get("upload_max_filesize") . ')</div>
			<div id="add_file_field" style="margin-top: 5px">
           		<input type=file name="zf_work_file" value="Выбрать" onchange="mkorder_add_file_change(this)">
			</div>
		</div>
		</form>';

    if ($order["manager_id"]) {
      if (isset($_REQUEST["cab_msg_answer"])) {
        $t = clearText($_REQUEST["cab_msg_answer"]);
        $t = substr($t, 0, 1000);

        if (strlen($t)) {
          $cid = $_SESSION["frame"]["client"]["id"];
          $sbj = "Сообщение от клиента по заказу " . $order["id"];
          $message_id = Message::create(array(
            "klient_id" => $cid,
            "order_id" => $order["id"],
            "created" => time(),
            "addr" => "u" . $order["manager_id"],
            "creator_id" => "k" . $cid,
            "subject" => $sbj,
            "text" => $t,
            "prior" => 1,
          ));

          if ($message_id) {
            \Components\Classes\Author::enqueue_message_to_email($message_id, $order['manager_id'], \Components\Entity\EmailNotificationType::TO_MANAGER_ON_ORDER_CHANGE);
            $_SESSION["cab_ord_answer_info"] = "<span style='color:green'>Сообщение отправлено</span>";
          } else {
            $_SESSION["cab_ord_answer_info"] = "<span style='color:red'>Не удалось отправить сообщение</span>";
          }

          header("location: ?type=cabinet&order=" . $order["id"]);
          die();
        } else $_SESSION["cab_ord_answer_info"] = "<span style='color:red;'>Введите текст сообщения</span>";
      }

      print "<script>
	jQuery(function(){
	jQuery('#cab_msg_answer').bind('focus', function(){
		jQuery('#cab_msg_answer_btn').removeAttr('disabled');
		jQuery('#cab_msg_answer').css('color', 'black');
		jQuery('#cab_msg_answer').text('');
		jQuery('#cab_msg_answer').unbind('focus');
	});
});
</script>
<div style='margin-top: 10px'>";

      if (strlen(@$_SESSION["cab_ord_answer_info"])) {
        print "<div style='margin-bottom: 5px;'>" . $_SESSION["cab_ord_answer_info"] . "</div>";
        $_SESSION["cab_ord_answer_info"] = "";
      } else print "<div>Отправить сообщение менеджеру</div>";

      print "<form method='post'>" . "<div style='margin-bottom: 10px;'>" . "<textarea id='cab_msg_answer' name='cab_msg_answer' maxlength='1000' style='color: silver; width: 100%; height: 100px;'>Сообщение</textarea>" . "</div><input type='submit' id='cab_msg_answer_btn' disabled='disabled' value='Отправить'></form>";

    }
  } else {
    if (count(Order::findBy(array(
      'klient_id' => $_SESSION["frame"]["client"]["id"],
    )))) {
      include_once(DIR_FS_DOCUMENT_ROOT . "/gui/gui.php");
      include_once(DIR_FS_MODULES . "/ord/functions.php");
      $headers_already_printer = true;

      $tbl = $GUI->Table("cabinet_orders" . $_SESSION["frame"]["client"]["id"]);
      $tbl->Width = "100%";
      $tbl->DataMYSQL('orders');
      $tbl->FilterMYSQL('klient_id = ' . db::input($_SESSION["frame"]["client"]["id"]));
      $tbl->Pager(CGUI_PAGER_FLAG_SEL | CGUI_PAGER_FLAG_RR | CGUI_PAGER_FLAG_R | CGUI_PAGER_FLAG_FF | CGUI_PAGER_FLAG_F, 10, array(
        10,
        20,
        50,
        100,
        0
      ));
      $tbl->RowEvent2 = "document.location.href=\"?order=%var%\"";

      $r = $tbl->NewColumn();
      $r->Caption = '№';
      $r->DoSort = true;
      $r->Key = 'id';

      $r = $tbl->NewColumn();
      $r->Caption = 'Вид работы';
      $r->Key = 'type_id';
      $r->Process = '_get_worktype_name';

      $r = $tbl->NewColumn();
      $r->Caption = 'Тема';
      $r->Key = 'subject';

      $r = $tbl->NewColumn();
      $r->Caption = 'Статус';
      $r->Key = 'status_id';
      $r->Process = '_get_status_name';
      $r->DoSort = true;

      $r = $tbl->NewColumn();
      $r->DoSort = true;
      $r->Caption = 'Дата сдачи';
      $r->Key = 'time_kln';
      $r->Process = '_get_fmt_date';

      $r = $tbl->NewColumn();
      $r->DoSort = true;
      $r->Caption = 'Стоимость';
      $r->Key = 'cost_kln';
      $r->Process = '_get_fmt_cost';

      $r = $tbl->NewColumn();
      $r->DoSort = true;
      $r->Caption = 'Оплачено';
      $r->Key = 'oplata_kln';
      $r->Process = '_get_fmt_cost';

      $r = $tbl->NewColumn();
      $r->Caption = 'Долг';
      $r->Process = 'get_client_debt';

      echo '<div style="margin-top: 10px;" class="gui_style">';
      echo $GUI->tables[0]->PrintTable();
      echo '</div>';

      $stat_tbl = $GUI->Table("cabinet_orders_stats" . $_SESSION["frame"]["client"]["id"]);
      $stat_tbl->Width = "50%";

      $column = $stat_tbl->NewColumn();
      $column->Caption = "Итого";
      $column->Key = "id";

      $column = $stat_tbl->NewColumn();
      $column->Caption = "Стоимость";
      $column->Key = "client_price";

      $column = $stat_tbl->NewColumn();
      $column->Caption = "Оплачено";
      $column->Key = "client_payed";

      $column = $stat_tbl->NewColumn();
      $column->Caption = "Долг";
      $column->Key = "client_debt";

      $result = array(
        'id' => '',
        'client_price' => 0,
        'client_payed' => 0,
        'client_debt' => 0,
      );

      foreach (db::get_arrays("SELECT cost_kln, oplata_kln FROM " . TABLE_ORDERS . " WHERE klient_id = " . db::input($_SESSION["frame"]["client"]["id"])) as $row) {
        $result['client_price'] += $row['cost_kln'];
        $result['client_payed'] += $row['oplata_kln'];
        $result['client_debt'] += $row['cost_kln'] - $row['oplata_kln'];
      }
      $stat_tbl->AddRow($result, "id");

      echo '<center>';
      echo $GUI->tables[1]->PrintTable();
      echo '</center>';
    } else {
      echo "У Вас нет заказов";
    }
  }
}

function get_client_debt($value, $row, $table, &$info) {
  return ($row['cost_kln'] - $row['oplata_kln']);
}

function _get_status_name($value, $row, $table, &$info) {
  $status_name = get_status_name($value);
  if (empty($status_name)) {
    return '<i>неизвестно</i>';
  } else {
    return $status_name;
  }
}

function generate_file_link_for_frame($file) {
  $extension = pathinfo($file['name']);
  if (!empty($extension['extension'])) {
    $extension = strtolower($extension['extension']);
  } else {
    $extension = '';
  }
  $result = '';
  if ($file['creator_id'] != 0) {
    return $result;
  }
  switch($extension) {
    case 'jpg':
    case 'jpeg':
    case 'gif':
    case 'png':
      $result .= "[<a href='" . SITE_URL . "index.php?section=ord&subsection=2&p=4&order=" . $file["order_id"] . "&file=" . $file["id"] . "&action=download'>скачать</a>]/[<a href='" . SITE_URL . "index.php?section=ord&subsection=2&p=4&order=" . $file["order_id"] . "&file=" . $file["id"] . "&action=view'>просмотреть</a>]";
      break;
    default:
      $result .= "[<a href='" . SITE_URL . "index.php?section=ord&subsection=2&p=4&order=" . $file["order_id"] . "&file=" . $file["id"] . "&action=download'>скачать</a>]";
      break;
  }
  return $result;
}