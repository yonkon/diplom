<?php

use Components\Entity\Message;

page_scriptNeed("scripts.js", "modules/mls");

function _email_notification_delete($value, $row, $table, &$info)
{
   return '<a href="#" onclick="email_notification_delete('.$row['id'] . ');">Удалить</a>';
}

if (isset($_REQUEST["ids_to_read"]))
{
	$n=0;
	$ids = explode(";", $_REQUEST["ids_to_read"]);
	foreach ($ids as $id)
	{
		if (intval($id) > 0)
		{
			$message = Message::find($id);
			if ($message)
			{
				if ($message["addr"] == "u" . $_SESSION["user"]["data"]["id"])
				{
					mls_setreaded($message);
					$n++;
				}
			}
		}
	}
	
	$GUI->OK("Отмечено писем: " . $n);
	
	die("".$n);
}

if (isset($_REQUEST["ids_to_trash"]))
{
	$n=0;
	$ids = explode(";", $_REQUEST["ids_to_trash"]);
	foreach ($ids as $id)
	{
		if (intval($id) > 0)
		{
			$message = Message::find($id);
			if ($message)
			{
				if ($message["addr"] == "u" . $_SESSION["user"]["data"]["id"])
				{
					mls_setbasket($message, 1);
					$n++;
				}
			}
		}
	}
	
	$GUI->OK("Перемещено в корзину писем: " . $n);
	
	die("".$n);
}
