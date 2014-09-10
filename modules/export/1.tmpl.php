<div style="margin-top:20px; padding: 5px" class="export_links_wrap">

  <?php

  use Components\Classes\Roles;

  global $GUI, $n;

  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Скачать базу заказов")) {
    echo '<a class="show_as_button" href="index.php?section=' . $GUI->mmenu->selected->section . '&subsection=' . $n . '&entity=orders">Скачать базу заказов</a>';
  }

  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Скачать базу клиентов")) {
    echo '<a class="show_as_button" href="index.php?section=' . $GUI->mmenu->selected->section . '&subsection=' . $n . '&entity=clients">Скачать базу клиентов</a>';
  }

  if (Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Скачать базу сотрудников")) {
    echo '<a class="show_as_button" href="index.php?section=' . $GUI->mmenu->selected->section . '&subsection=' . $n . '&entity=users">Скачать базу сотрудников</a>';
  }
  ?>

</div>