<?

use Components\Classes\db;
use Components\Classes\ErrorLogger;
use Components\Entity\Discipline;
use Components\Entity\Worktypes;
use Components\Exceptions\Exception;
use Components\Entity\Filial;
use Components\Classes\Filials;
use Components\Entity\Order;
use Components\Entity\Client;
use Components\Classes\Email;
use Components\Entity\OrderFile;
use \Components\Entity\EmailNotification;

function save_order_form() {
  if (!is_client_logged()) {
//user
    $_SESSION["zf_user_login"] = clearText($_REQUEST["zf_user_login"]);
    $_SESSION["zf_user_name"] = clearText($_REQUEST["zf_user_name"]);
    $_SESSION["zf_user_tel"] = clearText($_REQUEST["zf_user_tel"]);
    $_SESSION["zf_user_city"] = clearText($_REQUEST["zf_user_city"]);
  }

  //work
  $_SESSION["zf_work_type"] = intval(clearText($_REQUEST["zf_work_type"]));
  $_SESSION["zf_work_predm"] = clearText($_REQUEST["zf_work_predm"]);
  $_SESSION["zf_work_tema"] = clearText($_REQUEST["zf_work_tema"]);
  $_SESSION["zf_work_date"] = clearText($_REQUEST["zf_work_date"]);
  $_SESSION["zf_work_pages"] = clearText($_REQUEST["zf_work_pages"]);
  $_SESSION["zf_work_dopinfo"] = clearText($_REQUEST["zf_work_dopinfo"]);

  $_SESSION["zf_filial_domain"] = clearText($_REQUEST["zf_filial_domain"]);
  $_SESSION["zf_referrer_code"] = clearText($_REQUEST["zf_referrer_code"]);
}

function clear_saved_form() {
  //user
  $_SESSION["zf_user_login"] = "";
  $_SESSION["zf_user_name"] = "";
  $_SESSION["zf_user_tel"] = "";
  $_SESSION["zf_user_city"] = "";

  //work
  $_SESSION["zf_work_type"] = "";
  $_SESSION["zf_work_type_user"] = "";
  $_SESSION["zf_work_predm"] = "";
  $_SESSION["zf_work_tema"] = "";
  $_SESSION["zf_work_date"] = "";
  $_SESSION["zf_work_pages"] = "";
  $_SESSION["zf_work_dopinfo"] = "";

  $_SESSION["zf_filial_domain"] = "";
  $_SESSION["zf_referrer_code"] = "";

  $files = check_user_files();
  foreach ($files as $f)
    if (file_exists($f["full"]))
      unlink($f["full"]);
  $path = TMPFILES_PATH . session_id();
  if (file_exists($path))
    rmdir($path);

}

?>


<? if (isset($_REQUEST["ok"])): ?>

  <? clear_saved_form(); ?>

  <!--<div class="caption_txt" id="page_caption">Заказ работы</div>-->
  <div class="normal_txt" id="mkorder_info"><? include "text_mkorder_ok.html"; ?></div>


<? else: ?>

<?

// logout
  if (is_client_logged() && isset($_REQUEST["logout"])) {
    unauth_user();
    header("Location: /frame/?type=form");
    die();
  }

  function _fmt_size($sz) {
    $s = array(
      "Б",
      "кБ",
      "Мб"
    );
    $n = 0;
    $d = 1;

    for (; ;) {
      $sz = $sz / $d;
      if (($sz < 1000) || ($n == 2))
        break;

      $d *= 1024;
      $n++;
    }

    return ceil($sz) . " " . $s[$n];
  }


  function add_order($client_id, &$err) {
    $err = "";
    try {
      $client = Client::find($client_id);
    } catch(Exception $e) {
      $err = "Ошибка - не указан клиент";
      return false;
    }

    try {
      $filial = Filial::find($client['filial_id']);
    } catch(Exception $e) {
      $filial = Filials::getDefault();
      Client::update($client['id'], array(
        'filial_id' => $filial['id']
      ));
      db::update(TABLE_ORDERS, array(
        'filial_id' => $filial['id']
      ), 'klient_id = ' . $client['id']);
    }

    $pgs = get_min_max_from_str($_SESSION["zf_work_pages"]);

    // disc id
    try {
      $discipline = Discipline::find($_SESSION["zf_work_predm"]);
      $disc_id = $discipline['id'];
    } catch(Exception $e) {
      $discipline = Discipline::findOneBy(array(
        'name' => $_SESSION["zf_work_predm"],
      ));

      if ($discipline) {
        $disc_id = $discipline['id'];
      } else {
        $disc_id = Discipline::create(array(
          'name' => $_SESSION["zf_work_predm"],
        ));
      }
    }

    $info = $_SESSION["zf_work_dopinfo"];

    $worktype_custom = '';
    $worktype = null;
    try {
      $worktype = Worktypes::find($_SESSION["zf_work_type"]);
    } catch(Exception $e) {
      $worktype = Worktypes::findOneBy(array(
        'name' => $_SESSION["zf_work_type"],
      ));

      if (!$worktype) {
        $worktype_custom = $_SESSION["zf_work_type"];
        $worktype['id'] = null;
      }
    }

    $id = Order::create(array(
      "filial_id" => $filial['id'],
      "klient_id" => $client['id'],
      "type_id" => $worktype['id'],
      "type_user" => $worktype_custom,
      "disc_id" => $disc_id,
      "time_kln" => strtotime($_SESSION["zf_work_date"]),
      // Дата сдачи
      "subject" => $_SESSION["zf_work_tema"],
      "about_kln" => $info,
      //treb
      "pages_min" => $pgs["min"],
      "pages_max" => $pgs["max"],
    ));

    if ($id > 0) {

      ////////////////////////
      // Текст клиенту

      $txt = "<p>Здравствуйте, " . $client["fio"] . "!</p>";
      // Если первый раз
      if (@$_SESSION["new_klient_added"]) {
        $txt .= "<p>Мы очень рады, что Вы решили воспользоваться нашими услугами и высоко ценим Ваше доверие!</p>" . "<p>Теперь Вы можете войти в личный кабинет:<br>" . "&nbsp;Логин: " . $client["email"] . "<br>" . "&nbsp;Пароль: " . $client["password"] . "<br></p>";

      } else {
        $txt .= "<p>Спасибо, что Вы с нами! Для постоянных клиентов у нас всегда есть интересные и выгодные предложения!</p>";
      }

      $zak = "<p>Номер заказа: " . $id . "<br>" . "Дата: " . date("d.m.Y") . "<br>";
      $zak .= "Вид работы: ";
      if (!empty($worktype_custom)) {
        $zak .= $worktype_custom . "<br>";
      } else {
        $zak .= $worktype["name"] . "<br>";
      }

      $zak .= "Дисциплина: ";
      if ($discipline) {
        $zak .= $discipline['name'] . "<br>";
      } else {
        $zak .= $_SESSION["zf_work_predm"] . "<br>";
      }

      $zak .= "Тема работы: " . $_SESSION["zf_work_tema"] . "<br>" . "Требования: " . $_SESSION["zf_work_dopinfo"] . "<br>" . "Дата сдачи: " . $_SESSION["zf_work_date"] . "<br>" . "Число страниц: " . $_SESSION["zf_work_pages"] . "<br>" .

      $txt .= "<p>Ваш заказ принят, и в ближайшее время наш менеджер свяжется с Вами.</p>" . "<p>Содержание заказа: <br>" . $zak . "</p>";
      $txt .= "<p><i>С уважением, компания по написанию студенческих работ.</i></p>";

      $email = new Email();
      $email->setData(array(
        'email' => $client['email'],
        'name' => $client['fio'],
      ), "Ваш заказ принят!", $txt, array(), true, array(), array(
        'email' => $filial['email'],
        'name' => $filial['name'],
      ));

      //$m->SMTPDebug = true;

      if ($email->send()) {
        $user_send_res = "Письмо клиенту отправлено";
      } else {
        $user_send_res = "Ошибки при отправке письма клиенту: " . $email->ErrorInfo;
      }

      ////////////////////////
      // Текст в приемную заказов

      $zak .= "<p>Заказчик:<br>";
      if (@$_SESSION["new_klient_added"]) {
        $zak .= "Новая регистрация<br>";
      }
      $zak .= "id: " . $client["id"] . "<br>" . "Имя: " . $client["fio"] . "<br>" . "Почта: " . $client["email"] . "<br>" . "Телефон: " . $client["telnum"] . "<br>" . "Город: " . $client["city"] . "<br>" . "Другие контакты: " . $client["contacts"] . "<br>";
      $zak .= $user_send_res;

      // Прикалываем файлы
      $files = check_user_files();

      $message_id = \Components\Entity\Message::create(array(
        'parent_id'     =>  0,
        'order_id'      =>  $id,
        'klient_id'     =>  $client["id"],
        'visit_id'      =>  0,
        'tender_id'     =>  0,
        'created'       =>  time(),
        'creator_id'    =>  'k'.$client["id"],
        'addr'          =>  'u'.$filial['id'],
        'subject'       =>  "Поступил новый заказ #" . $id,
        'text'          =>  $zak,
        'prior'         =>  1,
        'uvedom'        =>  1,
        'readed'        =>  0,
        'needansv'      =>  0,
        'basket'        =>  0,
      ));
      if(!empty ($message_id) ) {
        \Components\Classes\Author::enqueue_message_to_email($message_id, $filial['id'], EmailNotification::TO_MANAGER_ON_CLIENT_CREATED_ORDER);
      }

      /* ОТПРАВИТЬ В РЕАЛЬНОМ ВРЕМЕНИ
      $email = new Email();
      $email->setData(array(
        'email' => FIRM_ORD_MAIL,
        'name' => 'Приемная заказов',
      ), "Поступил новый заказ #" . $id, $zak, $files, true, array(), array(
        'email' => $filial['email'],
        'name' => $filial['name'],
      ));
      $email->send();
      ENDOF ОТПРАВИТЬ В РЕАЛЬНОМ ВРЕМЕНИ */

      // move file
      if (count($files)) {
        $path = DIR_FS_ORDER_FILES . $id;
        if (!file_exists($path))
          mkdir($path);

        foreach ($files as $f) {
          $fid = OrderFile::create(array(
            'order_id' => $id,
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

      $path = TMPFILES_PATH . session_id();
      if (file_exists($path))
        rmdir($path);

      return true;
    } else return false;
  }

  function add_client_if_need($orderform = 0) {
    if (is_client_logged()) {
      return $_SESSION["frame"]["client"]["id"];
    }

    $add_to_filial = Filials::search($_SESSION['zf_filial_domain'], $_SESSION["zf_user_city"]);

    $referrer_id = 0;
    if (!empty($_SESSION['zf_referrer_code'])) {
      $referrer = Client::findOneBy(array(
        'referrer_code' => $_SESSION['zf_referrer_code'],
      ));

      if (!empty($referrer)) {
        $referrer_id = $referrer['id'];
      }
    }

    $pwd = generate_pasw(5);
    $id = Client::create(array(
      'filial_id' => $add_to_filial,
      'fio' => $_SESSION["zf_user_name"],
      'email' => $_SESSION["zf_user_login"],
      'telnum' => $_SESSION["zf_user_tel"],
      'city' => $_SESSION["zf_user_city"],
      'password' => $pwd,
      'orderform' => $orderform,
      'ref_id' => $referrer_id,
    ));

    auth_client($_SESSION["zf_user_login"]);

    $_SESSION["new_klient_added"] = true;

    return $id;
  }

  function check_order_form(&$err) {
    save_order_form();

    $err = "";


    // Обработка файлов
    // Если передается файл, то запускаем прием файла
    if (isset($_FILES["zf_work_file"]) && $_FILES["zf_work_file"]["error"] && strlen($_FILES["zf_work_file"]["name"])) {
      $err = "Ошибка при загрузке файла";
      return false;
    }

    if (isset($_REQUEST["zf_file_to_del"]) && (intval($_REQUEST["zf_file_to_del"]) > -1)) {
      // Удалить файл
      $path = TMPFILES_PATH . session_id();
      $fls = check_user_files();
      $delfile = $fls[intval($_REQUEST["zf_file_to_del"])]["full"];
      if (is_file($delfile))
        unlink($delfile);

      $fls = check_user_files();
      if (!count($fls) && file_exists($path))
        rmdir($path);
      $err = "Файл удален";
      return false;
    }

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
      $err = "Файл сохранен";
      return false;
    }

    // check auth if need
    if (!is_client_logged()) {
      $login = clearText($_REQUEST["zf_user_login"]);

      // need login
      if (!validateEmail($login)) {
        if (isset($_REQUEST["ajax_mode"])) {
          ob_clean();
          die("-1");
        }
        $err = "Необходимо указать адрес электронной почты";
        return false;
      }


      if ($client = Client::findOneBy(array(
        'email' => $login
      ))) {
        $_SESSION["frame"]["client"] = $client;
      } else {
        // create new user
      }
    }

    // check form
    $_SESSION["zf_work_tema"] = substr($_SESSION["zf_work_tema"], 0, 200);
    $_SESSION["zf_work_pages"] = substr($_SESSION["zf_work_pages"], 0, 50);
    $_SESSION["zf_work_dopinfo"] = substr($_SESSION["zf_work_dopinfo"], 0, 1000);

    return true;
  }

  $error = "";

  if (isset($_REQUEST["forma_zakaz"])) {

    if (check_order_form($error)) {
      $client_id = add_client_if_need(1);

      if (!$client_id) {
        ErrorLogger::add('iframe_order', 'Fail to add client', $error);
      }
      if (add_order($client_id, $error)) {
        ob_clean();
        header("location: /frame/?type=form&ok");
        die();
      } else {
        ErrorLogger::add('iframe_order', 'Fail to add order', $error);
      }
    } else {
      ErrorLogger::add('iframe_order', 'Fail to check order form', $error);
    }
  } else {
    clear_saved_form();
  }



  ?>

  <!-- <div class="caption_txt" id="page_caption">Заказ работы</div> -->
  <!--<div class="normal_txt" id="mkorder_info">--><?// include "text_mkorder.html"; ?><!--</div>-->

  <? if (strlen($error)): ?>
    <div id="mkorder_error_text" class="error_txt"><?= $error ?></div>
  <? endif ?>

  <form method="post" action="" id="zakaz_form" enctype="multipart/form-data">
    <input type=hidden name="forma_zakaz">
    <input type="hidden" name="zf_filial_domain" value="<?= @$_SESSION["zf_filial_domain"] ?>" id="filial_domain">
    <input type="hidden" name="zf_referrer_code" value="<?= @$_SESSION["zf_referrer_code"] ?>" id="zf_referrer_code">

    <script>
      (function($) {
        $(function() {
          function getURLParameter(name, url) {
            return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(url)||[,""])[1].replace(/\+/g, '%20'))||null;
          }

          $("#zakaz_form").valform();

          var referrer = document.referrer;
          if (typeof referrer != 'undefined' && referrer != '') {
            var domain = referrer.match(/:\/\/(.[^/]+)/)[1];
            var referrer_code = getURLParameter('ref', referrer);

            $('#zf_referrer_code').val(referrer_code);
            $('#filial_domain').val(domain);
          }

          $.datepicker.setDefaults($.datepicker.regional['ru']);
          $('#zf_work_date').datepicker();

          function format(item) {
            if (typeof item.own_choise != 'undefined') {
              return item.name + item.own_choise;
            } else {
              return item.name;
            }
          }
          function own_choise(term) {
            return {
              id: term,
              name: term,
              own_choise: '(свой вариант)'
            };
          }
          function sort_by(field, reverse, primer) {
            var key = primer ?
              function(x) {return primer(x[field])} :
              function(x) {return x[field]};

            reverse = [-1, 1][+!!reverse];

            return function(a, b) {
              return a = key(a), b = key(b), reverse * ((a > b) - (b > a));
            }
          }

          var disciplines = [], worktypes = [];
          disciplines = <?= json_encode(Discipline::findAll()); ?>;
          $('.select2[name=zf_work_predm]').select2({
            data: {
              results: disciplines,
              text: 'name'
            },
            containerCssClass: 'select2_container',
            formatSelection: format,
            formatResult: format,
            createSearchChoice: own_choise,
            sortResults: function(results, container, query) {
              if (query.term) {
                return results.sort(sort_by('name', true, function(a){return a.length}));
              }
              return results;
            }
          });

          worktypes = <?= json_encode(Worktypes::findAll()); ?>;
          $('.select2[name=zf_work_type]').select2({
            data: {
              results: worktypes,
              text: 'name'
            },
            containerCssClass: 'select2_container',
            formatSelection: format,
            formatResult: format,
            createSearchChoice: own_choise
          });
        });
      })(jQuery);
    </script>

    <!-- order part -->
    <div id="mkorder_order_box" class="mkorder_box">
      <div id="mkorder_order_workvid" class="row">
        <label for="zf_work_type">Вид работы</label>
        <input type="hidden" id="zf_work_type" name="zf_work_type" value="<?= @$_SESSION["zf_work_type"] ?>" class="select2">
        <div class="clear"></div>
      </div>

      <div id="mkorder_order_disc" class="row">
        <label for="zf_work_predm">Дисциплина/специализация</label>
        <input type="hidden" id="zf_work_predm" name="zf_work_predm" value="<?= @$_SESSION["zf_work_predm"] ?>" class="select2">
        <div class="clear"></div>
      </div>

      <div id="mkorder_order_tema" class="row">
        <label for="zf_work_tema">Тема работы</label>
        <input type=text id="zf_work_tema" name="zf_work_tema" value="<? print @$_SESSION["zf_work_tema"]; ?>" maxlength="200">

        <div class="clear"></div>
      </div>

      <div id="mkorder_order_srok" class="row">
        <label for="zf_work_date">Срок выполнения</label>
        <input id="zf_work_date" type="text" name="zf_work_date" value="<?= @$_SESSION["zf_work_date"] ?>">

        <div class="clear"></div>
      </div>

      <div id="mkorder_order_lists" class="row">
        <label for="zf_work_pages">Кол-во страниц</label>
        <input type="text" id="zf_work_pages" name="zf_work_pages" value="<?= @$_SESSION["zf_work_pages"] ?>" maxlength="50">

        <div class="clear"></div>
      </div>

      <div class="clear"></div>

      <div id="mkorder_order_terb" class="row">
        <label for="zf_work_dopinfo">Примечание</label>
        <textarea name="zf_work_dopinfo" id="zf_work_dopinfo" placeholder="В этом поле Вы можете написать свои пожелания к работе" rows="5"><?= @$_SESSION["zf_work_dopinfo"] ?></textarea>

        <div class="clear"></div>
      </div>

      <div id="mkorder_order_files" class="row">

        <?
        $fls = check_user_files();
        $allsz = 0;
        foreach ($fls as $v)
          $allsz += $v["size"];
        if ($allsz < FILES_SZ_LIMIT)
          print '
		<div style="height:50px; margin-bottom: 20px">
		  <div id="add_file_field" style="margin-top: 5px">
           		<input type=file name="zf_work_file" value="Выбрать" onchange="mkorder_add_file_change(this)">
			</div>
			<div>Если имеются методические указания и материалы прикрепите файл (макс. размер 1го файла = ' . ini_get("upload_max_filesize") . ')</div>

		</div>'; else print "<div style='height:10px'></div>";
        ?>
        <div class="clear"></div>
        <input type="hidden" id="zf_file_to_del" name="zf_file_to_del" value="-1">

        <?
        if (count($fls)) {
          print "<div style='margin-bottom: 20px'>" . "<div>Файлы (" . _fmt_size($allsz) . " из " . (FILES_SZ_LIMIT / (1024 * 1024)) . " MB):</div>" . "<ul style='line-height:150%; margin-top:5px; font-size:8pt'>";
          foreach ($fls as $k => $v) {
            print "<li>" . $v["name"] . " <font color=gray>" . _fmt_size($v["size"]) . "</font> <a class='zf_del_file_a' href='#' onclick='mkorder_delete_upl_file(" . $k . ")'>удалить</a></li>";
          }
          print "</ul></div>";
        }
        ?>


      </div>
    </div>

    <!-- user part -->

    <? if (is_client_logged()): ?>

      <div class="mkorder_box">Делайте Ваш заказ, <?= $_SESSION["frame"]["client"]["fio"] ?>(<a href="?type=form&logout">сменить
          пользователя</a>)
      </div>

    <? else: ?>
      <div id="mkorder_user_box" class="mkorder_box">
        <div id="mkorder_user_loginbox">
          <div id="mkorder_user_loginbox_login" class="row">
            <label for="user_email">Электронная почта<span class="zvezda">*</span></label>
            <input name="zf_user_login" class="required email" value="<?= @$_SESSION["zf_user_login"] ?>" id="user_email" type="text" maxlength="50" onkeyup="checkuser_change_zak(this)" onblur="checkuser_change_zak(this)" onchange="checkuser_change_zak(this)"/>

            <div class="clear"></div>
            <!--        <div id="checkuser_info_text" style="font-size:7pt; color:gray; height: 10px"></div>-->
            <!--        <div class="clear"></div>-->
          </div>

          <!--      <div id="mkorder_user_loginbox_pwd" style="display:--><?//=$user_pwd_block_displ?><!--">-->
          <!--        <div style="height:20px">Пароль</div>-->
          <!--        <div style="float:left; margin-right:10px">-->
          <!--          <input name="zf_user_password" id="user_password" type="password" class="required" style="width:200px" maxlength="50"\>-->
          <!--          <div style="font-size: 7pt"><a href="/frame/?type=remind">забыли пароль?</a></div>-->
          <!--        </div>-->
          <!--        <div><input type="button" value="Авторизация" onclick="mkorder_auth_user()" style="width: 100px"></div>-->
          <!--        <div class="clear"></div>-->
          <!--      </div>-->
        </div>


        <div id="mkorder_user_name" class="row">
          <label for="zf_user_name">Ваше имя<span class="zvezda">*</span></label>
          <input type=text id="zf_user_name" name="zf_user_name" value="<?= @$_SESSION["zf_user_name"] ?>" class="required" maxlength="100">

          <div class="clear"></div>
        </div>

        <div id="mkorder_user_tel" class="row">
          <label for="zf_user_tel">Телефон для связи<span class="zvezda">*</span></label>
          <input type=text id="zf_user_tel" name="zf_user_tel" value="<?= @$_SESSION["zf_user_tel"] ?>" class="required telephone" maxlength="50">

          <div class="clear"></div>
        </div>

        <div id="mkorder_user_town" class="row">
          <label for="zf_user_city">Город<span class="zvezda">*</span></label>
          <input type=text id="zf_user_city" name="zf_user_city" value="<?= @$_SESSION["zf_user_city"] ?>" class="required" maxlength="100">

          <div class="clear"></div>
        </div>
        <div class="clear"></div>
      </div>

    <? endif ?>

    <div style="text-align: right; margin-bottom: 20px;">
      <input type=submit class="bttn" value="Отправить заказ" onclick="this.blur();">
    </div>

  </form>

<? endif ?>