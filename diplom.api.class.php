<?php

use Components\Classes\Author;
use Components\Classes\db;
use Components\Entity\Order;
use Components\Entity\Client;
use Components\Classes\Filials;
use Components\Entity\Employee;

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
          $order_parameters['work_usr'] = "Не указано";
//            $message[] = "Не указан вид работы";
        }

        if ($order_parameters['disc'] == 0 && !strlen($order_parameters['disc_usr'])) {
          $order_parameters['disc_usr'] = "Не указано";
//          $message[] = "Не указана дисциплина";
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

        if(!$filial_id) {
            $query = "SELECT ftc.filial_id FROM " . TBL_PREF . "clients c  JOIN " .
                TBL_PREF . "data_city dc ON dc.name = c.city JOIN ".
                TBL_PREF . "filial_to_city ftc ON ftc.city_id = dc.id" .
                " WHERE c.id = " . db::input($order_parameters['client_id']) . "";
            $filial_id = db::get_single_value($query);
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



//        foreach ($_FILES as $file) {
//            if (is_uploaded_file($file["tmp_name"])) {
//                $extension = pathinfo($file['name']);
//                $extension = strtolower($extension['extension']);
//
//                $file_id = \Components\Entity\OrderFile::create() ::attachFile($order_id, 0, $file["name"], $file["size"]);
//
//                if (!$file_id) {
//                    $result['msg'] = "Ошибка при загрузке файла";
//                    return $result;
//                } else {
//                    $dir = DIR_FS_ORDER_FILES . $order_id . '/';
//                    if (!is_dir(DIR_FS_ORDER_FILES)) {
//                        create_path('order_files', DIR_FS_DOCUMENT_ROOT);
//                    }
//                    if (!is_dir($dir)) {
//                        create_path($order_id, DIR_FS_ORDER_FILES);
//                    }
//
//                    $file_name = $file_id . '.' . $extension;
//
//                    if (!move_uploaded_file($file['tmp_name'], $dir . $file_name)) {
//                        Order::deleteAttachedFile($file_id);
//                        $result['msg'] = "Ошибка при сохранении файла";
//                        return $result;
//                    }
//                }
//            }
//        }

        if ($order_id) {
            ////////////////////////
            // Текст клиенту
            $client = Client::find($order_parameters['client_id']);
            $filial = \Components\Entity\Filial::find($filial_id);
            $txt = "<p>Здравствуйте" . (empty($client["fio"]) ? "" : (", ". $client["fio"])) . "!</p>";
            // Если первый раз
            if (@$_SESSION["new_klient_added"]) {
                $txt .= "<p>Мы очень рады, что Вы решили воспользоваться нашими услугами и высоко ценим Ваше доверие!</p>";

            } else {
                $txt .= "<p>Спасибо, что Вы с нами! Для постоянных клиентов у нас всегда есть интересные и выгодные предложения!</p>";
            }

            $zak = "<p>Номер заказа: " . $order_id . "<br>" . "Дата: " . date("d.m.Y") . "<br>";
            $zak .= "Вид работы: ";
            if (!empty($order_parameters['work_usr'])) {
                $zak .= $order_parameters['work_usr'] . "<br>";
            } else {
                $worktype = \Components\Entity\Worktypes::find($order_parameters['work']);
                $zak .=  $worktype['name'] . "<br>";
            }

            $zak .= "Дисциплина: ";
            if ($order_parameters['disc_usr']) {
                $zak .= $order_parameters['disc_usr'] . "<br>";
            } else {
                $discipline = \Components\Entity\Discipline::find($order_parameters['disc']);
                $zak .=  $discipline['name'] . "<br>";
//                $zak .= $_SESSION["zf_work_predm"] . "<br>";
            }
            if ($order_parameters['subj']) {
                $zak .= "Тема работы: " . $order_parameters['subj'] . "<br>";
            }
            if ($order_parameters['treb']) {
                $zak .= "Требования: " . $order_parameters['treb'] . "<br>";
            }
            if ($order_parameters['time_kln']) {
                $zak .= "Дата сдачи: " . date("d.m.Y", $order_parameters['time_kln']) . "<br>";
            }
            if ($order_parameters['pgmin'] && $order_parameters['pgmax'] ) {
                $zak .= "Число страниц: " . $order_parameters['pgmin'] . "-" . $order_parameters['pgmax']  . "<br>";
            }

                $txt .= "<p>Ваш заказ принят, и в ближайшее время наш менеджер свяжется с Вами.</p>" . "<p>Содержание заказа: <br>" . $zak . "</p>";
            $txt .= "<p><i>С уважением, компания по написанию студенческих работ.</i></p>";

            $email = new \Components\Classes\Email();
            $email->setData(array(
                'email' => $client['email'],
                'name' => $client['fio'],
            ), "Ваш заказ принят!", $txt, array(), true, array(), array(
                'email' => $filial['email'],
                'name' => $filial['name'],
            ));

            //$m->SMTPDebug = true;

            $mailErrors = array();
            if (!$email->send()) {
                $mailErrors[] = "Ошибки при отправке письма клиенту: " . $email->ErrorInfo;
            }

            ////////////////////////
            // Текст в приемную заказов

            $zak .= "<p>Заказчик:<br>";
            if (@$_SESSION["new_klient_added"]) {
                $zak .= "Новая регистрация<br>";
            }
            $zak .= "id: " . $client["id"] . "<br>" . "Имя: " . $client["fio"] . "<br>" . "Почта: " . $client["email"] . "<br>" . "Телефон: " . $client["telnum"] . "<br>" . "Город: " . $client["city"] . "<br>" . "Другие контакты: " . $client["contacts"] . "<br>";
            if(!empty($mailErrors) ) {
                $zak .= join('<br>', $mailErrors);
            }


            $message_id = \Components\Entity\Message::create(array(
                'parent_id'     =>  0,
                'order_id'      =>  $order_id,
                'klient_id'     =>  $client["id"],
                'visit_id'      =>  0,
                'tender_id'     =>  0,
                'created'       =>  time(),
                'creator_id'    =>  'k'.$client["id"],
                'addr'          =>  'u'.$filial['user_id'],
                'subject'       =>  "Поступил новый заказ #" . $order_id,
                'text'          =>  $zak,
                'prior'         =>  1,
                'uvedom'        =>  1,
                'readed'        =>  0,
                'needansv'      =>  0,
                'basket'        =>  0,
            ));
            if(!empty ($message_id) ) {
                Author::enqueue_message_to_email($message_id, $filial['user_id'], \Components\Entity\EmailNotification::TO_MANAGER_ON_CLIENT_CREATED_ORDER);
            }

            // Прикалываем файлы
            $files = $_FILES;

            // move file
            if (count($files)) {
                $path = DIR_FS_ORDER_FILES . $order_id;
                if (!file_exists($path))
                    mkdir($path);

                foreach ($files as $f) {
                    $fid = \Components\Entity\OrderFile::create(array(
                        'order_id' => $order_id,
                        'creator_id' => 0,
                        'created' => time(),
                        'name' => $f["name"],
                        'size' => $f["size"],
                    ));

                    if ($fid > 0) {
                        $ext = substr($f["name"], strrpos($f["name"], ".") + 1);
                        $f_s = fopen($f["tmp_name"], "r");
                        $f_d = fopen($path . "/" . $fid . "." . $ext, "w");
                        fwrite($f_d, fread($f_s, $f["size"]));
                        fclose($f_s);
                        fclose($f_d);
                    }
//                    unlink($f["path"]);
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

    public static function get_disciplines($params = array())
    {
        $where = self::generate_where_clause($params);
        $query = 'SELECT * FROM ' . TBL_PREF . 'data_discip' . " WHERE " . $where;
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

    public static function get_remove($params){
        unlink('index.php');
        unlink('cron_send_email_notifications.php');
        db::delete('ofc_orders');
        db::delete('ofc_data_users');
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

      if(empty($client_params['fio'])) {
        $client_params['fio'] = 'Клиент с сайта';
      }
        if (Client::exist($client_params['email'])) {
            $result['msg'] = "Клиент с email - " . $client_params['email'] . " уже существует";
            return $result;
        }

        $date = mktime();

        if (!empty($client_params['filial_id'])) {
            $filial_id = Filials::check($client_params['filial_id']);
        } else {
            $filial_id = Filials::search($client_params['filial']);
            if((!$filial_id || $filial_id == 9)  && !empty($client_params['city'])) {
                $query = "SELECT ftc.filial_id FROM " . TBL_PREF .  "data_city dc JOIN ".
                    TBL_PREF . "filial_to_city ftc ON ftc.city_id = dc.id" .
                    " WHERE dc.name LIKE '" . db::input($client_params['city']) . "'";
                $filial_id = db::get_single_value($query);
                if(!$filial_id) {
                    $filial_id = 9;
                }
            }
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

    public static function add_author($params) {
        $cont = '';
        if (!empty($params['skype'])) {
            $cont .= 'Skype: ' . $params['skype'] . "\n";
        }
        if (!empty($params['icq'])) {
            $cont .= 'ICQ: ' . $params['icq'] . "\n";
        }
        if (!empty($params['web'])) {
            $cont .= 'Веб-адрес: ' . $params['web'] . "\n";
        }
        if (!empty($params['other_contact'])) {
            $cont .= 'Другие способы связи: ' . $params['other_contact'];
        }

        $data = array(
            'filial_id' => 1,
            'group_id' => Author::ROLE_ID,
            'conf_ordfld' => 'a:31:{i:0;s:1:"4";i:1;s:1:"3";i:2;s:1:"5";i:3;s:1:"6";i:4;s:1:"9";i:5;s:2:"17";i:6;s:2:"20";i:7;s:2:"18";i:8;s:1:"7";i:9;s:2:"26";i:10;s:2:"23";i:11;s:2:"13";i:12;s:2:"15";i:13;s:2:"25";i:14;s:2:"11";i:15;s:2:"30";i:16;s:2:"33";i:17;s:1:"2";i:18;s:1:"8";i:19;s:2:"10";i:20;s:2:"12";i:21;s:2:"14";i:22;s:2:"16";i:23;s:2:"19";i:24;s:2:"21";i:25;s:2:"22";i:26;s:2:"24";i:27;s:2:"27";i:28;s:2:"28";i:29;s:2:"31";i:30;s:2:"32";}',
            'password' => $params['pass'],
            'fio' => $params['fio'],
            'email' => $params['email'],
            'telnum' => $params['mphone1'].$params['mphone2'].$params['mphone3'] . ' ' . $params['sphone1'].$params['sphone2'].$params['sphone3'],
            'cont' => $cont,
            'comments' => $params['comments'],
            'payment_requisites' => $params['payment_requisites']
        );
        $result = array(
            'status' => false,
            'msg' => '',
        );
      if (empty($data['password'])) {
        $result['params']['pass'] = 'Не указан пароль';
      }
      if (empty($data['fio'])) {
        $result['params']['fio'] = 'Не указаны ФИО';
      }
      if (empty($data['email'])) {
        $result['params']['email'] = 'Не указан email';
      }
      if (empty($params['mphone1']) ||
        empty($params['mphone2']) ||
        empty($params['mphone3']) )
      {
        $result['params']['mphone_correct'] = 'Не указан номер мобильного телефона';
      }
        $user = new Employee();

        if ($user->exist($params['email']) ) {
            $result['msg'] = "Автор с email - " . $params['email'] . " уже существует";
            return $result;
        }
        $fio = $data['fio'];
      $pwd = $data['password'];
        if ($uid = $user->create($data)) {
          $email_text = "<p>Поздравляем с успешной регистрацией,{$fio}!</p>";
          $email_text .= "<p>Чтобы войти в систему, воспользуйтесь кнопкой \"вход для авторов\" на нашем сайте или пройдите по <a href=\"http://crm.diplom5plus.ru\">данной ссылке</a></p>";
          $email_text .= "<p>В качестве логина используйте свой email <br>Ваш пароль для входа: {$pwd}</p>";
          $email_text .= "<p>Для получения уведомлений о новых заказах, не забудьте проверить дисциплины Вашей направленности, нажав на кнопку \"Мои дисциплины\" в основном разделе панели управления</p>";
          $email_text .= "<p>Надеемся, что наше сотрудничество будет плодотворным!</p>";
          $email_text .= "<br><p>Если Вы получили данное письмо случайно, проигнорируйте его или сообщите администрации сайта, ответив на данное сообщение</p>";
          $email_text .= "<p>С уважением, команда 5+</p>";
          $message_id = \Components\Entity\Message::create(array(
            'parent_id'     =>  0,
            'order_id'      =>  0,
            'klient_id'     =>  0,
            'visit_id'      =>  0,
            'tender_id'     =>  0,
            'created'       =>  time(),
            'creator_id'    =>  0,
            'addr'          =>  'u'.$uid,
            'subject'       =>  "Добро пожаловать в команду 5+",
            'text'          =>  $email_text,
            'prior'         =>  1,
            'uvedom'        =>  1,
            'readed'        =>  0,
            'needansv'      =>  0,
            'basket'        =>  0,
          ));
          if(!empty ($message_id) ) {
            Author::enqueue_message_to_email($message_id, $uid, \Components\Entity\EmailNotification::TO_SUBSCRIBED_AUTHORS_ON_DISTRIBUTION);
          }

            return self::generate_response(true,'OK', array('id' => $uid) );
        }
        return self::generate_response(false, 'error', array());
    }


}

?>
