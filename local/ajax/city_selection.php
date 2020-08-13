<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('statistic');
CModule::IncludeModule('sale');

// В итоговом виде у нас должно получиться 2 колоки: слева Регионы, справа привязанные к ним города

// Замечания:
// ВАЖНО: в данном примере рассматривается работа только с одной страной - РФ.
// Москва и Питер идтут особняком т.к. Города=Регионы
// у некоторых городов нет привязки к региону, пример - Зеленоград. В таком случае будем получать привязку к региону через родителя
// 

$arRegion = $arLocation = $arCity = [];

// чтобы не делать кучу запросов, получим сразу все местоположения
$resLoc = \Bitrix\Sale\Location\LocationTable::getList(array(
	'order' => array("NAME_RU" => "ASC"),
    'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
    'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
));
while($itemLoc = $resLoc->fetch())
{
	$arLocation[$itemLoc["ID"]] = $itemLoc;
}

// собираем регионы и города
// чтобы не заморачиваться с сортировкой, просто разобъем на два масива arRegion и arCity
foreach($arLocation as $id => $arLoc)
{
	if($arLoc["TYPE_CODE"] == "REGION" || in_array($arLoc["ID"], [84,85])) // Регионы + Мск и Спб
		$arRegion[$arLoc["ID"]] = $arLoc;
		
	if($arLoc["TYPE_CODE"] == "CITY")
	{
		$parent = "";
		
		if(!empty($arLoc["REGION_ID"]))
			$parent = $arLoc["REGION_ID"];
		elseif(in_array($arLoc["ID"], [84,85]))
			$parent = $arLoc["ID"];
		elseif(!empty($arLoc["PARENT_ID"]))
			$parent = $arLoc["PARENT_ID"];
		
		if(!empty($parent))
		{
			$arCity[$parent]["CITIES"][] =  $arLoc;
		}
	}
}

// вывод
?>
<div class="ajax-city-selection">
	<div class="ajax-city-selection__regions">
		<ul>
			<?
			foreach($arRegion as $region)
			{
				?>
				<li><a href=""><?=$region["NAME_RU"]?></a></li>
				<?
			}
			?>
		</ul>
	</div>
	<div class="ajax-city-selection__cities">
		<?
			foreach($arCity as $region)
			{
				?>
				<div class="cities-region-<?=$region["ID"]?>">
					<ul>
						<?
						foreach($region["CITIES"] as $city)
						{
							?><li><a href=""><?=$city["NAME_RU"]?></a></li><?
						}
						?>
					</ul>
				</div>
				<?
			}
			?>
	</div>
</div>
<?


?>