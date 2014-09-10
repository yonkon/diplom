<?

require_once('../includes/application_top.php');

define("TMPLS_PATH", "./htmls/");
define("TMPFILES_PATH", "./tmp_files/");
define("FILES_SZ_LIMIT", 6 * 1024 * 1024);

include_once "func.php";

// 1. Форма заказа - всегда. type=form
// 2. Логин в кабинет / Регистрация в кабинет - если не вошел
// 3. Восстановить пароль - если не вошел
// 4. Кабинет - если вошел
// Кабинет - инфо
// Кабинет - заказы
// Кабинет - заказ


// header
print_header();

if (isset($_REQUEST["type"]) && ($_REQUEST["type"] == "form")) {
  // order form
  include TMPLS_PATH . "makeorder.php";
} else {
  // cabinet
  if (is_client_logged()) {
    include TMPLS_PATH . "cabinet.php";
  } else {
    if (isset($_REQUEST["type"]) && ($_REQUEST["type"] == "remind")) {
      include TMPLS_PATH . "remind.php";
    } else {
      include TMPLS_PATH . "login.php";
    }
  }
}

print_footer();