<?php

namespace Components\Classes;

class MysqlToExcel {
  private $phpExcel;
  private $worksheet;
  private $data = array();
  private $module;
  private $submodule;
  private $availableColumns = array();


  public function __construct() {
    $this->phpExcel = new \PHPExcel();
    $this->phpExcel->setActiveSheetIndex(0);
    $this->worksheet = $this->phpExcel->getActiveSheet();
  }

  public function setWorkSheetName($worksheet_name) {
    $this->worksheet->setTitle($worksheet_name);
  }

  public function setData($data = array()) {
    $this->data = $data;
  }

  public function setModuleName($module_name) {
    $this->module = $module_name;
  }

  public function setSubModuleName($submodule_name) {
    $this->submodule = $submodule_name;
  }

  public function getOutput($file_name) {
    // Создаем "писателя"
    $writer = \PHPExcel_IOFactory::createWriter($this->phpExcel, 'Excel2007');
    // Сохраняем файл
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $file_name . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
  }

  public function writeData() {
    $this->getAvailableColumns();
    require_once(DIR_FS_DOCUMENT_ROOT . '/modules/' . $this->module . '/functions.php');
    // Счетчик строк
    $row = 1;

    foreach($this->data as $rows) {
      // Перебираем столбцы и пишем в лист Excel
      $cell = 0;
      foreach ($rows as $column => $value) {
        if (!array_key_exists($column, $this->availableColumns)) {
          continue;
        }

        $column = $this->availableColumns[$column];
        if (count($column) != count($column, 1)) {
          foreach($column as $multi_column) {
            $value = $this->getColumnValue($multi_column, $value, $rows);
            $this->setCellValue($row, $cell, $multi_column, $value);
          }
        } else {
          $value = $this->getColumnValue($column, $value, $rows);
          $this->setCellValue($row, $cell, $column, $value);
        }
      }

      // Увеличиваем счетчик
      if ($row == 1) {
        $row = 3;
      } else {
        $row++;
      }
    }
  }

  private function getAvailableColumns() {
    $res = db::query("
      SELECT sc.name, sc.on_execute, sc.internal_name
      FROM " . TBL_PREF . "submodule_columns sc
      JOIN " . TBL_PREF . "modules m ON m.id = sc.module_id
      JOIN " . TBL_PREF . "submodules sm ON sm.id = sc.submodule_id
      WHERE m.internal_name = '" . db::input($this->module) . "'
        AND sm.name = '" . db::input($this->submodule) . "'
    ");

    $result = array();
    while($row = db::fetch_array($res)) {
      if (isset($result[$row['internal_name']])) {
        $temp = $result[$row['internal_name']];
        unset($result[$row['internal_name']]);
        $result[$row['internal_name']][] = $temp;
        $result[$row['internal_name']][] = array(
          'name' => $row['name'],
          'handler' => $row['on_execute'],
        );
      } else {
        $result[$row['internal_name']] = array(
          'name' => $row['name'],
          'handler' => $row['on_execute'],
        );
      }
    }

    $this->availableColumns = $result;
  }

  private function setCellValue(&$row, &$cell, $column, $value) {
    if ($row == 1) {
      $this->worksheet->setCellValueByColumnAndRow($cell, $row, $column['name']);
      $this->worksheet->setCellValueByColumnAndRow($cell, $row + 1, $value);
    } else {
      $this->worksheet->setCellValueByColumnAndRow($cell, $row, $value);
    }
    $cell++;
  }

  private function getColumnValue($column, $value, $row) {
    if (!empty($column['handler']) && is_callable($column['handler'])) {
      $new_value = strip_tags(call_user_func($column['handler'], $value, $row, false, false));

      if ($new_value != '') {
        $value = $new_value;
      }
    }

    return $value;
  }
}