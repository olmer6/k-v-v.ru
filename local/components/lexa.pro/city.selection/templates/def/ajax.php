<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('sale');

// смена города
if(!empty($_REQUEST["CITY_NAME"]) && !empty($_REQUEST["CITY_CODE"]) && !empty($_REQUEST["CITY_ZIP"]))
{
	$_SESSION["CITYSELECTION_CITY"]["NAME"] = $_REQUEST["CITY_NAME"];
	$_SESSION["CITYSELECTION_CITY"]["CODE"] = $_REQUEST["CITY_CODE"];
	$_SESSION["CITYSELECTION_CITY"]["ZIP"] = $_REQUEST["CITY_ZIP"];

	return true;
}

// В итоговом виде у нас 2 колоки: слева Регионы, справа привязанные к ним города

// Замечания:
// ВАЖНО: в данном компоненте рассматривается работа только с одной страной - РФ.
// Москва и Питер идтут особняком т.к. Города=Регионы
// у некоторых городов нет привязки к региону, пример - Зеленоград. В таком случае будем получать привязку к региону через родителя (PARENT_ID)

$cache_time = ($_REQUEST["CACHE_TIME"])?$_REQUEST["CACHE_TIME"]:0;

$obCache = new CPHPCache();

if($obCache->InitCache($cache_time, "lexapro_cityselection"))
{
    list($arRegion, $arCity) = $obCache->GetVars();
}
elseif($obCache->StartDataCache()){

    $arRegion = $arLocation = $arCity = array();

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
        if($arLoc["TYPE_CODE"] == "REGION" || in_array($arLoc["ID"], array(84,85))) // Регионы + Мск и Спб
            $arRegion[$arLoc["ID"]] = $arLoc;

        if($arLoc["TYPE_CODE"] == "CITY")
        {
            $parent = "";

            if(!empty($arLoc["REGION_ID"]))
                $parent = $arLoc["REGION_ID"];
            elseif(in_array($arLoc["ID"], array(84,85)))
                $parent = $arLoc["ID"];
            elseif(!empty($arLoc["PARENT_ID"]))
                $parent = $arLoc["PARENT_ID"];

            $arLocs = CSaleLocation::GetLocationZIP($arLoc["ID"])->Fetch();
            $arLoc["ZIP"] = $arLocs['ZIP'];

            if(!empty($parent))
            {
                $arCity[$parent]["CITIES"][] =  $arLoc;
            }
        }
    }

    $obCache->EndDataCache([$arRegion, $arCity]);
}


// вывод
?>
<div class="ajax-city-selection">

	<div class="ajax-city-selection__top-box">
		<p class="ajax-city-selection__your-city">Ваш город:</p>
		<p class="ajax-city-selection__cur-city">Москва</p>
		
		<input  class="ajax-city-selection__search" type="text" value="" placeholder="Поиск по названию">
	</div>
	
	<div class="ajax-city-selection__regions">
		<p class="ajax-city-selection__regions_name">Регионы</p>
		<div class="ajax-city-selection__regions_box">
			<ul>
				<?
				foreach($arRegion as $region)
				{
					?>
					<li><a href="#" region="<?=$region["ID"]?>"><?=$region["NAME_RU"]?></a></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<div class="ajax-city-selection__cities">
		<p class="ajax-city-selection__cities_name">Населенные пункты</p>
		<div class="ajax-city-selection__cities_box">
            <div class="cities-search"><ul></ul></div>
		<?
			foreach($arCity as $region_id => $region)
			{
				?>
				<div class="cities-region-<?=$region_id?> cities-region-list">
					<ul>
						<?
						foreach($region["CITIES"] as $city)
						{
							?><li class="li-city"><a href="#" city-zip="<?=$city["ZIP"]?>" city-code="<?=$city["CODE"]?>" city-name="<?=$city["NAME_RU"]?>"><?=$city["NAME_RU"]?></a></li><?
						}
						?>
					</ul>
				</div>
				<?
			}
			?>
			</div>
	</div>
</div>