<?php

use Components\Classes\db;

use Components\Entity\Order;
use Components\Entity\OrderHistory;
use Components\Entity\Employee;
use Components\Classes\Author;

$result = array();
if (!empty($_GET['action'])) {
  switch($_GET['action']) {
    case 'save':
      if (!empty($_GET['field'])) {
        $bDoUpdate = true;
        $bDoHistoryUpdate = true;
        switch($_GET['field']) {
          case 'status_id':
            $result[] = get_status_name($_GET['value']);
            $value = $_GET['value'];
            break;

          case 'referrer_payment_status':
            $bDoHistoryUpdate = false;
            $value = $_GET['value'];
            $result[] = 'Оплачено';
            Order::update($_GET['order_id'], array(
              'referrer_payment_date' => date('Y-m-d H:i:s'),
            ));
            break;

          case 'oplata_kln':
          case 'cost_kln':
          case 'cost_auth':
          case 'author_paid':
          case 'company_paid':
          case 'about_mng'://коментарий
            $result[] = $value = $_GET['value'];
            break;

          case 'time_auth':
            $value = utils_cvt_date2i(str_replace('.', '-', $_GET['value']));
            $result[] = $_GET['value'];

            $author_info = db::get_single_row("
              SELECT u.id, u.email, u.fio
              FROM " . TABLE_USERS . " u
              JOIN " . TABLE_ORDERS . " o ON o.author_id = u.id
              WHERE o.id = " . db::input($_GET['order_id']) . "
            ");

            if ($author_info) {
//              Author::sendEmail($_GET['order_id'], array($author_info['id']), '№' . $_GET['order_id'] . ' ИЗМЕНИЛАСЬ ДАТА', '№' . $_GET['order_id'] . ' ИЗМЕНИЛАСЬ ДАТА на ' . $_GET['value']);

              $message_id = mls_Send("u" . $author_info['id'], "u" . $_SESSION["user"]["data"]["id"], '№' . $_GET['order_id'] . ' ИЗМЕНИЛАСЬ ДАТА', '№' . $_GET['order_id'] . ' ИЗМЕНИЛАСЬ ДАТА на ' . $_GET['value'], 1, 0);
              Author::enqueue_message_to_email(
                $message_id,
                array($author_info['id']),
                \Components\Entity\EmailNotification::TO_AUTHOR_ON_ORDER_CHANGE
              );
            }
            break;

          case 'debt_to_author':
            $bDoUpdate = false;

            $orders = explode(',', $_GET['order_id']);
            foreach($orders as $order_id) {
              $order_id = trim($order_id);
              if (empty($order_id)) {
                continue;
              }

              $order_info = get_order_info($order_id);
              Order::update($order_id, array(
                'author_paid' => $order_info['cost_auth'],
              ));

              $data = array(
                'change_date' => time(),
                'change_user_id' => $_SESSION['user']['data']['id'],
                'order_id' => $order_id,
                'filial_id_new' => $order_info['filial_id'],
                'klient_id_new' => $order_info['klient_id'],
                'vuz_id_new' => $order_info['vuz_id'],
                'vuz_user_new' => $order_info['vuz_user'],
                'type_id_new' => $order_info['type_id'],
                'type_user_new' => $order_info['type_user'],
                'napr_id_new' => $order_info['napr_id'],
                'disc_id_new' => $order_info['disc_id'],
                'disc_user_new' => $order_info['disc_user'],
                'time_kln_new' => $order_info['time_kln'],
                'cost_kln_new' => $order_info['cost_kln'],
                'payment_id_new' => $order_info['payment_id'],
                'subject_new' => $order_info['subject'],
                'about_kln_new' => $order_info['about_kln'],
                'about_mng_new' => $order_info['about_mng'],
                'kurs_new' => $order_info['kurs'],
                'prakt_pc_new' => $order_info['prakt_pc'],
                'pages_min_new' => $order_info['pages_min'],
                'pages_max_new' => $order_info['pages_max'],
                'src_min_new' => $order_info['src_min'],
                'src_max_new' => $order_info['src_max'],
                'from_id_new' => $order_info['from_id'],
                'oform_new' => $order_info['oform'],
                'next_rel_date_new' => $order_info['next_rel_date'],
                'status_id_new' => $order_info['status_id'],
                'ok_comment_new' => $order_info['ok_comment'],
                'ok_comment_date_new' => $order_info['ok_comment_date'],
                'payment_comment_new' => $order_info['payment_comment'],
                'cost_auth_new' => $order_info['cost_auth'],
                'time_auth_new' => $order_info['time_auth'],
                'oplata_kln_new' => $order_info['oplata_kln'],
                'author_paid_new' => $order_info['cost_auth'],
                'company_paid_new' => $order_info['company_paid'],
                'filial_id_old' => $order_info['filial_id'],
                'klient_id_old' => $order_info['klient_id'],
                'vuz_id_old' => $order_info['vuz_id'],
                'vuz_user_old' => $order_info['vuz_user'],
                'type_id_old' => $order_info['type_id'],
                'type_user_old' => $order_info['type_user'],
                'napr_id_old' => $order_info['napr_id'],
                'disc_id_old' => $order_info['disc_id'],
                'disc_user_old' => $order_info['disc_user'],
                'time_kln_old' => $order_info['time_kln'],
                'cost_kln_old' => $order_info['cost_kln'],
                'payment_id_old' => $order_info['payment_id'],
                'subject_old' => $order_info['subject'],
                'about_kln_old' => $order_info['about_kln'],
                'about_mng_old' => $order_info['about_mng'],
                'kurs_old' => $order_info['kurs'],
                'prakt_pc_old' => $order_info['prakt_pc'],
                'pages_min_old' => $order_info['pages_min'],
                'pages_max_old' => $order_info['pages_max'],
                'src_min_old' => $order_info['src_min'],
                'src_max_old' => $order_info['src_max'],
                'from_id_old' => $order_info['from_id'],
                'oform_old' => $order_info['oform'],
                'next_rel_date_old' => $order_info['next_rel_date'],
                'status_id_old' => $order_info['status_id'],
                'ok_comment_old' => $order_info['ok_comment'],
                'ok_comment_date_old' => $order_info['ok_comment_date'],
                'payment_comment_old' => $order_info['payment_comment'],
                'cost_auth_old' => $order_info['cost_auth'],
                'time_auth_old' => $order_info['time_auth'],
                'oplata_kln_old' => $order_info['oplata_kln'],
                'author_paid_old' => $order_info['author_paid'],
                'company_paid_old' => $order_info['company_paid'],
              );

              OrderHistory::create($data);
            }
            $result[] = 0;
            break;

          case 'debt_to_company':
            $bDoUpdate = false;

            $orders = explode(',', $_GET['order_id']);
            foreach($orders as $order_id) {
              $order_id = trim($order_id);
              if (empty($order_id)) {
                continue;
              }

              $order_info = get_order_info($order_id);

              $company_paid = calculate_debt_to_company($order_info['cost_kln'], $order_info['cost_auth'], $order_info['filial_id']);
              Order::update($order_id, array(
                'company_paid' => $company_paid,
              ));

              $data = array(
                'change_date' => time(),
                'change_user_id' => $_SESSION['user']['data']['id'],
                'order_id' => $order_id,
                'filial_id_new' => $order_info['filial_id'],
                'klient_id_new' => $order_info['klient_id'],
                'vuz_id_new' => $order_info['vuz_id'],
                'vuz_user_new' => $order_info['vuz_user'],
                'type_id_new' => $order_info['type_id'],
                'type_user_new' => $order_info['type_user'],
                'napr_id_new' => $order_info['napr_id'],
                'disc_id_new' => $order_info['disc_id'],
                'disc_user_new' => $order_info['disc_user'],
                'time_kln_new' => $order_info['time_kln'],
                'cost_kln_new' => $order_info['cost_kln'],
                'payment_id_new' => $order_info['payment_id'],
                'subject_new' => $order_info['subject'],
                'about_kln_new' => $order_info['about_kln'],
                'about_mng_new' => $order_info['about_mng'],
                'kurs_new' => $order_info['kurs'],
                'prakt_pc_new' => $order_info['prakt_pc'],
                'pages_min_new' => $order_info['pages_min'],
                'pages_max_new' => $order_info['pages_max'],
                'src_min_new' => $order_info['src_min'],
                'src_max_new' => $order_info['src_max'],
                'from_id_new' => $order_info['from_id'],
                'oform_new' => $order_info['oform'],
                'next_rel_date_new' => $order_info['next_rel_date'],
                'status_id_new' => $order_info['status_id'],
                'ok_comment_new' => $order_info['ok_comment'],
                'ok_comment_date_new' => $order_info['ok_comment_date'],
                'payment_comment_new' => $order_info['payment_comment'],
                'cost_auth_new' => $order_info['cost_auth'],
                'time_auth_new' => $order_info['time_auth'],
                'oplata_kln_new' => $order_info['oplata_kln'],
                'author_paid_new' => $order_info['author_paid'],
                'company_paid_new' => $company_paid,
                'filial_id_old' => $order_info['filial_id'],
                'klient_id_old' => $order_info['klient_id'],
                'vuz_id_old' => $order_info['vuz_id'],
                'vuz_user_old' => $order_info['vuz_user'],
                'type_id_old' => $order_info['type_id'],
                'type_user_old' => $order_info['type_user'],
                'napr_id_old' => $order_info['napr_id'],
                'disc_id_old' => $order_info['disc_id'],
                'disc_user_old' => $order_info['disc_user'],
                'time_kln_old' => $order_info['time_kln'],
                'cost_kln_old' => $order_info['cost_kln'],
                'payment_id_old' => $order_info['payment_id'],
                'subject_old' => $order_info['subject'],
                'about_kln_old' => $order_info['about_kln'],
                'about_mng_old' => $order_info['about_mng'],
                'kurs_old' => $order_info['kurs'],
                'prakt_pc_old' => $order_info['prakt_pc'],
                'pages_min_old' => $order_info['pages_min'],
                'pages_max_old' => $order_info['pages_max'],
                'src_min_old' => $order_info['src_min'],
                'src_max_old' => $order_info['src_max'],
                'from_id_old' => $order_info['from_id'],
                'oform_old' => $order_info['oform'],
                'next_rel_date_old' => $order_info['next_rel_date'],
                'status_id_old' => $order_info['status_id'],
                'ok_comment_old' => $order_info['ok_comment'],
                'ok_comment_date_old' => $order_info['ok_comment_date'],
                'payment_comment_old' => $order_info['payment_comment'],
                'cost_auth_old' => $order_info['cost_auth'],
                'time_auth_old' => $order_info['time_auth'],
                'oplata_kln_old' => $order_info['oplata_kln'],
                'author_paid_old' => $order_info['author_paid'],
                'company_paid_old' => $order_info['company_paid'],
              );

              OrderHistory::create($data);
            }
            $result[] = 0;
            break;

          case 'color':
            $bDoUpdate = false;

            $currentColors = db::get_single_value("SELECT conf_ord_colors FROM " . TBL_PREF . "data_users WHERE id = " . $_SESSION['user']['data']['id']);

            if (empty($currentColors)) {
              $currentColors = array();
            } else {
              $currentColors = unserialize($currentColors);
            }

            $currentColors[$_GET['order_id']] = $_GET['value'];

            $currentColors = serialize($currentColors);
            Employee::update($_SESSION['user']['data']['id'], array(
              'conf_ord_colors' => $currentColors,
            ));
            $_SESSION['user']['data']['conf_ord_colors'] = $currentColors;
            break;

          case 'referrer_payment_status_all':
            $bDoUpdate = false;

            $orders = explode(',', $_GET['order_id']);
            foreach($orders as $order_id) {
              $order_id = trim($order_id);
              if (empty($order_id)) {
                continue;
              }

              $order_info = get_order_info($order_id);

              if ($order_info['referrer_payment_status'] == 0) {
                Order::update($order_id, array(
                  'referrer_payment_status' => 1,
                  'referrer_payment_date' => date('Y-m-d H:i:s'),
                ));
              }
            }
            $result[] = 'Оплачено';
            break;
        }

        if ($bDoUpdate) {
          Order::update($_GET['order_id'], array(
            $_GET['field'] => $value,
          ));

          $order_info = Order::find($_GET['order_id']);

          if ($bDoHistoryUpdate) {
            $data = array(
              'change_date' => time(),
              'change_user_id' => $_SESSION['user']['data']['id'],
              'order_id' => $_GET['order_id'],
              'filial_id_new' => $order_info['filial_id'],
              'klient_id_new' => $order_info['klient_id'],
              'vuz_id_new' => $order_info['vuz_id'],
              'vuz_user_new' => $order_info['vuz_user'],
              'type_id_new' => $order_info['type_id'],
              'type_user_new' => $order_info['type_user'],
              'napr_id_new' => $order_info['napr_id'],
              'disc_id_new' => $order_info['disc_id'],
              'disc_user_new' => $order_info['disc_user'],
              'time_kln_new' => $order_info['time_kln'],
              'cost_kln_new' => $order_info['cost_kln'],
              'payment_id_new' => $order_info['payment_id'],
              'subject_new' => $order_info['subject'],
              'about_kln_new' => $order_info['about_kln'],
              'about_mng_new' => $order_info['about_mng'],
              'kurs_new' => $order_info['kurs'],
              'prakt_pc_new' => $order_info['prakt_pc'],
              'pages_min_new' => $order_info['pages_min'],
              'pages_max_new' => $order_info['pages_max'],
              'src_min_new' => $order_info['src_min'],
              'src_max_new' => $order_info['src_max'],
              'from_id_new' => $order_info['from_id'],
              'oform_new' => $order_info['oform'],
              'next_rel_date_new' => $order_info['next_rel_date'],
              'status_id_new' => $order_info['status_id'],
              'ok_comment_new' => $order_info['ok_comment'],
              'ok_comment_date_new' => $order_info['ok_comment_date'],
              'payment_comment_new' => $order_info['payment_comment'],
              'cost_auth_new' => $order_info['cost_auth'],
              'time_auth_new' => $order_info['time_auth'],
              'oplata_kln_new' => $order_info['oplata_kln'],
              'author_paid_new' => $order_info['author_paid'],
              'company_paid_new' => $order_info['company_paid'],
              'filial_id_old' => $order_info['filial_id'],
              'klient_id_old' => $order_info['klient_id'],
              'vuz_id_old' => $order_info['vuz_id'],
              'vuz_user_old' => $order_info['vuz_user'],
              'type_id_old' => $order_info['type_id'],
              'type_user_old' => $order_info['type_user'],
              'napr_id_old' => $order_info['napr_id'],
              'disc_id_old' => $order_info['disc_id'],
              'disc_user_old' => $order_info['disc_user'],
              'time_kln_old' => $order_info['time_kln'],
              'cost_kln_old' => $order_info['cost_kln'],
              'payment_id_old' => $order_info['payment_id'],
              'subject_old' => $order_info['subject'],
              'about_kln_old' => $order_info['about_kln'],
              'about_mng_old' => $order_info['about_mng'],
              'kurs_old' => $order_info['kurs'],
              'prakt_pc_old' => $order_info['prakt_pc'],
              'pages_min_old' => $order_info['pages_min'],
              'pages_max_old' => $order_info['pages_max'],
              'src_min_old' => $order_info['src_min'],
              'src_max_old' => $order_info['src_max'],
              'from_id_old' => $order_info['from_id'],
              'oform_old' => $order_info['oform'],
              'next_rel_date_old' => $order_info['next_rel_date'],
              'status_id_old' => $order_info['status_id'],
              'ok_comment_old' => $order_info['ok_comment'],
              'ok_comment_date_old' => $order_info['ok_comment_date'],
              'payment_comment_old' => $order_info['payment_comment'],
              'cost_auth_old' => $order_info['cost_auth'],
              'time_auth_old' => $order_info['time_auth'],
              'oplata_kln_old' => $order_info['oplata_kln'],
              'author_paid_old' => $order_info['author_paid'],
              'company_paid_old' => $order_info['company_paid'],
            );

            $data[$_GET['field'] . '_new'] = $_GET['value'];

            OrderHistory::create($data);
          }
        }
      }
      break;

    case 'get':
      if (!empty($_GET['field'])) {
        switch($_GET['field']) {
          case 'status_id':
            $result[] = db::get_select("SELECT id, status_name FROM " . TBL_PREF . "orders_status", 'status', '', $_GET['value'], 'class="instantEditNewValue"');
            break;

          case 'referrer_payment_status':
            $result[] = 'Оплатить?<input type="hidden" value="1" class="instantEditNewValue"/>';
            break;

          case 'oplata_kln':
          case 'cost_kln':
          case 'cost_auth':
          case 'author_paid':
          case 'company_paid':
            $result[] = '<input type="text" class="instantEditNewValue" value="' . $_GET['value'] . '"/>';
            break;

          case 'time_auth':
            $result[] = '<input type="text" class="instantEditNewValue" value="' . _get_fmt_date($_GET['value']) . '"/>';
            break;

          case 'debt_to_author':
          case 'referrer_payment_status_all':
            $result[] = 'Оплатить все?';
            break;

          case 'debt_to_company':
            $result[] = 'Списать весь долг?';
            break;

          case 'about_mng'://коментарий
            $result[] = '<textarea class="instantEditNewValue">' . $_GET['value'] . '</textarea>';
            break;
        }
      }
      break;
  }
}

echo join("\n", $result);
die;