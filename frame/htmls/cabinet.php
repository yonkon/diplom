<? if (!is_client_logged() || $_SESSION["frame"]["client"]["blocked"]): ?>
  Доступ запрещен.
<? else: ?>

  <?

  if (isset($_REQUEST["logout"])) {
    ob_end_clean();
    unauth_user();
    header("Location: ?type=cabinet");
    die();
  }


  $mode = 0;
  if (isset($_REQUEST["profile"]))
    $mode = 1;
  if (isset($_REQUEST["visits"]))
    $mode = 2;
  if (isset($_REQUEST["messages"]))
    $mode = 3;
  if (isset($_REQUEST["referrer"]))
    $mode = 4;


  ?>

  <div class="cabinet_mmenu">
    <a <? if ($mode == 1): ?>class="selected"<? endif ?> href="?type=cabinet&profile">Профиль</a>&nbsp;
    |&nbsp;<a <? if ($mode == 0): ?>class="selected"<? endif ?> href="?type=cabinet">Все заказы</a>&nbsp;
    |&nbsp;<a <? if ($mode == 2): ?>class="selected"<? endif ?> href="?type=cabinet&visits">Встречи</a>&nbsp;
    |&nbsp;<a <? if ($mode == 3): ?>class="selected"<? endif ?> href="?type=cabinet&messages">Сообщения</a>&nbsp;
    |&nbsp;<a <? if ($mode == 4): ?>class="selected"<? endif ?> href="?type=cabinet&referrer=1">Партнерская
      программа</a>&nbsp;
    |&nbsp;<a href="?logout">Выход</a>
  </div>


  <?

  switch ($mode) {
    case 0:
      include "cabinet_orders.php";
      break;
    case 1:
      include "cabinet_profile.php";
      break;
    case 2:
      include "cabinet_visits.php";
      break;
    case 3:
      include "cabinet_messages.php";
      break;
    case 4:
      include "cabinet_referrer_system.php";
      break;
  }

  ?>


<? endif ?>