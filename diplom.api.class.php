<?php

class diplom {
  public static function create_order($order_parameters) {
    $result = array(
      'status' => false,
      'msg' => '',
    );

    $message = array();
    ?>
<script type="javascript">
    var int, string;
    var create_order =
{
action: 'create_order',
params:
    {
        //Обязательные
        client_id: int,
        work: int,      //Вид работы, либо передавать id, либо строку параметром work_usr
        work_usr: string,
        disc: int,      //Аналогично для дисциплины
        dsic_usr: string,
        pgmin: int,     //Кол-во страниц
        pgmax: int,
        srcmin, int,    //Кол-во источников
        srcmax, int,
        //Необязательные
        filial_id: int,
        vuz : int,
        vuz_usr : string,
        napr: int,      //Направление обучения
        time_kln: int,  //Время сдачи, Unix timestamp
        kurs: int,      //Курс обучения
        prakt: int,     //Процент практической части
        treb: string,   //Требования к работе
        subj: string,   //Тема работы
        opl, int        //ID оплаты
    }
}
//Возвращает при ошибке | успехе
// {
//  status: false | true,
//  msg: <текст ошибок> | 'OK',
//  params: undefined | {id : <order_id>, date : <unix timestamp> },
// }
// Загрузка файлов происходит из стандартного массива $_FILES, т.е. скормить ссылку на файл не получится

</script>
<?
    if (empty($order_parameters['client_id'])) {
      $result['msg'] = "Id клиента не указан";
      return $result;
    }

    if ($order_parameters['work'] == 0 && !strlen($order_parameters['work_usr'])) {
      $message[] = "Не указан вид работы";
    }

    if ($order_parameters['disc'] == 0 && !strlen($order_parameters['disc_usr'])) {
      $message[] = "Не указана дисциплина";
    }

    if ($order_parameters['pgmax'] && ($order_parameters['pgmax'] < $order_parameters['pgmin'])) {
      $message[] = "Неверно указано макс. число страниц";
    }

    if ($order_parameters['srcmax'] && ($order_parameters['srcmax'] < $order_parameters['srcmin'])) {
      $message[] = "Неверно указано макс. число источников";
    }

    if (count($message)) {
      $result['msg'] = join("\n", $message);
      return $result;
    }

    $date = mktime();

    $filial_id = db_get_single_value("SELECT filial_id FROM " . TBL_PREF . "clients WHERE id = " . db_input($order_parameters['client_id']) . "");

    if (!$filial_id) {
      $filial_id = $order_parameters['filial_id'];
    }

    $order_id = Order::create(array(
      "filial_id" => $filial_id,
      "klient_id" => $order_parameters['client_id'],
      "vuz_id" => $order_parameters['vuz'],
      "vuz_user" => $order_parameters['vuz_usr'],
      "type_id" => $order_parameters['work'],
      "type_user" => $order_parameters['work_usr'],
      "napr_id" => $order_parameters['napr'],
      "disc_id" => $order_parameters['disc'],
      "disc_user" => $order_parameters['disc_usr'],
      "time_kln" => $order_parameters['time_kln'],// Дата сдачи
      "payment_id" => $order_parameters['opl'],
      "subject" => $order_parameters['subj'],
      "about_kln" => $order_parameters['treb'],//treb
      "kurs" => $order_parameters['kurs'],
      "prakt_pc" => $order_parameters['prakt'],
      "pages_min" => $order_parameters['pgmin'],
      "pages_max" => $order_parameters['pgmax'],
      "src_min" => $order_parameters['srcmin'],
      "src_max" => $order_parameters['srcmax'],
      "from_id" => 4,
    ));

    foreach ($_FILES as $file) {
      if (is_uploaded_file($file["tmp_name"])) {
        $extension = pathinfo($file['name']);
        $extension = strtolower($extension['extension']);

        $file_id = Order::attachFile($order_id, 0, $file["name"], $file["size"]);

        if (!$file_id) {
          $result['msg'] = "Ошибка при загрузке файла";
          return $result;
        } else {
          $dir = DIR_FS_ORDER_FILES . $order_id . '/';
          if (!is_dir(DIR_FS_ORDER_FILES)) {
            create_path('order_files', DIR_FS_DOCUMENT_ROOT);
          }
          if (!is_dir($dir)) {
            create_path($order_id, DIR_FS_ORDER_FILES);
          }

          $file_name = $file_id . '.' . $extension;

          if (!move_uploaded_file($file['tmp_name'], $dir . $file_name)) {
            Order::deleteAttachedFile($file_id);
            $result['msg'] = "Ошибка при сохранении файла";
            return $result;
          }
        }
      }
    }

    return self::generate_response(true, "OK", array(
      'id' => $order_id,
      'date' => $date,
    ));
  }

  public static function get_worktypes($params = array()) {
    global $db;

    $where = self::generate_where_clause($params);
    $db->Select('data_worktypes', '*', "WHERE " . $where);

    if (!$db->Error) {
      if ($db->ResultCount) {
        return self::generate_response(true, "OK", $db->Result);
      } else {
        return self::generate_response(false, "Вида работ подходящего под параметры " . $where . " не существует");
      }
    } else {
      return self::generate_response(false, $db->Error);
    }
  }

  public static function get_payment_methods($params = array()) {
    global $db;

    $where = self::generate_where_clause($params);
    $db->Select('data_payments', '*', "WHERE " . self::generate_where_clause($params));

    if (!$db->Error) {
      if ($db->ResultCount) {
        return self::generate_response(true, "OK", $db->Result);
      } else {
        return self::generate_response(false, "Способа оплаты подходящего под параметры " . $where . " не существует");
      }
    } else {
      return self::generate_response(false, $db->Error);
    }
  }

  public static function get_napravlen($params = array()) {
    global $db;

    $where = self::generate_where_clause($params);
    $db->Select('data_napravl', '*', "WHERE " . $where);

    if (!$db->Error) {
      if ($db->ResultCount) {
        return self::generate_response(true, "OK", $db->Result);
      } else {
        return self::generate_response(false, "Направления подходящего под параметры " . $where . " не существует");
      }
    } else {
      return self::generate_response(false, $db->Error);
    }
  }

  function edit_order() {

  }

  function delete_order() {

  }

  public static function get_order($params) {
    $params['limit'] = 1;
    $result = self::get_orders($params);

    if ($result['status'] === false) {
      return $result;
    }

    $order = $result['params'][0];
    $result['params'] = $order;

    return $result;
  }

  public static function get_orders($params) {
    global $db;

    $order_by = '';
    if (array_key_exists("order_by", $params)) {
      $order_by = ' ORDER BY ' . $params['order_by'];
      unset($params['order_by']);
    }

    $limit = '';
    if (array_key_exists("limit", $params)) {
      $limit = ' LIMIT ' . $params['limit'];
      unset($params['limit']);
    }

    $fields = '*';
    if (array_key_exists('fields', $params)) {
      $fields = join(', ', $params['fields']);
      unset($params['fields']);
    }
    $where = self::generate_where_clause($params);

    $db->Select('orders', $fields, "WHERE " . $where . $order_by . $limit);

    if (!$db->Error) {
      if ($db->ResultCount) {
        return self::generate_response(true, "OK", $db->Result);
      } else {
        return self::generate_response(false, "Нет ни одного заказа с параметрами " . $where);
      }
    } else {
      return self::generate_response(false, $db->Error);
    }
  }

  public static function get_orders_statuses() {
    global $db;

    $db->Select('orders_status', '*');

    if (!$db->Error) {
      if ($db->ResultCount) {
        return self::generate_response(true, "OK", $db->Result);
      } else {
        return self::generate_response(false, "Статусов заказов нету");
      }
    } else {
      return self::generate_response(false, $db->Error);
    }
  }

  public static function add_client($client_params) {
    $result = array(
      'status' => false,
      'msg' => '',
    );

    if (Client::exist($client_params['email'])) {
      $result['msg'] = "Клиент с email - " . $client_params['email'] . " уже существует";
      return $result;
    }

    $date = mktime();

    if (!empty($client_params['filial_id'])) {
      $filial_id = Filial::check($client_params['filial_id']);
    } else {
      $filial_id = Filial::search($client_params['filial']);
    }

    $client_id = Client::create(array(
      'filial_id' => $filial_id,
      'fio' => $client_params['fio'],
      'email' => $client_params['email'],
      'telnum' => $client_params['telnum'],
      'city' => $client_params['city'],
      'liketel' => $client_params['liketel'],
      'teltime' => $client_params['teltime'],
      'icq' => $client_params['icq'],
      'skype' => $client_params['skype'],
      'contacts' => $client_params['contacts'],
      'blocked' => $client_params['blocked'],
      'about' => $client_params['about'],
      'ocenka' => $client_params['ocenka'],
      'ref_id' =>  $client_params['ref_id'],
      'from_id' =>  $client_params['from_id'],
      'added_by' => $client_params['added_by'],
      'orderform' => $client_params['orderform'],
      'password' => $client_params['password'],
    ));

    return self::generate_response(true, "OK", array(
      'id' => $client_id,
      'date' => $date,
    ));
  }

  public static function edit_client($params) {
    global $db;

    $db->Update("clients", array_keys($params['fields_values']), array_values($params['fields_values']), $params['params']);

    if (!$db->Error) {
      return self::generate_response(true, "OK");
    } else {
      return self::generate_response(false, $db->Error);
    }
  }

  function delete_client() {

  }

  public static function get_client($params) {
    global $db;

    $fields = '*';

    if (array_key_exists('fields', $params)) {
      $fields = join(', ', $params['fields']);
      unset($params['fields']);
    }

    $where = self::generate_where_clause($params);

    $db->Select('clients', $fields, "WHERE " . self::generate_where_clause($params));

    if (!$db->Error) {
      if ($db->ResultCount) {
        return self::generate_response(true, "OK", $db->Result[0]);
      } else {
        return self::generate_response(false, "Клиента с параметрами: " . $where . " не существует");
      }
    } else {
      return self::generate_response(false, $db->Error);
    }
  }

  private static function generate_where_clause($params = array()) {
    if (!count($params)) {
      return ' 1';
    }
    $result = '';
    foreach ($params as $key => $value) {
      if (empty($value)) {
        continue;
      }

      if (empty($result)) {
        $result .= $key . " = '" . db_input($value) . "'";
      } else {
        $result .= " AND " . $key . " = '" . db_input($value) . "'";
      }
    }

    return $result;
  }

  private static function generate_response($status, $msg = '', $params = array()) {
    return array(
      'status' => $status,
      'msg' => $msg,
      'params' => $params,
    );
  }

}

?>