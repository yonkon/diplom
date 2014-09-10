<div style="margin-top:20px; padding: 5px"><center>

    <?php if (isset($this->Vars["page_top"])) print $this->Vars["page_top"]?>

<?php if(isset($this->Vars["info_excel"])):?>
<div style='margin-bottom: 20px'>
Для импорта необходимо использовать excel-файл с данными в 3 столбца (A1 - краткое название,B1 - полное название,C1 - адрес, телефон)
</div>
<?php endif?>

<?php if(isset($this->Vars["info_excel1"])):?>
<div style='margin-bottom: 20px'>
Для импорта необходимо использовать excel-файл с данными в 2 столбца (A1 - код дисциплины,B1 - название)
</div>
<?php endif?>

<?php if(isset($this->Vars["info_excel_stations"])):?>
<div style='margin-bottom: 20px'>
Для импорта необходимо использовать excel-файл с данными в 1 столбец (A1 - название станции)
</div>
<?php endif?>

<?php if(isset($this->Vars["info_excel_city"])):?>
<div style='margin-bottom: 20px'>
Для импорта необходимо использовать excel-файл с данными в 1 столбец (A1 - название города)
</div>
<?php endif?>

<?php
if (isset($this->tables[0])) $this->tables[0]->PrintTable(); 
if (isset($this->forms[0]))print $this->forms[0]->GetHTML();

?>

</center></div>