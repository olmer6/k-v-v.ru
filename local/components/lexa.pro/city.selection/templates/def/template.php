<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$cache_time = (!empty($arParams["CACHE_TIME"])?$arParams["CACHE_TIME"]:0);

// файл лежит либо в текущем шаблоне, либо заберем его из дефолтного
$ajax_path = (file_exists($this->GetFolder()."/ajax.php")?$this->GetFolder()."/ajax.php":"/bitrix/components/lexa.pro/city.selection/templates/def/ajax.php");

$jsParams = array(
	"CURRENT_PATH_AJAX" => $ajax_path,
	"CACHE_TIME" => $cache_time
);

?>

<div class="region">
	<a href="" class="city-selection"><?=$_SESSION["CITYSELECTION_CITY"]["NAME"]?></a>
</div>

<!-- Блоки для попапов -->
<div class="city-selection__dialog"></div>
<div class="city-selection__shadow"></div>
<!-- END: Блоки для попапов -->

<script>
	var citySelectionJs = <?=CUtil::PhpToJSObject($jsParams, false, true)?>;
</script>