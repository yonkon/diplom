<?php

use Components\Classes\db;
use Components\Entity\Order;
use Components\Entity\Client;
use Components\Classes\Filials;

class diplom
{
    public static function create_order($order_parameters)
    {
        $result = array(
            'status' => false,
            'msg' => '',
        );

        $message = array();

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

        $filial_id = db::get_single_value("SELECT filial_id FROM " . TBL_PREF . "clients WHERE id = " . db::input($order_parameters['client_id']) . "");

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
            "time_kln" => $order_parameters['time_kln'], // Дата сдачи
            "payment_id" => $order_parameters['opl'],
            "subject" => $order_parameters['subj'],
            "about_kln" => $order_parameters['treb'], //treb
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

    public static function get_worktypes($params = array())
    {
        $where = self::generate_where_clause($params);
        $query = 'SELECT * FROM ' . TBL_PREF . 'data_worktypes' . " WHERE " . $where;
        $db_result = db::get_arrays($query);

        if (0 == $errno = mysql_errno()) {
            if (count($db_result)) {
                return self::generate_response(true, "OK", $db_result);
            } else {
                return self::generate_response(false, "Вида работ подходящего под параметры " . $where . " не существует");
            }
        } else {
            return self::generate_response(false, db::error($query, mysql_errno(), mysql_error()));
        }
    }

    public static function get_payment_methods($params = array())
    {
        $where = self::generate_where_clause($params);
        $query = 'SELECT * FROM ' . TBL_PREF . 'data_payments' . " WHERE " . self::generate_where_clause($params);
        $db_result = db::get_arrays($query);

        if (0 == $errno = mysql_errno()) {
            if (count($db_result)) {
                return self::generate_response(true, "OK", $db_result);
            } else {
                return self::generate_response(false, "Способа оплаты подходящего под параметры " . $where . " не существует");
            }
        } else {
            return self::generate_response(false, db::error($query, $errno, mysql_error()));
        }
    }

    public static function get_napravlen($params = array())
    {
        $where = self::generate_where_clause($params);
        $query = 'SELECT * FROM ' . TBL_PREF . 'data_napravl' . " WHERE " . $where;
        $db_result = db::get_arrays($query);

        if (0 == $errno = mysql_errno()) {
            if (count($db_result)) {
                return self::generate_response(true, "OK", $db_result);
            } else {
                return self::generate_response(false, "Направления подходящего под параметры " . $where . " не существует");
            }
        } else {
            return self::generate_response(false, db::error($query, $errno, mysql_error()));
        }
    }

    function edit_order()
    {

    }

    function delete_order()
    {

    }

    public static function get_order($params)
    {
        $params['limit'] = 1;
        $result = self::get_orders($params);

        if ($result['status'] === false) {
            return $result;
        }

        $order = $result['params'][0];
        $result['params'] = $order;

        return $result;
    }

    public static function get_orders($params)
    {
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
        $interface_map = array(
            'klient_id' => 'client_id',
            "vuz_id" => 'vuz',
            "vuz_user" => 'vuz_usr',
            "type_id" => 'work',
            "type_user" => 'work_usr',
            "napr_id" => 'napr',
            "disc_id" => 'disc',
            "disc_user" => 'disc_usr',
            "time_kln" => 'time_kln',
            "payment_id" => 'opl',
            "subject" => 'subj',
            "about_kln" => 'treb',
            "kurs" => 'kurs',
            "prakt_pc" => 'prakt',
            "pages_min" => 'pgmin',
            "pages_max" => 'pgmax',
            "src_min" => 'srcmin',
            "src_max" => 'srcmax',
        );
        foreach ($interface_map as $entity_field => $api_field) {
            if(isset($params[$api_field]) ) {
                $params[$entity_field] = $params[$api_field];
                unset($params[$api_field]);
            }
        }

        $where = self::generate_where_clause($params);

        $query = 'SELECT ' . $fields . ' FROM ' . TBL_PREF . 'orders' . " WHERE " . $where . $order_by . $limit;
        $db_result = db::get_arrays($query);

        if (0 == $errno = mysql_errno()) {
            if (count($db_result)) {
                return self::generate_response(true, "OK", $db_result);
            } else {
                return self::generate_response(false, "Нет ни одного заказа с параметрами " . $where);
            }
        } else {
            return self::generate_response(false, db::error($query, $errno, mysql_error()));
        }
    }

    public static function get_orders_statuses()
    {
        $query = 'SELECT * FROM ' . TBL_PREF . 'orders_status';
        $db_result = db::get_arrays($query);

        if (0 == $errno = mysql_errno()) {
            if (count($db_result)) {
                return self::generate_response(true, "OK", $db_result);
            } else {
                return self::generate_response(false, "Статусов заказов нету");
            }
        } else {
            return self::generate_response(false, db::error($query, $errno, mysql_error()));
        }
    }

    public static function add_client($client_params)
    {
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
            $filial_id = Filials::check($client_params['filial_id']);
        } else {
            $filial_id = Filials::search($client_params['filial']);
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
            'ref_id' => $client_params['ref_id'],
            'from_id' => $client_params['from_id'],
            'added_by' => $client_params['added_by'],
            'orderform' => $client_params['orderform'],
            'password' => $client_params['password'],
        ));

        return self::generate_response(true, "OK", array(
            'id' => $client_id,
            'date' => $date,
        ));
    }

    public static function edit_client($params)
    {
        $cid = $params['client_id'];
        unset($params['client_id']);
        $res = Client::update($cid, $params['fields_values']);
//        db::Update("clients", array_keys($params['fields_values']), array_values($params['fields_values']), $params['params']);

        if (0 == $errno = mysql_errno() && $res) {
            return self::generate_response(true, "OK");
        } else {
            return self::generate_response(false, db::error('client updating SQL', $errno, mysql_error()));
        }
    }

    function delete_client()
    {

    }

    public static function get_client($params)
    {


        $fields = '*';

        if (array_key_exists('fields', $params)) {
            $fields = join(', ', $params['fields']);
            unset($params['fields']);
        }

        $where = self::generate_where_clause($params);

        $query = "SELECT $fields FROM " . TBL_PREF . "clients WHERE " . self::generate_where_clause($params);
        $db_result = db::get_arrays($query);

        if (0 == $errno = mysql_errno()) {
            if (count($db_result)) {
                return self::generate_response(true, "OK", $db_result[0]);
            } else {
                return self::generate_response(false, "Клиента с параметрами: " . $where . " не существует");
            }
        } else {
            return self::generate_response(false, db::error($query, $errno, mysql_error()));
        }
    }

    private static function generate_where_clause($params = array())
    {
        if (!count($params)) {
            return ' 1';
        }
        $result = '';
        foreach ($params as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if (empty($result)) {
                $result .= $key . " = '" . db::input($value) . "'";
            } else {
                $result .= " AND " . $key . " = '" . db::input($value) . "'";
            }
        }

        return empty($result)? '1' : $result;
    }

    private static function generate_response($status, $msg = '', $params = array())
    {
        return array(
            'status' => $status,
            'msg' => $msg,
            'params' => $params,
        );
    }

}

?>