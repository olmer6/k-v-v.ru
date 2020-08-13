<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
CModule::IncludeModule("iblock");
$arSelect = Array("ID", "NAME", "DETAIL_PAGE_URL", "DETAIL_PICTURE");
$arFilter = Array("IBLOCK_ID"=>2, "ACTIVE"=>"Y", "!=PROPERTY_PROD_OF_DAY" => false);
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
while($ob = $res->GetNextElement())
{
	$arFields = $ob->GetFields();
	$arProps = $ob->GetProperties();
	?>
	<div class="col-md-23 col-xs-3">
		<div class="day-item"><a href="<?=$arFields["DETAIL_PAGE_URL"];?>" class="item">
		<div class="item-img"><img src="<?=CFile::GetPath($arFields["DETAIL_PICTURE"]);?>" class="img"></div><a href="<?=$arFields["DETAIL_PAGE_URL"];?>" class="item-descr"> <?=$arFields["NAME"];?> </a>
		<div class="price"><?=CurrencyFormat($arProps["MINIMUM_PRICE"]["VALUE"] ,"RUB")?></div></a></div>
	</div>
	<?
}
?>


