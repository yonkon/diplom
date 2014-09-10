<?php

use Components\Entity\Client;
use Components\Classes\Email;
use Components\Classes\Filials;
use Components\Entity\Filial;

if (!is_client_logged() || $_SESSION["frame"]["client"]["blocked"]) {
  echo 'Доступ запрещен.';
} else {
  $info = "";
  $error = "";

  if (isset($_REQUEST["cab_prof_infochng"])) {
    $c = clearText($_REQUEST["cab_prof_infochng"]);
    $c = substr($c, 0, 500);

    Client::update($_SESSION["frame"]["client"]["id"], array(
      'contacts' => $c,
    ));
    $_SESSION["frame"]["client"]["contacts"] = $c;

    $info = "Сохранено";
  }

  if (isset($_REQUEST["cab_prof_pwdchng"]) && is_array($_REQUEST["cab_prof_pwdchng"])) {
    $a = $_REQUEST["cab_prof_pwdchng"];

    if ($a["old"] == $_SESSION["frame"]["client"]["password"]) {
      if ($a["new"] == $a["rep"]) {
        $new = preg_replace("/[^0-9a-z]/i", "", $a["new"]);

        if ((strlen($new) > 4) && (strlen($new) < 21)) {
          $hpwd = md5($new . strtolower($_SESSION["frame"]["client"]["email"]));
          Client::update($_SESSION["frame"]["client"]["id"], array(
            'hpwd' => $hpwd,
            'password' => $new,
          ));

          $info = "Пароль изменен";

          $txt = "<p>Здравствуйте, " . $_SESSION["frame"]["client"]["fio"] . "!</p>" . "Новый пароль для доступа к личному кабинету: <i>" . $new . "</i>" . "<p><i>С уважением, компания по написанию студенческих работ.</i></p>";

          $email = new Email();
          $email->setData(array(
            'email' => $_SESSION["frame"]["client"]["email"],
            'name' => $_SESSION["frame"]["client"]["fio"],
          ), "Изменение пароля к личному кабинету", $txt, array(), true, array(), array(
            'email' => Filials::getEmail($_SESSION["frame"]["client"]["filial_id"]),
            'name' => Filials::getName($_SESSION["frame"]["client"]["filial_id"]),
          ));

          if ($email->send()) {
            $info .= ". Сообщение на почту отправлено.";
          } else {
            $info .= ". Не удалось отправить сообщение на почту.";
          }
        } else $error = "Длина пароля должна быть 5-20 символов 0-9,a-z";
      } else $error = "Пароли не совпадают";
    } else $error = "Не верный текущий пароль";
  }

  $filial = Filial::find($_SESSION["frame"]["client"]["filial_id"]);

?>
  <script type="text/javascript">
    jQuery(function () {
      jQuery("#cab_prof_pwdform").valform();
    });
  </script>


  <? if (strlen($info)): ?>
    <div style="margin-bottom: 10px;" class="info_txt"><?= $info ?></div>
  <? endif ?>

  <? if (strlen($error)): ?>
    <div style="margin-bottom: 10px;" class="error_txt"><?= $error ?></div>
  <? endif ?>


  <div id="cab_prof_info">
    <div><b>Контактные данные</b></div>

    <form method="post">

      <div class="cab_prof_fld_capt">Имя: <i><?= $_SESSION["frame"]["client"]["fio"] ?></i></div>
      <div class="cab_prof_fld_capt">Почта: <i><?= $_SESSION["frame"]["client"]["email"] ?></i></div>
      <div class="cab_prof_fld_capt">Телефон: <i><?= $_SESSION["frame"]["client"]["telnum"] ?></i></div>

      <div class="cab_prof_fld_capt">Дополнительные контактные данные:</div>
      <div class="cab_prof_fld">
        <textarea style="width: 250px; height: 60px;" name="cab_prof_infochng"><?= $_SESSION["frame"]["client"]["contacts"] ?></textarea>
      </div>

      <div class="cab_prof_fld" style="margin-top: 10px"><input type="submit" value="Сохранить"></div>

    </form>
  </div>

  <div id="cab_prof_pwd">
    <div><b>Смена пароля</b></div>
    <form id="cab_prof_pwdform" method="post">
      <div class="cab_prof_fld_capt">Текущий пароль</div>
      <div class="cab_prof_fld"><input type="password" class="required" name="cab_prof_pwdchng[old]"></div>
      <div class="cab_prof_fld_capt">Новый пароль (5-20 символов)</div>
      <div class="cab_prof_fld"><input type="password" class="required" name="cab_prof_pwdchng[new]"></div>
      <div class="cab_prof_fld_capt">Повтор нового пароля</div>
      <div class="cab_prof_fld"><input type="password" class="required" name="cab_prof_pwdchng[rep]"></div>
      <div class="cab_prof_fld" style="margin-top: 10px"><input type="submit" value="Изменить"></div>
    </form>
  </div>

  <div id="cab_ref_block">
    <div><b>Код на скидку<br/>5%</b></div>
    <div class="cab_prof_fld" style="margin-top: 10px">
      <?= $_SESSION["frame"]["client"]["referrer_code"] ?>
    </div>
    <div class="cab_prof_fld_capt" style="text-align: left;">Партнерская ссылка, дающая Вам возможность заработать <?= get_config_value_by_iname('REFERRER_SYSTEM_PROFIT'); ?>% с каждого заказа:</div>
    <div class="cab_prof_fld">
      <?= $filial['order_form_path'] . '?ref=' . $_SESSION["frame"]["client"]["referrer_code"]; ?>
    </div>
    <div class="cab_prof_fld" style="margin-top: 10px"><a href="/frame/?type=cabinet&referrer=1&p=1">Узнать подробнее</a></div>
  </div>

  <div class="clear"></div>
<?
}
?>