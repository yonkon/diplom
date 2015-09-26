<?php

ini_set('memory_limit', '1000M');

use Components\Entity\Employee;
use Components\Entity\Module;
use Components\Classes\db;
use Components\Classes\Roles;

require_once('includes/application_top.php');

use Components\Classes\MysqlToExcel;

use Components\Entity\Order;
use Components\Entity\Client;

require_once(DIR_FS_DOCUMENT_ROOT . '/ext/PHPExcel/PHPExcel.php');
ini_set('memory_limit', '1000M');

//need_data('data_vuz');
//need_data('data_worktypes');
//need_data('data_napravl');
//need_data('data_discip');
//need_data('data_payments');
//need_data('data_filials');

$export = new MysqlToExcel();
$export->setWorkSheetName('База заказов');
$export->setModuleName('ord');
$export->setSubModuleName('Список');
$limit = $argv[1];
$offset = $argv[2];
$orders = Order::findBy(array(), null, $limit, $offset );
$export->setData($orders);

$export->writeData();

$export->saveOutput('Заказы_export.xls');
echo 'Готово';
die;