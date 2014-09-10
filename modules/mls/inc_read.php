<?php

use Components\Entity\Message;

use Components\Exceptions\InvalidArgumentException;

function find_prew_mails($id, $ret = array()) {
  try {
    $message = Message::find($id);
  } catch(InvalidArgumentException $e) {
    $message = array();
  }

  if (!$message) {
    return $ret;
  }

  $message["sender"] = mls_getAdrName($message["creator_id"]);
  $ret[] = $message;
  $ret = find_prew_mails($message["parent_id"], $ret);
  return $ret;
}

function find_aft_mails($id, $ret = array()) 
{
  try 
  {
    $message = Message::findOneBy(array(
      'parent_id' => $id,
    ));
  }
  catch(InvalidArgumentException $e)
  {
    $message = array();
  }

  if (!$message) {
    return $ret;
  }

  $message["sender"] = mls_getAdrName($message["creator_id"]);
  $ret[] = $message;
  $ret = find_aft_mails($message["id"], $ret);
  return $ret;
}

$type = "";
if (isset($_REQUEST["type"])) {
  $type = $_REQUEST["type"];
}
$GUI->Vars["type"] = $type;

$GUI->Vars["tmpl_info"] = "Сообщение №" . $message["id"] . " от " . date("d.m.y", $message["created"]) . ". Отправитель: " . mls_getAdrName($message["creator_id"]);

if ($message["needansv"]) {
  if (!Message::findBy(array(
    'parent_id' => $message["id"],
  ))) {
    $GUI->Vars["tmpl_info"] .= "<p style='color:red'>Требует ответа не позднее " . date("d.m.y", $message["needansv"]) . "</p>";
  }
}
$GUI->Vars["tmpl_subj"] = $message["subject"];
$GUI->Vars["tmpl_text"] = text_to_html($message["text"], "text-align:left");
$GUI->Vars["tmpl_ml"] = $message;

// При чтении входящих!
if ((($type == "i")||($type == "b")) && ($message["addr"] == "u" . $_SESSION["user"]["data"]["id"])) {
  mls_setreaded($message);
}

$GUI->Vars["tmpl_mls_prew_lim"] = false;
$GUI->Vars["tmpl_mls_after_lim"] = false;
$GUI->Vars["tmpl_mls_prew"] = find_prew_mails($message["parent_id"]);
$GUI->Vars["tmpl_mls_after"] = find_aft_mails($message["id"]);

//ограничить
$lim = 5;
if (count($GUI->Vars["tmpl_mls_prew"]) > $lim) {
  $GUI->Vars["tmpl_mls_prew_lim"] = count($GUI->Vars["tmpl_mls_prew"]) - $lim;
  while (count($GUI->Vars["tmpl_mls_prew"]) > $lim) {
    array_pop($GUI->Vars["tmpl_mls_prew"]);
  }
}

if (count($GUI->Vars["tmpl_mls_after"]) > $lim) {
  $GUI->Vars["tmpl_mls_after_lim"] = count($GUI->Vars["tmpl_mls_after"]) - $lim;
  while (count($GUI->Vars["tmpl_mls_after"]) > $lim) {
    array_pop($GUI->Vars["tmpl_mls_after"]);
  }
}

page_scriptNeed("scripts.js", "modules/mls");