<?php

use Components\Classes\Roles;
use Components\Classes\MysqlToExcel;

use Components\Entity\Order;
use Components\Entity\Client;
use Components\Entity\Employee;

if (!empty($_GET['entity'])) {
  set_time_limit(300);
  ini_set('memory_limit', '1000M');

  switch($_GET['entity']) {
    case 'orders':
      if (!Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Скачать базу заказов")) {
        $GUI->ERR('У вас нету прав');
        page_reloadSubSec();
      }

      $export = new MysqlToExcel();
      $export->setWorkSheetName('База заказов');
      $export->setModuleName('ord');
      $export->setSubModuleName('Список');

      if (is_director($_SESSION["user"]["data"]["id"])) {
        $orders = Order::findAll();
      } else {
        $orders = Order::findBy(array(
          'filial_id' => $_SESSION["user"]["data"]["filial_id"],
        ));
      }

      $export->setData($orders);

      $export->writeData();

      $export->getOutput('Заказы');
      die;
      break;

    case 'clients':
      if (!Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Скачать базу клиентов")) {
        $GUI->ERR('У вас нету прав');
        page_reloadSubSec();
      }

      $export = new MysqlToExcel();
      $export->setWorkSheetName('База клиентов');
      $export->setModuleName('kln');
      $export->setSubModuleName('Список');

      if (is_director($_SESSION["user"]["data"]["id"])) {
        $clients = Client::findAll();
      } else {
        $clients = Client::findBy(array(
          'filial_id' => $_SESSION["user"]["data"]["filial_id"],
        ));
      }

      $export->setData($clients);

      $export->writeData();

      $export->getOutput('Клиенты');
      die;
      break;

    case 'users':
      if (!Roles::isActionAllowed($GUI->mmenu->selected->id, $GUI->mmenu->selected->selected->id, $_SESSION["user"]["data"]["group_id"], "Скачать базу сотрудников")) {
        $GUI->ERR('У вас нету прав');
        page_reloadSubSec();
      }

      $export = new MysqlToExcel();
      $export->setWorkSheetName('База сотрудников');
      $export->setModuleName('sotr');
      $export->setSubModuleName('Список');

      if (is_director($_SESSION["user"]["data"]["id"])) {
        $employers = Employee::findAll();
      } else {
        $employers = Employee::findBy(array(
          'filial_id' => $_SESSION["user"]["data"]["filial_id"],
        ));
      }

      $export->setData($employers);

      $export->writeData();

      $export->getOutput('Сотрудники');
      die;
      break;

    default:
      $GUI->ERR('У вас нету прав');
      page_reloadSubSec();
  }
}