<?

use Components\Classes\Email;
use Components\Entity\Client;
use Components\Exceptions\Exception;
use Components\Classes\Filials;

if (isset($_REQUEST["codeimg"])) {
  ob_clean();

  $scode = generate_pasw(4);

  $_SESSION["remind_scode"] = $scode;

  $img = imagecreatetruecolor(100, 30);

  $cw = imagecolorallocate($img, 255, 255, 255);
  $cb = imagecolorallocate($img, 0, 0, 0);

  imagefill($img, 0, 0, $cw);

  $x = 10;

  for ($i = 0; $i < 4; $i++) {
    $y = 5 + mt_rand(0, 5);
    imagestring($img, 4, $x, $y, $scode[$i], $cb);
    $x += 10 + mt_rand(0, 10);
  }

  for ($i = 0; $i < 200; $i++) {
    $x = mt_rand(0, 99);
    $y = mt_rand(0, 29);
    imagesetpixel($img, $x, $y, $cw);
  }

  /*
  for ($i=0; $i<200; $i++)
  {
    $x = mt_rand(0, 99);
    $y = mt_rand(0, 29);
    imagesetpixel($img, $x, $y, $cb);
  }
   */

  imagejpeg($img, "", 100);

  ob_end_flush();

  die();
}

$error = "";

function check_rm_form(&$err) {

  if (isset($_REQUEST["ok"]))
    return true;

  $err = "";

  if (!isset($_REQUEST["rm_user_login"]) || !strlen($_REQUEST["rm_user_login"]))
    return false;
  if (!isset($_REQUEST["rm_user_code"]))
    return false;

  $login = clearText($_REQUEST["rm_user_login"]);
  $code = clearText($_REQUEST["rm_user_code"]);

  if (!strlen($login)) {
    $err = "Укажите адрес электронной почты";
    return false;
  }

  if (!validateEmail($login)) {
    $err = "Укажите корректный адрес электронной почты";
    return false;
  }

  if ((strlen($code) != 4) || ($code != @$_SESSION["remind_scode"])) {
    $err = "Неверный код";
    return false;
  }

  $client = Client::findOneBy(array(
    'email' => $login,
  ));
  if ($client) {
    $txt = "<p>Здравствуйте, " . $client["fio"] . "!</p>";
    $txt .= "Пароль для доступа к личному кабинету: <i>" . $client["password"] . "</i>";
    $txt .= "<p><i>С уважением, компания по написанию студенческих работ.</i></p>";

    $email = new Email();
    $email->setData(array(
      'email' => $client["email"],
      'name' => $client["fio"],
    ), "Восстановление пароля", $txt, array(), true, array(), array(
      'email' => Filials::getEmail($client["filial_id"]),
      'name' => Filials::getName($client["filial_id"]),
    ));
    $email->send();
  }

  ob_end_clean();
  header("location: ?type=remind&ok");
  die();

}

?>

  <div class="caption_txt" id="page_caption">Восстановление пароля</div>

<? if (!check_rm_form($error)): ?>

  <div class="normal_txt" id="mkorder_info"><? include "text_remind.html"; ?></div>

  <script>
    jQuery(function () {
      jQuery("#reminder_form").valform();
    });
  </script>

  <? if (strlen($error)): ?>
    <div id="mkorder_error_text" class="error_txt"><?= $error ?></div> <? endif ?>

  <form id="reminder_form" method="post">

    <div id="mkorder_user_box" class="mkorder_box">


      <div id="reminder_email">
        <div style="height:20px">Логин (электронная почта)<span class="zvezda">*</span></div>
        <div>
          <input name="rm_user_login" class="required email" value="<?= @$_REQUEST["rm_user_login"] ?>" id="user_email" type="text" style="width:200px" maxlength="50"
          \>
        </div>
      </div>


      <div id="reminder_code">
        <div style="height:20px">Код<span class="zvezda">*</span></div>
        <div>
          <input name="rm_user_code" class="required" value="" id="user_code" type="text" style="width:50px" maxlength="4"
          \>
        </div>
      </div>

      <div id="reminder_codeimage">
        <img style="border: 1; width: 100px; height: 30px;" src="?type=remind&codeimg=<?= time() ?>">
      </div>

      <div class="clear"></div>


    </div>

    <div style="text-align: right; margin-bottom: 20px;">
      <input style="margin-right: 10px;" type="submit" value="Отправить">
    </div>

  </form>

<? else: ?>

  <div class="normal_txt" id="mkorder_info"><? include "text_remind2.html"; ?></div>

<? endif ?>