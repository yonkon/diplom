<?php

use Components\Classes\Author;
use Components\Classes\Napravls;
use Components\Classes\Disciplines;

use Components\Entity\Napravl;
use Components\Entity\Discipline;

$author_id = $_SESSION['user']['data']['id'];

if (!empty($_POST['save'])) {
  $disciplines = isset($_POST['discipline']) ? $_POST['discipline'] : array();

  Author::delete_napravl_all($author_id);
  Author::addDisciplines($author_id, array_keys($disciplines));

  $GUI->OK('Сохранено');
}

$napravlAll = Napravl::findAll();

$authorNapravls = Author::get_napravl($author_id);
$authorDisciplines = Author::getDisciplines($author_id);

$result[] = '<table style="width: 100%;">';
if (count($napravlAll)) {
  foreach ($napravlAll as $napravl) {
    $disciplines = Napravls::getDisciplines($napravl['id']);

    $checked = in_array($napravl['id'], $authorNapravls);

    $result[] = '<tr style="background-color: #d3d3d3;">';
    $result[] = '<td colspan="100">';
    $result[] = '<div class="module_name">
    <label for="napravl[' . $napravl['id'] . ']">' . Napravls::getName($napravl['id']) . '</label>
    </div>';
    if (count($disciplines)) {
      $result[] = '&nbsp;&nbsp;';
      $result[] = '<span style="vertical-align: middle;line-height: 1.9;" data-toggle="select" data-target="napravl_' . $napravl['id'] . '">выбрать все</span>';
      $result[] = '&nbsp;&nbsp;&nbsp;&nbsp;';
      $result[] = '<span style="vertical-align: middle;line-height: 1.9;" data-toggle="collapse" data-target="napravl_' . $napravl['id'] . '">' . ($checked ? 'скрыть' : 'показать') . ' дисциплины<span>';
    }
    $result[] = '</td>';
    $result[] = '</tr>';
    if (count($disciplines)) {
      foreach ($disciplines as $discipline_id) {
        $result[] = '<tr class="napravl_' . $napravl['id'] . '"' . ($checked ? '' : 'style="display:none;"') . '>';
        $result[] = '<td style="width: 100px;">';
        $result[] = '</td>';
        $result[] = '<td colspan="2">';
        $result[] = '<div class="submodule_name"><label for="discipline[' . $discipline_id . ']">' . Disciplines::getName($discipline_id) . '</label></div>';
        $result[] = '<input type="checkbox" ' . (in_array($discipline_id, $authorDisciplines) ? 'checked="checked"' : '') . ' name="discipline[' . $discipline_id . ']" class="submodule_checkbox" id="discipline[' . $discipline_id . ']">';
        $result[] = '</tr>';
        $result[] = '</td>';
      }
    }
  }
}
$result[] = '</table>';

$GUI->Vars['table'] = join("\n", $result);