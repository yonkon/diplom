<?php

use Components\Classes\db;

if (isset($_POST['referrer_system_profit'])) {
  db::update('configs', array(
    'value' => intval($_POST['referrer_system_profit']),
  ), "internal_name = 'REFERRER_SYSTEM_PROFIT'");
}

$config = get_config_by_iname('REFERRER_SYSTEM_PROFIT');

$GUI->Vars['page_top'] = '<form method="post" action=""><div>Укажите доход по партнерской программе % <input type="text" value="' . intval($config['value']) . '" name="referrer_system_profit" style="width: 100px;"/></div><div style="margin-top: 10px;"><button class="cgui_form_button_ovr" style="width: 100px;">Сохранить</button></div></form>';