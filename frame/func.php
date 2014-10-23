<?php

use Components\Entity\Client;

function get_min_max_from_str($s) {
  $ret = array(
    "min" => 0,
    "max" => 0
  );

  if (!strlen($s))
    return $ret;

  $s = preg_replace("/[^0-9]{1,}/i", " ", $s);
  $v = explode(" ", $s);

  if (count($v) == 1) {
    $ret["min"] = intval($v[0]);
  } elseif (count($v) == 2) {
    $ret["min"] = intval($v[0]);
    $ret["max"] = intval($v[1]);
  }

  return $ret;
}

function is_client_logged() {
  return isset($_SESSION["frame"]["client"]);
}

function auth_client($email) {
  if (is_client_logged()) {
    return true;
  }

  if (!validateEmail($email)) {
    return false;
  }

  $client = Client::findOneBy(array(
    'email' => $email,
  ));

  if (!$client) {
    return false;
  }

  $_SESSION["frame"]["client"] = $client;
  return true;
}

function unauth_user() {
  unset($_SESSION["frame"]['client']);
}

function validatePWD($pwd) {
  if ($pwd !== "") {
    if (preg_match("/^[A-Za-z0-9_]{5,20}$/", $pwd))
      return true; else
      return false;
  } else
    return false;
}


function clearText($src) {
  $src = str_replace("'", '"', $src);
  $search = array(
    "'<script[^>]*?>.*?</script>'si",
    // Вырезает javaScript
    "'<[\/\!]*?[^<>]*?>'si",
    // Вырезает HTML-теги
    //"'([\r\n])[\s]+'",                 // Вырезает пробельные символы
    "'&(quot|#34);'i",
    // Заменяет HTML-сущности
    "'&(amp|#38);'i",
    "'&(lt|#60);'i",
    "'&(gt|#62);'i",
    "'&(nbsp|#160);'i",
    "'&(iexcl|#161);'i",
    "'&(cent|#162);'i",
    "'&(pound|#163);'i",
    "'&(copy|#169);'i",
    "'&#(\d+);'e"
  ); // интерпретировать как php-код

  $replace = array(
    "",
    "",
    "\\1",
    "\"",
    "&",
    "<",
    ">",
    " ",
    chr(161),
    chr(162),
    chr(163),
    chr(169),
    "chr(\\1)"
  );

  $src = preg_replace($search, $replace, $src);

  //$src = htmlspecialchars($src);
  return $src;
}

////////////////

function print_header() {
  print "<html><head>";

  $css = "/frame/css/default.css";

  if (isset($_SESSION["style"])) {
    $css = $_SESSION["style"];
  }

  if (isset($_REQUEST["style"])) {
    $ncss = "./css/" . $_REQUEST["style"] . ".css";
    if (file_exists($ncss)) {
      $css = "/frame/css/" . $_REQUEST["style"] . ".css";
      $_SESSION["style"] = $css;
    }
  }

  print '<link rel="stylesheet" href="' . $css . '" />' . '<link rel="stylesheet" href="' . DIR_WS_JS . 'select2/select2.css" />' . '<link rel="stylesheet" href="/frame/css/jquery-ui.custom.css" />' . '<script type="text/javascript" src="/frame/js/jquery.js"></script>' . '<script type="text/javascript" src="/frame/js/jquery-ui.custom.min.js"></script>' . '<script type="text/javascript" src="/frame/js/ui.datepicker-ru.js"></script>' . '<script type="text/javascript" src="/frame/js/utils.js"></script>' . '<script type="text/javascript" src="/frame/js/valform.jquery.js"></script>' . '<script type="text/javascript" src="' . DIR_WS_JS . 'select2/select2.min.js"></script>' . '</head><body>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
(function (d, w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter22542955 = new Ya.Metrika({id:22542955,
                    webvisor:true,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true});
        } catch(e) { }
    });

    var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
    s.type = "text/javascript";
    s.async = true;
    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

    if (w.opera == "[object Opera]") {
        d.addEventListener("DOMContentLoaded", f, false);
    } else { f(); }
})(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="//mc.yandex.ru/watch/22542955" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
	';
}

function print_footer() {
  print "</body></html>";

}


function check_user_files() {
  $path = TMPFILES_PATH . session_id();

  $ret = array();

  if (file_exists($path)) {
    $d = opendir($path);
    if ($d) {
      while (false !== ($file = readdir($d))) {
        if (($file != ".") && ($file != ".."))
          $ret[] = array(
            "name" => $file,
            "size" => filesize($path . "/" . $file),
            "path" => $path . "/" . $file
          );
      }
      closedir($d);
    }
  }

  return $ret;
}

?>



