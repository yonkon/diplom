<?php
use Components\Classes\db;

$query =
'SELECT *, FROM_UNIXTIME(created) as _date  FROM `ofc_order_files` where creator_id = 0
ORDER BY `ofc_order_files`.`creator_id`, _date ';
$db_result = db::get_arrays($query);
$hasFiles = 0;
foreach($db_result as $file) {
  if(is_file(DIR_WS_ORDER_FILES . $file['order_id'] . '/' . $file['name'])) {
    $filename = $file['name'];
    $hasFiles++;
    echo("<p>$filename</p>");
  }
}
echo "<p>Всего файлов: $hasFiles</p>";
$printr = print_r($db_result, true);
echo(nl2br($printr) );
die();