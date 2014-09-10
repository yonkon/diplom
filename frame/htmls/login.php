<div class="caption_txt" id="page_caption">Вход в личный кабинет / регистрация</div>
<?

use Components\Entity\Client;
use Components\Classes\Filials;
use Components\Classes\Email;

function create_new_user($login, &$err) {
  $err = "";

  $name = clearText($_REQUEST["loginform"]["name"]);

  if (!strlen($name)) {
    $err = "Укажите имя";
    return false;
  }

  $tel = clearText($_REQUEST["loginform"]["tel"]);

  $city = clearText($_REQUEST["loginform"]["city"]);

  $cont = clearText($_REQUEST["loginform"]["cont"]);

  $liketel = intval(clearText(@$_REQUEST["loginform"]["liketel"]));

  $teltime = clearText(@$_REQUEST["loginform"]["teltime"]);

  $filial_domain = clearText(@$_REQUEST['loginform']['zf_filial_domain']);

  //get filial by domain
  $add_to_filial = Filials::search($filial_domain, $city);

  // create client
  $pwd = generate_pasw(5);

  $client_id = Client::create(array(
    'filial_id' => $add_to_filial,
    'fio' => $name,
    'email' => $login,
    'telnum' => $tel,
    'city' => $city,
    'liketel' => $liketel,
    'teltime' => $teltime,
    'contacts' => $cont,
    'password' => $pwd,
  ));

  if ($client_id < 1)
    return false;

  auth_client($login);

  // send reg email
  $txt = "<p>Здравствуйте, " . $name . "!</p>" . "<p>Мы очень рады, что Вы решили воспользоваться нашими услугами и высоко ценим Ваше доверие!</p>" . "<p>Теперь Вы можете войти в личный кабинет:<br>" . "&nbsp;Логин: " . $login . "<br>" . "&nbsp;Пароль: " . $pwd . "<br></p>" . "<p><i>С уважением, компания по написанию студенческих работ.</i></p>";

  $email = new Email();
  $email->setData(array(
    'email' => $login,
    'name' => $name,
  ), "Регистрация на сайте написания рефератов", $txt, array(), true, array(), array(
    'email' => Filials::getEmail($add_to_filial),
    'name' => Filials::getName($add_to_filial),
  ));

  if (!$email->send())
    die();

  return true;
}

$error = "";

$show_reg_form = true;

if (isset($_REQUEST["loginform"]) && is_array($_REQUEST["loginform"])) {
  // add user
  $login = $_REQUEST["loginform"]["login"];

  $client = Client::findOneBy(array(
    'email' => $login,
  ));

  if (!$client) {
    // create user
    if (create_new_user($login, $error)) {
      $show_reg_form = false;
    }
  }
}

if ($show_reg_form) {
  $user_pwd_block_displ = "none";
  $user_reg_block_displ = "none";

  if (isset($_REQUEST["loginform"]) && isset($client) && empty($client)) {
    $user_reg_block_displ = "block";
    $user_pwd_block_displ = "none";
  }
}

?>

<? if ($show_reg_form): ?>

  <div class="normal_txt" id="mkorder_info"><? include "text_register.html"; ?></div>

  <script>
    jQuery(function () {
      jQuery("#login_form").valform();
      jQuery("#user_password").keydown(function (e) {
        login_pwd_key(e);
      });

      var referrer = document.referrer;
      if (typeof referrer != 'undefined' && referrer != '') {
        var domain = referrer.match(/:\/\/(.[^/]+)/)[1];
        $('#filial_domain').val(domain);
      }
    });
  </script>

  <? if (strlen($error)): ?>
    <div id="mkorder_error_text" class="error_txt"><?= $error ?></div>
  <? endif ?>

  <form id="login_form" method="post">
    <input type="hidden" name="loginform[zf_filial_domain]" value="" id="filial_domain">

    <div id="mkorder_user_box" class="mkorder_box">

      <div id="mkorder_user_loginbox">

        <div id="mkorder_user_loginbox_login">
          <div style="height:20px">Логин (электронная почта)<span class="zvezda">*</span></div>
          <div>
            <input name="loginform[login]" class="required email" value="<?= @$_REQUEST["loginform"]["login"] ?>" id="user_email" type="text" style="width:200px" maxlength="50" onkeyup="checkuser_change_lgn(this)"\>
          </div>
          <div id="checkuser_info_text" style="font-size:7pt; color:gray; height: 10px"></div>
        </div>

        <div id="mkorder_user_loginbox_pwd" style="display:<?= $user_pwd_block_displ ?>">
          <div style="height:20px">Пароль</div>
          <div style="float:left; margin-right:20px">
            <input name="loginform[pwd]" id="user_password" type="password" class="required" style="width:200px" maxlength="50"\>
            <div style="font-size: 7pt"><a href="/frame/?type=remind">забыли пароль?</a></div>
          </div>
          <div><input type="button" value="Авторизация" onclick="login_auth_user()" style="width: 100px"></div>
          <div class="clear"></div>
        </div>
        <div class="clear"></div>
      </div>

      <div id="login_user_reg" style="display:<?= $user_reg_block_displ ?>">

        <div id="mkorder_user_name">
          <div>Ваше имя<span class="zvezda">*</span></div>
          <div style="margin-top:5px;">
            <input type=text name="loginform[name]" value="<?= @$_REQUEST["loginform"]["name"] ?>" class="required" style="width:450px;" maxlength="100">
          </div>
        </div>

        <div id="mkorder_user_tel">
          <div>Телефон для связи<span class="zvezda">*</span></div>
          <div style="margin-top:5px;">
            <input type=text name="loginform[tel]" value="<?= @$_REQUEST["loginform"]["tel"] ?>" class="required telephone" style="width:200px;" maxlength="50">
          </div>
        </div>

        <div id="mkorder_user_town">
          <div>Город<span class="zvezda">*</span></div>
          <div style="margin-top:5px;">
            <input type=text name="loginform[city]" value="<?= @$_REQUEST["loginform"]["city"] ?>" class="required" style="width:200px;" maxlength="100">
          </div>
        </div>
        <div class="clear"></div>

        <div id="mkorder_user_cont">
          <div>Дополнительные контактные данные (icq,skype,...)</div>
          <div style="margin-top:5px;">
            <input type=text name="loginform[cont]" value="<?= @$_REQUEST["loginform"]["cont"] ?>" style="width:450px;" maxlength="100">
          </div>
        </div>

        <?
        $v = "none";
        $c = "";
        if (@$_REQUEST["loginform"]["liketel"]) {
          $c = "checked=\"checked\"";
          $v = "block";
        }
        ?>

        <div id="mkorder_user_liketel">
          <input type="checkbox" value="1" <?= $c ?> id="zf_user_liketel" name="loginform[liketel]" style="border:0; float:left" onchange="mkorder_liketel_change(this.checked)">

          <div><label for="zf_user_liketel">Предпочитаю общение по телефону</label></div>
        </div>

        <div id="mkorder_teltime_block" style="display:<?= $v ?>">
          <div>Удобное время звонка</div>
          <div style="margin-top:5px">
            <input type=text name="loginform[teltime]" value="<?= @$_REQUEST["loginform"]["teltime"] ?>" style="width:200px;" maxlength="100">
          </div>
        </div>

        <div class="clear"></div>

        <div>
          <input type="submit" value="Зарегистрироваться">
        </div>
      </div>
    </div>

    <button type="button" class="bttn" id="login_button" onclick="checkuser_change_lgn($('#user_email').get(0))">Войти</button>

  </form>

<? else: ?>

  <div class="normal_txt" id="mkorder_info"><? include "text_register_ok.html"; ?></div>

<? endif ?>