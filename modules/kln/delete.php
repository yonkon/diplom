<?php

use Components\Entity\Client;

$GUI->mmenu->selected->selected->caption = "Запрос на удаление";

$client = Client::find($_REQUEST["kln_id"]);
if ($client) {
  $frm = $GUI->Form("Удалить клиента?", 400, 100);
  $frm->OnExecute = "delclient_exec";
  $t = $frm->Hidden($client["id"]);
  $t->linkName = 'id';
  $frm->Label("Клиент: " . $client["fio"], 10, 10);
  $frm->Button("Удалить", 110, 40, 80, true);
  $b = $frm->Button("К списку", 210, 40, 80);
  $b->Event = "document.location.href=\"?section=kln&subsection=2\"; return false;";
} else {
  $GUI->ERR("Клиент не найден");
  page_reloadToSec("2");
}