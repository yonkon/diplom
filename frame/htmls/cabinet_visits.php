<?php

use Components\Entity\Meeting;
use Components\Entity\SubwayStation;
use Components\Exceptions\Exception;

if (!is_client_logged() || $_SESSION["frame"]["client"]["blocked"]) {
  echo 'Доступ запрещен.';
} else {
  $visits = Meeting::findBy(array(
    'client_id' => $_SESSION["frame"]["client"]["id"],
  ), array(
    'date' => 'DESC',
  ));

  function print_visits_table($vs) {
    $status_name = array(
      "не проведена",
      "проведена",
      "отменена"
    );

    print
      "<div style='height: 450px; overflow: auto;'>" . "<table cellpadding='4' cellspacing='1' width='100%'><tr class='header'>" . "<td>№</td><td>Дата</td><td>Время</td><td>Место</td><td>Статус</td><td>Описание</td></tr>";

    foreach ($vs as $v) {
      $place = "";
      if ($v["filial_id"] == -1) {
        $place = "курьер";
        if ($v["station_id"] > 0) {
          try {
            $subway_station = SubwayStation::find($v["station_id"]);
            $place .= " (ст. " . $subway_station["name"] . ")";
          } catch(Exception $e) {
            $place = '';
          }
        }
      } else {
        $place = "офис";
      }

      $ts = sprintf("%02d:%02d", floor($v["tm_start"] / 60), ($v["tm_start"] % 60));
      $tf = sprintf("%02d:%02d", floor($v["tm_finish"] / 60), ($v["tm_finish"] % 60));

      print "<tr><td>" . $v["id"] . "</td><td>" . date("d.m.Y", $v["date"]) . "</td>" . "<td>" . $ts . "-" . $tf . "</td>" . "<td>" . $place . "</td>" . "<td>" . $status_name[$v["status"]] . "</td>" . "<td>" . $v["about"] . "</td></tr>";

    }

    print "</table></div>";
  }

  print_visits_table($visits);
}