<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Контактная информация");
?>
<div class="row">
	<div class="col-xs-12">
	
		<p><b>Пункт самовывоза:</b></p>
		<p>В настоящий момент пункт самовывоза  Интернет-Магазин Suprashop ЗАКРЫТ. 
		Приносим свои извинения за доставленные неудобства.
		 
		<p><b>Контакты:</b></p>
		<p>Телефон: +7 800 775 49 37
		<p>e-mail: shop@supra.ru
		<p>Время работы: по будним дням — с 10:00 до 18:00 

		<p><b>Для юридических лиц</b></p>
		<p>Если Вас интересуют оптовые закупки или сотрудничество Вы можете направить Ваш запрос на нашу электронную почту shop@supra.ru.
		<p>В письме Вы должны указать: Название компании, контактный телефон 
		и интересующий вас товар. Или связаться с нами по телефону: +7 800 775 49 37
		 
		<p><b>Наши реквизиты</b></p>

		<table class="contacts_table_rekv">
		<tr><td>Наименование:</td><td>ИП Полубнев Алексей Андреевич</td></tr>
		<tr><td>Юридический адрес:</td><td>117648, г.Москва, микрорайон Чертаново Северное д.3 к.А. кв.121</td></tr>
		<tr><td>ИНН:</td><td>772602732545</td></tr>
		<tr><td>ОГРН:</td><td>314774605100421</td></tr>
		<tr><td>Расчетный счет:</td><td>40802810900000005668</td></tr>
		<tr><td>Наименование банка:</td><td>ПАО «ПРОМСВЯЗЬБАНК»</td></tr>
		<tr><td>Корр. счет:</td><td>30101810400000000555</td></tr>
		<tr><td>БИК: </td><td>044525555</td></tr>
		</table>

 <?$APPLICATION->IncludeComponent(
	"bitrix:map.yandex.view",
	"",
	Array(
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"CONTROLS" => array("ZOOM", "MINIMAP", "TYPECONTROL", "SCALELINE"),
		"INIT_MAP_TYPE" => "MAP",
		"MAP_DATA" => "a:4:{s:10:\"yandex_lat\";d:55.63929299999386;s:10:\"yandex_lon\";d:37.59657900000001;s:12:\"yandex_scale\";i:17;s:10:\"PLACEMARKS\";a:1:{i:0;a:3:{s:3:\"LON\";d:37.59662191534387;s:3:\"LAT\";d:55.639286931387765;s:4:\"TEXT\";s:0:\"\";}}}",
		"MAP_HEIGHT" => "500",
		"MAP_ID" => "",
		"MAP_WIDTH" => "",
		"OPTIONS" => array("ENABLE_SCROLL_ZOOM", "ENABLE_DBLCLICK_ZOOM", "ENABLE_DRAGGING")
	)
);?>
		
	</div>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>