<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CModule::IncludeModule("iblock");
?>

<?
$arSelect = Array("ID", "NAME", "DETAIL_PAGE_URL", "DETAIL_PICTURE", "CATALOG_GROUP_1","PROPERTY_MINIMUM_PRICE");
$arFilter = Array("IBLOCK_ID"=>2, "ACTIVE"=>"Y", "!=PROPERTY_NEWPRODUCT" => false,);
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
while($ob = $res->GetNext())
{
	if(!empty($ob["PROPERTY_MINIMUM_PRICE_VALUE"]))
		$minPrice = $ob["PROPERTY_MINIMUM_PRICE_VALUE"];
	elseif(!empty($ob["CATALOG_PRICE_1"]))
		$minPrice = $ob["CATALOG_PRICE_1"];
	?>
	<div class="item-wrap-404">
		<p class="item-wrap-404-img"><a href="<?=$ob["DETAIL_PAGE_URL"];?>" class="item-img"><img src="<?=CFile::GetPath($ob["DETAIL_PICTURE"]);?>" class="img"></a></p>
		<p class="item-wrap-404-name"><a href="<?=$ob["DETAIL_PAGE_URL"];?>" class="item-descr"><?=$ob["NAME"];?></a></p>
		<p class="price item-wrap-404-price"><?=CurrencyFormat($minPrice,"RUB")?></p>
		<div class="item-wrap-404-prod-day"><img src="/images/prod-day.png"></div>
	</div>
	<?
}
?>
